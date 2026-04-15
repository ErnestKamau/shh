import asyncio
from python.ai_service.core.query_decomposer import QueryDecomposer
from python.ai_service.services.ollama_service import OllamaService
from python.ai_service.services.retrieval_service import RetrievalService
from python.ai_service.routers.chat import chat
from python.ai_service.schemas.chat import ChatRequest
from python.ai_service.config.settings import settings

async def run_test():
    ollama = OllamaService()
    retrieval = RetrievalService()
    decomposer = QueryDecomposer(ollama)
    
    test_queries = [
        "How many samples are in the lab?",
        "Explain the procedure for inventory replenishment and check if anything is low stock."
    ]
    
    modes = ["balanced", "rag_only"]
    
    for mode in modes:
        print(f"\n{'='*20} TESTING MODE: {mode} {'='*20}")
        settings.orchestration_mode = mode
        
        for q in test_queries:
            print(f"\nUser Query: {q}")
            
            # Test Decomposer
            graph = decomposer.decompose(q, mode=mode)
            print(f"Plan: {graph.query_class} | Steps: {[s.type for s in graph.steps]}")
            
            # Simulate Chat Orchestration (Manually passing dependencies)
            request = ChatRequest(
                messages=[{"role": "user", "content": q}],
                company_id=1
            )
            
            try:
                # Bypass dependency injection by calling chat with explicit services
                response = await chat(request, ollama=ollama, retrieval=retrieval)
                print(f"Reply: {response.reply[:200]}...")
                print(f"Sources: {len(response.sources)} document chunks")
            except Exception as e:
                print(f"Execution Error: {e}")

if __name__ == "__main__":
    asyncio.run(run_test())
