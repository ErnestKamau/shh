import os
import re
import hashlib
import math
import logging
from typing import List, Dict, Any, Optional
from sqlalchemy import text
from python.py_etl.core.database import db_manager

logger = logging.getLogger(__name__)

class RetrievalService:
    def __init__(self):
        self.schema = os.getenv("AI_SCHEMA", "ai")
        self.embedding_dim = int(os.getenv("AI_EMBEDDING_DIM", "1536"))
        self._embedding_model = None
        from python.ai_service.services.rrf_fusion_service import RrfFusionService
        self.fusion_service = RrfFusionService()

    def _get_embedding_model(self):
        if self._embedding_model is not None:
            return self._embedding_model
        
        try:
            from sentence_transformers import SentenceTransformer
            model_name = os.getenv("AI_EMBEDDING_MODEL", "all-MiniLM-L6-v2")
            self._embedding_model = SentenceTransformer(model_name)
            logger.info(f"RetrievalService: Loaded embedding model: {model_name}")
        except Exception as exc:
            logger.warning(f"RetrievalService: sentence-transformers unavailable, using fallback: {exc}")
        
        return self._embedding_model

    def embed_text(self, text_value: str) -> List[float]:
        """Generate an embedding vector for a text input."""
        model = self._get_embedding_model()
        if model is not None:
            vector = model.encode(text_value).tolist()
            # Normalise/Pad to target dimension
            if len(vector) >= self.embedding_dim:
                return vector[:self.embedding_dim]
            return vector + [0.0] * (self.embedding_dim - len(vector))
        
        return self._fallback_embed(text_value)

    def _fallback_embed(self, text_value: str) -> List[float]:
        """Hash-based deterministic embedding."""
        vector = [0.0] * self.embedding_dim
        tokens = [t for t in re.split(r"\s+", text_value.lower().strip()) if t]
        for token in tokens:
            digest = hashlib.sha256(token.encode()).digest()
            idx = int.from_bytes(digest[:8], "big") % self.embedding_dim
            sign = 1.0 if digest[8] % 2 == 0 else -1.0
            vector[idx] += sign
        
        magnitude = math.sqrt(sum(v * v for v in vector))
        if magnitude > 0:
            vector = [v / magnitude for v in vector]
        return vector

    def search(self, 
               query: str, 
               limit: int = 5, 
               collections: Optional[List[str]] = None, 
               entity_types: Optional[List[str]] = None,
               metadata_filters: Optional[Dict[str, Any]] = None,
               mode: str = "hybrid",
               candidate_limit: int = 20) -> List[Dict[str, Any]]:
        """
        Unified search interface. 
        In Industrialized mode, 'hybrid_search' handles candidate fetching and fusion.
        """
        # Detect if query has strong identifiers (e.g., SOP-001, EQ-123)
        # Stage 9: Identifier Sensitivity
        id_patterns = [r'[A-Z]{2,4}-\d+', r'[A-Z]{2,4}_[0-9]+', r'DOC-\d+', r'AUD-\d+', r'CAPA-\d+']
        has_id = any(re.search(pat, query.upper()) for pat in id_patterns)
        
        # If it has an ID, we increase candidate limit to ensure lexical hit is caught
        if has_id:
            candidate_limit = max(candidate_limit, 50)
            logger.info(f"RetrievalService: Identifier detected in query '{query}', increasing search depth.")

        # Always use hybrid in production unless explicitly overridden
        return self.hybrid_search(
            query=query, 
            limit=limit, 
            collections=collections, 
            entity_types=entity_types, 
            metadata_filters=metadata_filters,
            candidate_limit=candidate_limit
        )

    def vector_search(self, query: str, limit: int = 5, collections: Optional[List[str]] = None, 
                     entity_types: Optional[List[str]] = None, metadata_filters: Optional[Dict[str, Any]] = None) -> List[Dict[str, Any]]:
        """Pure semantic search."""
        try:
            vector = self.embed_text(query)
            vector_json = "[" + ",".join(str(float(v)) for v in vector) + "]"

            where_parts, params = self._build_where_clause(collections, entity_types, metadata_filters)
            params["vector"] = vector_json
            params["limit"] = limit

            where_sql = f"WHERE {' AND '.join(where_parts)}" if where_parts else ""

            sql = text(f"""
                SELECT id, content, collection_name, entity_type, entity_id, metadata,
                       1 - (embedding <=> CAST(:vector AS vector)) AS score
                FROM {self.schema}.ai_knowledge_chunks
                {where_sql}
                ORDER BY embedding <=> CAST(:vector AS vector) ASC
                LIMIT :limit
            """)

            with db_manager.postgres_connection() as conn:
                rows = conn.execute(sql, params).mappings().all()
            
            return [dict(row) for row in rows]
        except Exception as exc:
            logger.error(f"RetrievalService: Vector search failed: {exc}")
            return []

    def lexical_search(self, query: str, limit: int = 5, collections: Optional[List[str]] = None, 
                      entity_types: Optional[List[str]] = None, metadata_filters: Optional[Dict[str, Any]] = None) -> List[Dict[str, Any]]:
        """Pure lexical (Full-Text) search using PostgreSQL tsquery."""
        try:
            where_parts, params = self._build_where_clause(collections, entity_types, metadata_filters)
            # Simple websearch_to_tsquery for robustness
            params["query_str"] = query
            params["limit"] = limit

            where_parts.append("tsvector_content @@ websearch_to_tsquery('english', :query_str)")
            where_sql = f"WHERE {' AND '.join(where_parts)}"

            sql = text(f"""
                SELECT id, content, collection_name, entity_type, entity_id, metadata,
                       ts_rank_cd(tsvector_content, websearch_to_tsquery('english', :query_str)) AS score
                FROM {self.schema}.ai_knowledge_chunks
                {where_sql}
                ORDER BY score DESC
                LIMIT :limit
            """)

            with db_manager.postgres_connection() as conn:
                rows = conn.execute(sql, params).mappings().all()
            
            return [dict(row) for row in rows]
        except Exception as exc:
            logger.error(f"RetrievalService: Lexical search failed: {exc}")
            return []

    def hybrid_search(self, query: str, limit: int = 5, collections: Optional[List[str]] = None, 
                     entity_types: Optional[List[str]] = None, metadata_filters: Optional[Dict[str, Any]] = None,
                     candidate_limit: int = 20) -> List[Dict[str, Any]]:
        """
        Industrialized Hybrid Search:
        1. Fetches large candidate pools (Semantic & Lexical).
        2. Fuses results with RRF.
        3. Applies traceability logs.
        """
        # 1. Fetch Candidates (Default 20 per stream)
        vector_results = self.vector_search(query, candidate_limit, collections, entity_types, metadata_filters)
        lexical_results = self.lexical_search(query, candidate_limit, collections, entity_types, metadata_filters)

        # 2. Fuse with RRF
        fused = self.fusion_service.fuse([vector_results, lexical_results], k=60, limit=limit)

        # 3. Traceability Logs
        logger.info(f"Retrieval Trace: query='{query[:30]}...' | vector_pool={len(vector_results)} | lexical_pool={len(lexical_results)} | fused={len(fused)}")
        if fused:
            logger.debug(f"Top Fused Result: ID={fused[0]['entity_id']} | Score={fused[0]['rrf_score']:.4f}")

        return fused

    def _build_where_clause(self, collections, entity_types, metadata_filters):
        """Helper to build consistent SQL WHERE conditions."""
        where_parts = []
        params = {}

        if collections:
            where_parts.append("collection_name = ANY(:collections)")
            params["collections"] = collections

        if entity_types:
            where_parts.append("entity_type = ANY(:entity_types)")
            params["entity_types"] = entity_types

        if metadata_filters:
            for i, (key, value) in enumerate(metadata_filters.items()):
                param_key = f"meta_{i}"
                if key == "company_id":
                    # Use the dedicated column for company_id
                    where_parts.append(f"company_id = :{param_key}")
                else:
                    # Fallback for other metadata fields
                    where_parts.append(f"metadata->>'{key}' = :{param_key}")
                params[param_key] = str(value)
        
        return where_parts, params
