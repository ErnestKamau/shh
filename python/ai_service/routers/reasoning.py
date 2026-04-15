import asyncio
import logging
from fastapi import APIRouter, Depends, HTTPException
from python.ai_service.schemas.reasoning import ReasoningRequest, ReasoningResponse
from python.ai_service.services.reasoning_service import ReasoningService
from python.ai_service.services.ollama_service import OllamaService

router = APIRouter(tags=["Reasoning"], prefix="/v1/reason")
logger = logging.getLogger(__name__)

# Reusing the global reasoning semaphore if defined elsewhere, 
# but for now independent or defined in a shared dependency.
_reasoning_semaphore = asyncio.Semaphore(2)

def get_reasoning_service():
    ollama = OllamaService()
    return ReasoningService(ollama)

@router.post("/interpret_tat", response_model=ReasoningResponse)
async def interpret_tat(
    request: ReasoningRequest,
    service: ReasoningService = Depends(get_reasoning_service)
):
    async with _reasoning_semaphore:
        result = await asyncio.to_thread(
            service.interpret_prediction, "tat", request.prediction, request.features, request.context
        )
    return ReasoningResponse(reasoning=result)

@router.post("/analyze_equipment", response_model=ReasoningResponse)
async def analyze_equipment(
    request: ReasoningRequest,
    service: ReasoningService = Depends(get_reasoning_service)
):
    async with _reasoning_semaphore:
        result = await asyncio.to_thread(
            service.interpret_prediction, "maintenance", request.prediction, request.features, request.context
        )
    return ReasoningResponse(reasoning=result)

@router.post("/explain_qc", response_model=ReasoningResponse)
async def explain_qc(
    request: ReasoningRequest,
    service: ReasoningService = Depends(get_reasoning_service)
):
    async with _reasoning_semaphore:
        result = await asyncio.to_thread(
            service.interpret_prediction, "qc", request.prediction, request.features, request.context
        )
    return ReasoningResponse(reasoning=result)
