import sys
import os
import json
import asyncio
from typing import Dict, Any

# Ensureproject root is in sys.path
sys.path.append(os.getcwd())

from python.ai_service.core.simple_assistant import SimpleAssistant
from python.ai_service.services.ollama_service import OllamaService
from python.ai_service.services.retrieval_service import RetrievalService
from python.ai_service.services.live_data_service import LiveDataService

async def test_simple_assistant():
    print("--- Starting SimpleAssistant Verification ---")
    
    # Initialize components
    ollama = OllamaService()
    retrieval = RetrievalService()
    live_data = LiveDataService()
    
    assistant = SimpleAssistant(ollama, live_data, retrieval)
    
    test_queries = [
        "How many samples are currently in the lab?", # SQL path
        "What are the safety protocols for handling acid?", # RAG path
        "Hello, who are you?", # Conversational path
    ]
    
    for query in test_queries:
        print(f"\nQUERY: {query}")
        try:
            # We call the synchronous process_query in a thread to mimic the FastAPI router
            result = await asyncio.to_thread(assistant.process_query, query, company_id=1)
            
            print(f"ROUTE: {result['meta']['route']}")
            print(f"ANSWER: {result['answer'][:100]}...")
            print(f"SOURCES: {len(result['sources'])}")
            
            if not result['answer']:
                print("FAIL: Empty answer returned")
            else:
                print("SUCCESS: Answer received")
                
        except Exception as e:
            print(f"ERROR: {str(e)}")

if __name__ == "__main__":
    asyncio.run(test_simple_assistant())
