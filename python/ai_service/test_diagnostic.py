import asyncio
import logging
from ai_service.services.ollama_service import OllamaService
from ai_service.services.retrieval_service import RetrievalService
from ai_service.services.live_data_service import LiveDataService
from ai_service.services.visualization_service import visualization_service
from ai_service.core.simple_assistant import SimpleAssistant
from ai_service.core.intermediate_assistant import IntermediateAssistant

logging.basicConfig(level=logging.INFO)

async def test_assistant():
    print("Testing Assistant Initialization...")
    ollama = OllamaService()
    retrieval = RetrievalService()
    live_data = LiveDataService()
    
    simple = SimpleAssistant(ollama, live_data, retrieval, visualization_service)
    intermediate = IntermediateAssistant(ollama, simple)
    
    print("Testing FastPath ('what can you do')...")
    try:
        res = await intermediate.process_query(
            message="what can you do?",
            messages=[{"role": "user", "content": "what can you do?"}],
            company_id=1
        )
        print(f"Result: {res}")
    except Exception as e:
        print(f"FastPath Failed: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    asyncio.run(test_assistant())
