from fastapi import APIRouter, HTTPException, BackgroundTasks, UploadFile, File, Form
from pydantic import BaseModel
from typing import Optional, Dict, Any, List
import logging
import json

from ai_service.core.rag_indexer import RagIndexer
from ai_service.core.file_parser import FileParser

router = APIRouter(prefix="/v1/index", tags=["indexing"])
logger = logging.getLogger(__name__)
indexer = RagIndexer()
parser = FileParser()

class IndexingRequest(BaseModel):
    collection: str
    content: str
    entity_type: Optional[str] = "manual"
    entity_id: Optional[str] = None
    metadata: Optional[Dict[str, Any]] = None
    permission: Optional[str] = "General.View"
    manual_doc_id: Optional[int] = None
    chunk_size: Optional[int] = 800
    chunk_overlap: Optional[int] = 100

class SearchRequest(BaseModel):
    query: str
    company_id: int = 0
    collections: Optional[List[str]] = None
    limit: int = 5

class BulkDeleteRequest(BaseModel):
    entity_type: str
    entity_ids: List[str]
    company_id: int = 0

@router.post("")
async def index_document(request: IndexingRequest, background_tasks: BackgroundTasks):
    """
    Endpoint for indexing manual documents or specific entities.
    Replaces the legacy PHP CollectionIndexerService.
    """
    try:
        # Prepare the record for the RagIndexer
        # RagIndexer expects a list of dictionaries with 'source_id' and 'payload'
        
        # company_id is extracted from metadata or default to 0
        company_id = request.metadata.get("company_id") if request.metadata else 0
        
        # Cleanup old chunks if entity_id or manual_doc_id exists
        cleanup_ids = []
        if request.manual_doc_id:
            cleanup_ids.append(str(request.manual_doc_id))
        elif request.entity_id:
            cleanup_ids.append(str(request.entity_id))
            
        if cleanup_ids:
            indexer.cleanup_stale_chunks(
                company_id=company_id,
                entity_type=request.entity_type,
                entity_ids=cleanup_ids
            )

        # Helper function for RagIndexer template logic
        def manual_template(record, payload):
            return payload.get("full_content")

        # Create record for indexer
        record = {
            "source_id": request.manual_doc_id or request.entity_id or "0",
            "payload": {
                "full_content": request.content,
                "company_id": company_id,
                "name": request.metadata.get("title", f"Manual Doc {request.manual_doc_id}") if request.metadata else "Manual Doc",
                "manual_doc_id": request.manual_doc_id,
                ** (request.metadata or {})
            },
            "_source_table": "manual_entry"
        }

        # Start a tracking run for this indexing task
        run_id = indexer.start_indexing_run(request.collection)

        # Index the record
        count = indexer.index_batch(
            domain=request.collection,
            entity_type=request.entity_type,
            records=[record],
            template_func=manual_template,
            chunk_size=request.chunk_size,
            chunk_overlap=request.chunk_overlap
        )

        # Finalize telemetry run
        indexer.finalize_run(run_id, count)

        return {
            "status": "ok",
            "run_id": run_id,
            "indexed_count": count,
            "message": f"Successfully indexed {count} chunks for {request.entity_type} {record['source_id']}"
        }

    except Exception as e:
        logger.error(f"Indexing endpoint failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.delete("/{entity_type}/{entity_id}")
async def delete_knowledge(entity_type: str, entity_id: str, company_id: int = 0):
    """
    Endpoint for removing knowledge chunks.
    """
    try:
        count = indexer.cleanup_stale_chunks(
            company_id=company_id,
            entity_type=entity_type,
            entity_ids=[entity_id]
        )
        return {"status": "ok", "deleted_count": count}
    except Exception as e:
        logger.error(f"Deletion endpoint failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/bulk-delete")
async def bulk_delete_knowledge(request: BulkDeleteRequest):
    """
    Endpoint for removing multiple knowledge entries at once.
    """
    try:
        count = indexer.cleanup_stale_chunks(
            company_id=request.company_id,
            entity_type=request.entity_type,
            entity_ids=request.entity_ids
        )
        return {"status": "ok", "deleted_count": count}
    except Exception as e:
        logger.error(f"Bulk deletion failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/search")
async def search_test(request: SearchRequest):
    """
    Endpoint for testing retrieval logic (Semantic Preview).
    """
    try:
        from ai_service.services.retrieval_service import RetrievalService
        retrieval = RetrievalService()
        
        results = retrieval.search(
            query=request.query,
            limit=request.limit,
            collections=request.collections,
            metadata_filters={"company_id": request.company_id}
        )
        
        return {
            "status": "ok",
            "results": results,
            "count": len(results)
        }
    except Exception as e:
        logger.error(f"Search test failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/upload")
async def upload_document(
    background_tasks: BackgroundTasks,
    file: UploadFile = File(...),
    collection: str = Form(...),
    permission: str = Form("General.View"),
    manual_doc_id: Optional[int] = Form(None),
    chunk_size: Optional[int] = Form(800),
    chunk_overlap: Optional[int] = Form(100),
    metadata: Optional[str] = Form(None) # Metadata as JSON string
):
    """
    Endpoint for uploading files (PDF, DOCX, CSV) for indexing.
    """
    try:
        content = await file.read()
        
        # Parse metadata if provided
        meta_dict = {}
        if metadata:
            try:
                meta_dict = json.loads(metadata)
            except:
                logger.warning(f"Failed to parse metadata JSON: {metadata}")

        # Extract text from file
        text_content = parser.extract_text(content, file.filename)
        
        if not text_content:
            raise HTTPException(status_code=400, detail="No readable text found in document.")

        # Prepare for indexing (mirroring index_document logic)
        company_id = meta_dict.get("company_id", 0)
        
        # Cleanup old chunks
        if manual_doc_id:
            indexer.cleanup_stale_chunks(
                company_id=company_id,
                entity_type="manual",
                entity_ids=[str(manual_doc_id)]
            )

        def manual_template(record, payload):
            return payload.get("full_content")

        record = {
            "source_id": str(manual_doc_id) if manual_doc_id else "0",
            "payload": {
                "full_content": text_content,
                "company_id": company_id,
                "name": meta_dict.get("title", file.filename),
                "manual_doc_id": manual_doc_id,
                **meta_dict
            },
            "_source_table": "manual_entry_file"
        }

        # Start a tracking run for this indexing task
        run_id = indexer.start_indexing_run(collection)

        # Indexing
        count = indexer.index_batch(
            domain=collection,
            entity_type="manual",
            records=[record],
            template_func=manual_template,
            chunk_size=chunk_size,
            chunk_overlap=chunk_overlap
        )

        # Finalize telemetry run
        indexer.finalize_run(run_id, count)

        return {
            "status": "ok",
            "run_id": run_id,
            "filename": file.filename,
            "indexed_count": count,
            "message": f"Successfully parsed and indexed {count} chunks from {file.filename}"
        }

    except Exception as e:
        logger.error(f"File upload indexing failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))
