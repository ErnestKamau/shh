import asyncio
import logging
import sys
from python.ai_service.core.query_decomposer import QueryDecomposer
from python.ai_service.services.ollama_service import OllamaService
from python.ai_service.services.retrieval_service import RetrievalService
from python.ai_service.routers.chat import chat
from python.ai_service.schemas.chat import ChatRequest
from python.ai_service.config.settings import settings

# Setup logging to see the dispatcher rationale
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

async def test_equipment_hybrid():
    print("\n" + "="*60)
    print("TEST: Equipment Hybrid Logic (SOP + Factual Status)")
    print("="*60)
    
    ollama = OllamaService()
    retrieval = RetrievalService()
    decomposer = QueryDecomposer(ollama)
    
    # The failing scenario: A hybrid query requiring both RAG and SQL
    # "SOP for calibration" -> RAG
    # "check equipment status" -> SQL (equipment_downtime_summary)
    query = "Provide the SOP for equipment calibration and check the current downtime status."
    
    print(f"\nTarget Query: \"{query}\"")
    
    # 1. Test Decomposition
    graph = decomposer.decompose(query, mode="balanced")
    print("\n--- DECOMPOSITION PLAN ---")
    print(f"Class: {graph.query_class}")
    print(f"Rationale: {graph.rationale}")
    for i, step in enumerate(graph.steps):
        print(f"  Step {i+1}: Type={step.type.upper()}, Goal={step.goal}, Intent={step.sub_query}")

    # 2. Verify we have the correct structure
    has_sql = any(s.type == "sql" for s in graph.steps)
    has_rag = any(s.type == "rag" for s in graph.steps)
    
    if has_sql and has_rag:
        print("\n✅ SUCCESS: Correctly identified Hybrid intent (SQL + RAG).")
    else:
        print("\n❌ FAILURE: Missing intent. Expected both SQL and RAG steps.")

    # 3. Simulate Execution
    print("\n--- SIMULATING EXECUTION ---")
    request = ChatRequest(
        messages=[{"role": "user", "content": query}],
        company_id=1
    )
    
    try:
        settings.orchestration_mode = "balanced"
        response = await chat(request, ollama=ollama, retrieval=retrieval)
        print(f"\nAI Response Fragment:\n{response.reply[:300]}...")
        
        if "AUTHORITATIVE SYSTEM DATA" in response.reply:
            print("\n✅ SUCCESS: System Data fragment injected into response.")
        else:
            print("\n⚠️  WARNING: No authoritative data found in response. (Check SQL results)")
            
    except Exception as e:
        print(f"\n❌ Execution Error: {e}")

if __name__ == "__main__":
    asyncio.run(test_equipment_hybrid())
