import os
import logging
import asyncio
import concurrent.futures
from typing import List, Dict, Any, Optional, AsyncGenerator

logger = logging.getLogger(__name__)

class OllamaService:
    def __init__(self):
        from python.ai_service.config.settings import settings
        self.host = settings.ollama_host
        self.model = settings.heavy_model
        self.request_timeout_s = float(os.getenv("OLLAMA_REQUEST_TIMEOUT_S", "120"))
        self.stream_idle_timeout_s = float(os.getenv("OLLAMA_STREAM_IDLE_TIMEOUT_S", "30"))
        self.stream_num_predict = int(os.getenv("OLLAMA_STREAM_NUM_PREDICT", "768"))
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
                        options=options or {"temperature": 0.3, "num_predict": self.stream_num_predict},
                        stream=True
                    )
                    for chunk in stream:
                        loop.call_soon_threadsafe(queue.put_nowait, self._normalize_chunk(chunk))
                except Exception as exc:
                    logger.error(f"OllamaService.chat_stream failed: {exc}")
                    try:
                        loop.call_soon_threadsafe(queue.put_nowait, {"error": str(exc)})
                    except Exception:
                        pass
                finally:
                    try:
                        loop.call_soon_threadsafe(queue.put_nowait, None)
                    except Exception:
                        pass

            producer_task = asyncio.create_task(asyncio.to_thread(producer))
            try:
                while True:
                    try:
                        chunk = await asyncio.wait_for(
                            queue.get(),
                            timeout=self.stream_idle_timeout_s,
                        )
                    except asyncio.TimeoutError:
                        logger.error(
                            "OllamaService.chat_stream idle timeout after %ss for model %s",
                            self.stream_idle_timeout_s,
                            target_model,
                        )
                        yield {
                            "error": (
                                f"AI stream timed out after {int(self.stream_idle_timeout_s)}s "
                                "without receiving a token."
                            )
                        }
                        break
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
