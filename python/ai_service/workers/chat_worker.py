"""
workers/chat_worker.py — Conversational Chat Worker

Handles purely conversational queries: greetings, empathy, general questions
that don't match any data source. Uses the mode-specific persona from the
ModeRegistry to stay in character throughout the conversation.
"""

import logging
import os
import concurrent.futures
from typing import Optional, Dict, Any, List

from python.ai_service.services.ollama_service import OllamaService
from python.ai_service.core import mode_registry

logger = logging.getLogger(__name__)

_CHAT_TIMEOUT = 10.0


class ChatWorker:
    """
    Worker that handles pure conversational responses using the Ollama LLM.
    Always returns a result (last-resort worker — never returns None).
    """

    def __init__(self, ollama: OllamaService):
        self.ollama = ollama
        self.chat_model = os.getenv("AI_CHAT_MODEL", "gemma3:1b")

    def run(
        self,
        message: str,
        messages: List[Dict[str, str]],
        mode: Optional[str] = None,
        model: Optional[str] = None,
        trace_id: str = "",
    ) -> Dict[str, Any]:
        """
        Generate a conversational response. Always returns a result dict.
        """
        system_prompt = mode_registry.get_persona(mode)

        try:
            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as ex:
                response = ex.submit(
                    self.ollama.chat,
                    messages=[{"role": "system", "content": system_prompt}] + messages,
                    model=model or self.chat_model,
                ).result(timeout=_CHAT_TIMEOUT)

            answer = response.get("message", {}).get("content", "")
            if not answer:
                raise ValueError("Empty response from Ollama")

            return {
                "answer": answer,
                "sources": [],
                "meta": {
                    "route": "conversational",
                    "routing_tier": "chat",
                    "confidence": 0.7,
                },
            }

        except Exception as exc:
            logger.error(f"ChatWorker [{trace_id[:8]}]: Chat failed: {exc}")
            return {
                "answer": (
                    "I'm having trouble generating a response right now. "
                    "Please try again in a moment, or ask a more specific question."
                ),
                "sources": [],
                "meta": {
                    "route": "chat_fallback",
                    "routing_tier": "chat",
                    "confidence": 0.0,
                    "error": str(exc),
                },
            }

    def run_stream(
        self,
        messages: List[Dict[str, str]],
        mode: Optional[str] = None,
        model: Optional[str] = None,
    ):
        """
        Streaming version. Yields tokens from the Ollama stream.
        Returns the stream object provided by OllamaService, which may be async.
        """
        system_prompt = mode_registry.get_persona(mode)
        return self.ollama.chat_stream(
            messages=[{"role": "system", "content": system_prompt}] + messages,
            model=model or self.chat_model,
        )
