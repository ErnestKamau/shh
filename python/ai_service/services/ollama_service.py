import os
import logging
import asyncio
import concurrent.futures
from typing import List, Dict, Any, Optional, AsyncGenerator

logger = logging.getLogger(__name__)

class OllamaService:
    def __init__(self):
        self.host = os.getenv("OLLAMA_HOST", "http://localhost:11434")
        self.model = os.getenv("AI_HEAVY_MODEL", os.getenv("OLLAMA_MODEL", "qwen2.5:3b"))
        self.request_timeout_s = float(os.getenv("OLLAMA_REQUEST_TIMEOUT_S", "120"))
        try:
            import ollama
            self.client = ollama.Client(host=self.host)
        except ImportError:
            self.client = None
            logger.warning("Ollama library not found. Service will only work with mocked calls.")

    def chat(self, messages: List[Dict[str, str]], options: Optional[Dict[str, Any]] = None, model: Optional[str] = None) -> Dict[str, Any]:
        """Synchronous chat call."""
        if not self.client:
            logger.error("Ollama client not initialized.")
            return {"message": {"content": "AI service offline (client missing)."}, "error": "client_not_initialized"}
            
        try:
            target_model = model or self.model
            executor = concurrent.futures.ThreadPoolExecutor(max_workers=1)
            future = executor.submit(
                self.client.chat,
                model=target_model,
                messages=messages,
                options=options or {"temperature": 0.3, "num_predict": 4096},
            )
            response = future.result(timeout=self.request_timeout_s)
            return self._normalize_chunk(response)
        except concurrent.futures.TimeoutError:
            future.cancel()
            logger.error(
                "OllamaService.chat timed out after %ss for model %s",
                self.request_timeout_s,
                target_model,
            )
            return {
                "message": {
                    "content": (
                        f"AI service timeout after {int(self.request_timeout_s)}s. "
                        "Please try again."
                    )
                },
                "error": "ollama_timeout",
            }
        except Exception as e:
            logger.error(f"OllamaService.chat failed: {e}")
            return {"message": {"content": f"AI service error: {str(e)}"}, "error": str(e)}
        finally:
            if 'executor' in locals():
                executor.shutdown(wait=False, cancel_futures=True)

    async def chat_stream(self, messages: List[Dict[str, str]], options: Optional[Dict[str, Any]] = None, model: Optional[str] = None) -> AsyncGenerator[Dict[str, Any], None]:
        """Stream Ollama responses without blocking the event loop."""
        if not self.client:
            yield {"error": "Ollama client not initialized"}
            return

        try:
            target_model = model or self.model
            queue: asyncio.Queue[Optional[Dict[str, Any]]] = asyncio.Queue()
            loop = asyncio.get_running_loop()

            def producer() -> None:
                try:
                    stream = self.client.chat(
                        model=target_model,
                        messages=messages,
                        options=options or {"temperature": 0.3, "num_predict": 4096},
                        stream=True
                    )
                    for chunk in stream:
                        loop.call_soon_threadsafe(queue.put_nowait, self._normalize_chunk(chunk))
                except Exception as exc:
                    logger.error(f"OllamaService.chat_stream failed: {exc}")
                    loop.call_soon_threadsafe(queue.put_nowait, {"error": str(exc)})
                finally:
                    loop.call_soon_threadsafe(queue.put_nowait, None)

            producer_task = asyncio.create_task(asyncio.to_thread(producer))
            try:
                while True:
                    # Removed strict wait_for as per user request to allow long LLM response times
                    chunk = await queue.get()
                    if chunk is None:
                        break
                    yield chunk
            except Exception as e:
                logger.error(f"OllamaService.chat_stream loop failed: {e}")
                yield {"error": str(e)}
            finally:
                if not producer_task.done():
                    producer_task.cancel()
        except Exception as e:
            logger.error(f"OllamaService.chat_stream failed: {e}")
            yield {"error": str(e)}

    def _normalize_chunk(self, chunk: Any) -> Dict[str, Any]:
        """Return a plain dict regardless of whether Ollama returned a mapping or a typed response object."""
        if isinstance(chunk, dict):
            return chunk
        if hasattr(chunk, "model_dump"):
            return chunk.model_dump()
        if hasattr(chunk, "dict"):
            return chunk.dict()
        logger.warning("Received unexpected Ollama chunk type: %s", type(chunk).__name__)
        return {"message": {"content": str(chunk)}}

    def generate(self, prompt: str, system: Optional[str] = None, model: Optional[str] = None) -> str:
        """Simple generation utility."""
        messages = []
        if system:
            messages.append({"role": "system", "content": system})
        messages.append({"role": "user", "content": prompt})
        
        response = self.chat(messages, model=model)
        return response['message']['content']
