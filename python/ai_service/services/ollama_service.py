import os
import logging
import asyncio
from typing import List, Dict, Any, Optional, AsyncGenerator

logger = logging.getLogger(__name__)

class OllamaService:
    def __init__(self):
        self.host = os.getenv("OLLAMA_HOST", "http://localhost:11434")
        self.model = os.getenv("OLLAMA_MODEL", "qwen2.5:3b")
        try:
            import ollama
            self.client = ollama.Client(host=self.host)
        except ImportError:
            self.client = None
            logger.warning("Ollama library not found. Service will only work with mocked calls.")

    def chat(self, messages: List[Dict[str, str]], options: Optional[Dict[str, Any]] = None) -> Dict[str, Any]:
        """Synchronous chat call."""
        try:
            return self.client.chat(
                model=self.model,
                messages=messages,
                options=options or {"temperature": 0.3}
            )
        except Exception as e:
            logger.error(f"OllamaService.chat failed: {e}")
            raise

    async def chat_stream(self, messages: List[Dict[str, str]], options: Optional[Dict[str, Any]] = None, model: Optional[str] = None) -> AsyncGenerator[Dict[str, Any], None]:
        """Stream Ollama responses without blocking the event loop."""
        try:
            target_model = model or self.model
            queue: asyncio.Queue[Optional[Dict[str, Any]]] = asyncio.Queue()
            loop = asyncio.get_running_loop()

            def producer() -> None:
                try:
                    stream = self.client.chat(
                        model=target_model,
                        messages=messages,
                        options=options or {"temperature": 0.3},
                        stream=True
                    )
                    for chunk in stream:
                        loop.call_soon_threadsafe(queue.put_nowait, chunk)
                except Exception as exc:
                    logger.error(f"OllamaService.chat_stream failed: {exc}")
                    loop.call_soon_threadsafe(queue.put_nowait, {"error": str(exc)})
                finally:
                    loop.call_soon_threadsafe(queue.put_nowait, None)

            producer_task = asyncio.create_task(asyncio.to_thread(producer))
            try:
                while True:
                    chunk = await queue.get()
                    if chunk is None:
                        break
                    yield chunk
            finally:
                await producer_task
        except Exception as e:
            logger.error(f"OllamaService.chat_stream failed: {e}")
            yield {"error": str(e)}

    def generate(self, prompt: str, system: Optional[str] = None) -> str:
        """Simple generation utility."""
        messages = []
        if system:
            messages.append({"role": "system", "content": system})
        messages.append({"role": "user", "content": prompt})
        
        response = self.chat(messages)
        return response['message']['content']
