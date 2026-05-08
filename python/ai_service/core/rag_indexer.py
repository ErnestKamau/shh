"""
RAG Indexer Service (STAGE 1)
Industrialized indexing pipeline for expanding coverage across lab and compliance domains.

Features:
- Idempotent indexing (deterministic chunk IDs)
- Stale-chunk replacement (clean before reindex)
- Batch embedding and pgvector insertion
- Rich metadata schema (company_id, entity_type, source_table, etc.)
- Multi-tenant isolation at the indexing level
"""
from __future__ import annotations

import json
import hashlib
import logging
from datetime import datetime
from typing import List, Dict, Any, Optional

from sqlalchemy import text
from loguru import logger as loguru_logger

from py_etl.core.database import db_manager
from ai_service.services.retrieval_service import RetrievalService
from ai_service.core.document_processor import DocumentProcessor

logger = logging.getLogger(__name__)

class RagIndexer:
    """
    Handles bulk, idempotent indexing of operational data into the RAG knowledge base.
    """

    def __init__(self, retrieval_service: Optional[RetrievalService] = None):
        self.retrieval_service = retrieval_service or RetrievalService()
        self.document_processor = DocumentProcessor()
        self.schema = "ai"
        self.table = "ai_knowledge_chunks"

    def generate_chunk_id(self, 
                          company_id: int, 
                          entity_type: str, 
                          entity_id: Any, 
                          section_title: str = "default", 
                          chunk_index: int = 0,
                          content: str = "") -> str:
        """
        Generate a refined deterministic ID for a knowledge chunk.
        Format: md5(company_id:entity_type:entity_id:section_title:chunk_index:content_hash)
        """
        content_hash = hashlib.md5(content.encode()).hexdigest()[:8]
        identifier = f"{company_id}:{entity_type}:{entity_id}:{section_title}:{chunk_index}:{content_hash}"
        return hashlib.md5(identifier.encode()).hexdigest()

    def cleanup_stale_chunks(self, company_id: int, entity_type: str, entity_ids: List[Any]):
        """
        Remove existing chunks for specific entities before re-indexing to ensure freshness.
        """
        if not entity_ids:
            return

        sql = text(f"""
            DELETE FROM {self.schema}.{self.table}
            WHERE company_id = :company_id 
              AND entity_type = :entity_type
              AND entity_id = ANY(:entity_ids)
        """)
        
        try:
            with db_manager.postgres_connection() as conn:
                result = conn.execute(sql, {
                    "company_id": company_id,
                    "entity_type": entity_type,
                    "entity_ids": entity_ids
                })
                conn.commit()
                return result.rowcount
        except Exception as e:
            logger.error(f"RagIndexer: Cleanup failed for {entity_type}: {e}")
            return 0

    def index_batch(self, 
                    domain: str,
                    entity_type: str,
                    records: List[Dict[str, Any]],
                    template_func: callable,
                    chunk_size: Optional[int] = None,
                    chunk_overlap: Optional[int] = None) -> int:
        """
        Process and index a batch of records. Supports multi-chunk document processing.
        """
        if not records:
            return 0

        # Initialize processor with tunable parameters
        processor = DocumentProcessor(
            chunk_size=chunk_size or 800,
            chunk_overlap=chunk_overlap or 100
        )

        indexed_count = 0
        is_document = entity_type.lower() in ["sop_metadata", "document", "manual"]
        
        for record in records:
            try:
                # 1. Extract metadata and payload
                source_id = record.get('source_id')
                payload = record.get('payload', {})
                if isinstance(payload, str):
                    payload = json.loads(payload)
                
                company_id = payload.get('company_id', payload.get('crm_customer_id', 0))
                
                # 2. Determine chunks logic
                prepared_chunks = []
                
                if is_document and payload.get("full_content"):
                    # Stage 4: Document-Aware Processing
                    full_content = payload.get("full_content")
                    prepared_chunks = processor.process_document(
                        content=full_content,
                        document_id=source_id,
                        base_metadata={"domain": domain, "entity_type": entity_type}
                    )
                else:
                    # Stage 1-3: Standard Record Template
                    content = template_func(record, payload)
                    if content:
                        prepared_chunks = [{
                            "content": content,
                            "metadata": {
                                "domain": domain,
                                "entity_type": entity_type,
                                "section_title": "General"
                            }
                        }]

                if not prepared_chunks:
                    continue

                # 3. Batch Index the chunks for this record
                for i, chunk_data in enumerate(prepared_chunks):
                    content = chunk_data["content"]
                    section_title = chunk_data["metadata"].get("section_title", "General")
                    
                    # Generate deterministic ID
                    chunk_id = self.generate_chunk_id(
                        company_id=company_id,
                        entity_type=entity_type,
                        entity_id=source_id,
                        section_title=section_title,
                        chunk_index=i,
                        content=content
                    )

                    # Generate Embedding
                    embedding = self.retrieval_service.embed_text(content)
                    embedding_json = "[" + ",".join(str(float(v)) for v in embedding) + "]"

                    # Stage 10: Knowledge Governance & Lineage
                    expires_at = payload.get('expires_at')
                    
                    # Generate automatic source lineage URL based on entity_type
                    source_lineage_url = payload.get('external_source_url') or payload.get('source_lineage_url')
                    if not source_lineage_url:
                        if entity_type == 'samples':
                            source_lineage_url = f"/laboratory/samples/view/{source_id}"
                        elif entity_type == 'equipment':
                            source_lineage_url = f"/laboratory/equipment/dashboard/{source_id}"
                        elif entity_type == 'manual' and payload.get('manual_doc_id'):
                            source_lineage_url = f"/imara-ai/knowledge-editor/{payload.get('manual_doc_id')}"

                    # Final Metadata Assembly
                    meta = {
                        **chunk_data["metadata"],
                        **{k: v for k, v in payload.items() if k not in ['full_content', 'content', 'embedding', 'expires_at', 'source_lineage_url']},
                        "source_id": source_id,
                        "company_id": company_id,
                        "indexed_at": datetime.now().isoformat(),
                        "source_table": record.get('_source_table', 'unknown'),
                        "display_label": payload.get('name', payload.get('reference_number', f"{entity_type} {source_id}")),
                        "source_lineage_url": source_lineage_url
                    }

                    # Idempotent Upsert with Governance
                    sql = text(f"""
                        INSERT INTO {self.schema}.{self.table} 
                        (chunk_id, collection_name, entity_type, entity_id, content, embedding, metadata, company_id, expires_at, source_lineage_url)
                        VALUES (:chunk_id, :collection, :entity_type, :entity_id, :content, CAST(:embedding AS vector), :metadata, :company_id, :expires_at, :source_lineage_url)
                        ON CONFLICT (chunk_id) DO UPDATE SET
                            content = EXCLUDED.content,
                            embedding = EXCLUDED.embedding,
                            metadata = EXCLUDED.metadata,
                            expires_at = EXCLUDED.expires_at,
                            source_lineage_url = EXCLUDED.source_lineage_url,
                            updated_at = NOW()
                    """)

                    with db_manager.postgres_connection() as conn:
                        conn.execute(sql, {
                            "chunk_id": chunk_id,
                            "collection": domain,
                            "entity_type": entity_type,
                            "entity_id": str(source_id),
                            "content": content,
                            "embedding": embedding_json,
                            "metadata": json.dumps(meta),
                            "company_id": company_id,
                            "expires_at": expires_at,
                            "source_lineage_url": source_lineage_url
                        })
                        conn.commit()
                    
                    indexed_count += 1
                    
            except Exception as e:
                error_msg = str(e)
                logger.error(f"RagIndexer: Failed to index {entity_type} {record.get('source_id')}: {error_msg}")
                continue

        return indexed_count
