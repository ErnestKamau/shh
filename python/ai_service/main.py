import logging
import os
import time
import asyncio
from fastapi import FastAPI, Depends
from fastapi.middleware.cors import CORSMiddleware

from python.ai_service.config.settings import settings
from python.ai_service.middleware.request_id import RequestContextMiddleware
from python.ai_service.routers import chat, health, indexing

# Configure Logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s | %(levelname)-8s | %(name)s:%(funcName)s:%(lineno)d - %(message)s"
)
logger = logging.getLogger(__name__)

def create_app() -> FastAPI:
    app = FastAPI(
        title=settings.app_name,
        version=settings.version,
        description="Streamlined Operational AI Assistant for Laboratory and Inventory Management",
        docs_url="/docs" if settings.debug else None,
        redoc_url="/redoc" if settings.debug else None,
    )

    # Middlewares
    app.add_middleware(
        CORSMiddleware,
        allow_origins=["*"],
        allow_methods=["*"],
        allow_headers=["*"],
    )
    app.add_middleware(RequestContextMiddleware)

    # Routers
    app.include_router(health.router)
    app.include_router(chat.router)
    app.include_router(indexing.router)

    return app

from fastapi.exceptions import RequestValidationError
from fastapi.responses import JSONResponse

app = create_app()

@app.exception_handler(RequestValidationError)
async def validation_exception_handler(request, exc):
    logger.error(f"Validation error: {exc.errors()}")
    return JSONResponse(
        status_code=422,
        content={"detail": exc.errors(), "body": exc.body},
    )

@app.on_event("startup")
async def startup_event():
    logger.info(f"Starting {settings.app_name} v{settings.version}...")
    logger.info(f"⚙️ Dynamic Hardware Profile: '{settings.hardware_profile.upper()}'")
    logger.info(
        f"🤖 Model Configuration -> Chat: '{settings.chat_model}' | "
        f"Heavy: '{settings.heavy_model}' | Coder: '{settings.coder_model}'"
    )
    # Ensure observability table exists
    try:
        from python.ai_service.core.request_logger import request_logger
        request_logger.ensure_table()
    except Exception as exc:
        logger.warning(f"Could not ensure request logs table on startup: {exc}")
