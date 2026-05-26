"""
workers/rag_worker.py — RAG (Knowledge Base) Worker

Searches the vector knowledge base using hybrid retrieval (semantic + lexical)
scoped to the mode's allowed collections and entity types.
Synthesizes a grounded answer from the retrieved chunks.
"""

import logging
import concurrent.futures
from typing import Optional, Dict, Any, List

from python.ai_service.services.retrieval_service import RetrievalService
from python.ai_service.services.ollama_service import OllamaService
from python.ai_service.core import mode_registry
from python.ai_service.core.language import language_instruction, localize_fixed_text

logger = logging.getLogger(__name__)

_RAG_TIMEOUT = 7.0
_SYNTH_TIMEOUT = 6.0


class RagWorker:
    """
    Worker that retrieves knowledge base chunks and synthesizes a grounded answer.
    Returns None if no relevant chunks are found.
    """

    def __init__(self, retrieval: RetrievalService, ollama: OllamaService):
        self.retrieval = retrieval
        self.ollama = ollama

    def run(
        self,
        message: str,
        company_id: int = 1,
        mode: Optional[str] = None,
        trace_id: str = "",
        limit: int = 5,
        language: Optional[str] = None,
    ) -> Optional[Dict[str, Any]]:
        """
        Search the knowledge base and synthesize an answer.
        Returns a result dict if chunks found, or None if nothing relevant found.
        """
        rag_filter = mode_registry.get_rag_filter(mode)
        collections = rag_filter.get("collections")
        entity_types = rag_filter.get("entity_types")
        metadata_filters = {"company_id": company_id} if company_id else {}

        logger.info(
            f"RagWorker [{trace_id[:8]}]: Searching — collections={collections} "
            f"entity_types={entity_types}"
        )

        try:
            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as ex:
                future = ex.submit(
                    self.retrieval.search,
                    query=message,
                    limit=limit,
                    collections=collections,
                    entity_types=entity_types,
                    metadata_filters=metadata_filters or None,
                )
                chunks = future.result(timeout=_RAG_TIMEOUT)
        except concurrent.futures.TimeoutError:
            logger.warning(f"RagWorker [{trace_id[:8]}]: Search timed out")
            return None
        except Exception as exc:
            logger.error(f"RagWorker [{trace_id[:8]}]: Search failed: {exc}")
            return None

        if not chunks:
            logger.info(f"RagWorker [{trace_id[:8]}]: No chunks found")
            return None

        logger.info(f"RagWorker [{trace_id[:8]}]: Found {len(chunks)} chunks")
        answer = self._synthesize(message, chunks, mode, language)

        return {
            "answer": answer,
            "sources": self._format_sources(chunks),
            "meta": {
                "route": "rag",
                "routing_tier": "rag",
                "confidence": 0.9,
                "source_count": len(chunks),
            },
        }

    def _synthesize(
        self, message: str, chunks: List[Dict[str, Any]], mode: Optional[str], language: Optional[str]
    ) -> str:
        context = "\n\n".join(
            f"Source: {c.get('collection_name')}\nContent: {c['content']}"
            for c in chunks
        )

        system_role = mode_registry.get_persona(mode, language)
        lang_rule = language_instruction(language)

        prompt = f"""{system_role}
{lang_rule}

Use the following knowledge base context to answer the user's question concisely.

Context:
{context}

Question: "{message}"

Rules:
1. Answer in 2-3 sentences max. Be direct and professional.
2. If the context does not contain the answer, say:
   "I couldn't find a relevant document for that. Please contact the lab supervisor."
3. Do NOT fabricate information not in the context.

Answer:"""

        try:
            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as ex:
                answer = ex.submit(self.ollama.generate, prompt=prompt).result(timeout=_SYNTH_TIMEOUT)

            if answer and not answer.startswith("I couldn't find"):
                # Append source attribution
                sources_text = "\n\n---\n**Sources:**\n"
                seen = set()
                for c in chunks:
                    key = f"{c.get('collection_name')} ({c.get('entity_type')})"
                    if key not in seen:
                        sources_text += f"- {key}\n"
                        seen.add(key)
                answer += sources_text

            return answer
        except Exception as exc:
            logger.error(f"RagWorker: synthesis failed: {exc}")
            return localize_fixed_text(
                "rag_no_summary",
                language,
                "I found relevant documents but couldn't generate a summary. Please check the sources.",
            )

    def _format_sources(self, chunks: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        return [
            {
                "id": str(c.get("id", "")),
                "collection": c.get("collection_name", ""),
                "entity_type": c.get("entity_type", ""),
                "entity_id": str(c.get("entity_id", "")),
                "score": round(float(c.get("rrf_score", c.get("score", 0))), 4),
                "preview": str(c.get("content", ""))[:200],
            }
            for c in chunks
        ]
