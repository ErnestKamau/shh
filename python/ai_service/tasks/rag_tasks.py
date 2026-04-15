"""
Celery tasks for RAG Indexing.
"""
from __future__ import annotations

import json
from typing import Dict, Any

from celery import Task
from loguru import logger
from sqlalchemy import text

from python.celery_config import app as celery_app
from python.py_etl.core.database import db_manager
from python.py_etl.services.etl_index_state_service import etl_index_state_service
from python.ai_service.services.reporting_reader_service import reporting_reader_service
from python.ai_service.services.retrieval_service import RetrievalService


class RagTask(Task):
    def on_failure(self, exc, task_id, args, kwargs, einfo):
        logger.error(f"RAG task {self.name} failed", extra={"task_id": task_id, "error": str(exc)})

    def on_success(self, retval, task_id, args, kwargs):
        logger.info(f"RAG task {self.name} succeeded", extra={"task_id": task_id, "result": retval})


@celery_app.task(
    name="tasks.rag.reindex_domain",
    base=RagTask,
    queue="rag",
    bind=True,
    max_retries=2
)
def reindex_domain(self, table_key: str) -> Dict[str, Any]:
    """
    Chunk and embed fresh rows from reporting.* for 'table_key'.
    Only processes rows with source_id > last indexed watermark.
    """
    try:
        state = etl_index_state_service.get_state(table_key)
        
        # 1. Fetch fresh rows
        # Instead of max source_id, we use the timestamp of the last index run
        last_indexed_at = str(state.get("last_indexed_at") or '1970-01-01 00:00:00+00:00')
        df = reporting_reader_service.fetch_fresh_rows(table_key, since_timestamp=last_indexed_at)
        
        if df.empty:
            logger.info(f"No new rows to index for {table_key}")
            # we keep the previous watermarks, just touch the time
            watermark = etl_index_state_service.get_last_watermark(table_key)
            etl_index_state_service.upsert_index_success(table_key, watermark, 0, 0)
            return {"status": "success", "table_key": table_key, "chunks_produced": 0}

        # 2. Chunk rows
        chunks = reporting_reader_service.chunk_rows(table_key, df)
        
        # 3. Clean up any existing stale chunks for these source_ids
        source_ids = df["source_id"].tolist()
        stale_deleted = _delete_stale_chunks(table_key, source_ids)
        
        # 4. Embed and upsert to pgvector
        embeddings_count = _upsert_chunks_to_pgvector(table_key, chunks)
        
        # 5. Update watermark (still log max source_id for debugging, but indexing logic uses current time)
        new_watermark = int(df["source_id"].max())
        etl_index_state_service.upsert_index_success(
            table_key, new_watermark, len(chunks), embeddings_count
        )
        
        return {
            "status": "success",
            "table_key": table_key,
            "chunks_produced": len(chunks),
            "embeddings": embeddings_count,
            "stale_deleted": stale_deleted,
            "watermark": new_watermark,
        }
        
    except Exception as exc:
        etl_index_state_service.upsert_index_failure(table_key, str(exc))
        raise self.retry(exc=exc, countdown=30)


def _delete_stale_chunks(table_key: str, source_ids: list[int]) -> int:
    """Delete existing chunks for these source_ids so we don't duplicate on edit."""
    if not source_ids:
        return 0
    sql = text("""
        DELETE FROM ai.ai_knowledge_chunks
        WHERE collection_name = :table_key
          AND entity_id = ANY(:source_ids)
    """)
    try:
        with db_manager.postgres_connection() as conn:
            # Cast source_ids to string since entity_id is a string field
            str_ids = [str(i) for i in source_ids]
            res = conn.execute(sql, {"table_key": table_key, "source_ids": str_ids})
            conn.commit()
            return res.rowcount
    except Exception as e:
        logger.error(f"Failed to delete stale chunks for {table_key}: {e}")
        return 0


def _upsert_chunks_to_pgvector(table_key: str, chunks: list[dict]) -> int:
    """Generate embeddings and upsert chunks into ai_knowledge_chunks."""
    if not chunks:
        return 0
        
    retrieval_service = RetrievalService()
    success_count = 0
    
    upsert_sql = text("""
        INSERT INTO ai.ai_knowledge_chunks 
        (chunk_id, collection_name, entity_type, entity_id, content, embedding, metadata, company_id)
        VALUES (:chunk_id, :collection, :entity_type, :entity_id, :content, CAST(:embedding AS vector), :metadata, :company_id)
        ON CONFLICT (chunk_id) DO UPDATE SET
            content = EXCLUDED.content,
            embedding = EXCLUDED.embedding,
            metadata = EXCLUDED.metadata,
            updated_at = NOW()
    """)
    
    with db_manager.postgres_connection() as conn:
        for chunk in chunks:
            try:
                embedding = retrieval_service.embed_text(chunk["content"])
                embedding_json = "[" + ",".join(str(float(v)) for v in embedding) + "]"
                
                meta = {
                    "source_table": f"reporting.{table_key}",
                    "chunk_id": chunk["chunk_id"]
                }
                
                conn.execute(upsert_sql, {
                    "chunk_id": chunk["chunk_id"],
                    "collection": table_key,
                    "entity_type": table_key,
                    "entity_id": str(chunk["source_id"]),
                    "content": chunk["content"],
                    "embedding": embedding_json,
                    "metadata": json.dumps(meta),
                    "company_id": 0 # Default single tenant
                })
                success_count += 1
            except Exception as e:
                logger.error(f"Failed to embed/upsert chunk {chunk['chunk_id']}: {e}")
                continue
                
        conn.commit()
        
    return success_count
