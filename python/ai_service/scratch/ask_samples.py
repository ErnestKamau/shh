import asyncio
import sys
import os

# Ensure project root is in sys.path
sys.path.append(os.getcwd())

from ai_service.core.simple_assistant import SimpleAssistant
from ai_service.services.ollama_service import OllamaService
from ai_service.services.retrieval_service import RetrievalService
from ai_service.services.live_data_service import LiveDataService

async def main():
    ollama = OllamaService()
    retrieval = RetrievalService()
    live_data = LiveDataService()
    assistant = SimpleAssistant(ollama, live_data, retrieval)

    query = "how many samples are in the system"
    print(f"Testing Query: {query}")
    
    result = await asyncio.to_thread(assistant.process_query, query, company_id=1)
    
    print("-" * 30)
    print(f"ROUTE: {result['meta']['route']}")
    print(f"ANSWER: {result['answer']}")
    print("-" * 30)

if __name__ == "__main__":
    asyncio.run(main())
