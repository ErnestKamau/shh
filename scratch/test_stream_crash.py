import asyncio
from python.ai_service.routers.chat import chat_stream, ChatStreamRequest, OllamaService, RetrievalService

async def simulate_stream_crash():
    print("Simulating stream execution to check for process crashes...")
    
    ollama = OllamaService()
    retrieval = RetrievalService()
    
    request = ChatStreamRequest(
        messages=[{"role": "user", "content": "How many samples since start of system?"}],
        company_id=1,
        user_id=1
    )
    
    try:
        async for chunk in chat_stream(request, None, ollama, retrieval):
            print(f"CHUNK: {chunk[:100]}...")
        print("STREAM COMPLETED SUCCESSFULY")
    except Exception as e:
        print(f"STREAM FAILED: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    asyncio.run(simulate_stream_crash())
