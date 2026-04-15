import logging
import os
import time
from fastapi import FastAPI, Depends
from fastapi.middleware.cors import CORSMiddleware

from python.ai_service.config.settings import settings
from python.ai_service.middleware.request_id import RequestContextMiddleware
from python.ai_service.routers import chat, prediction, reasoning, features, registry, health, etl_status

# Configure Logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s | %(levelname)-8s | %(name)s:%(funcName)s:%(lineno)d - %(message)s"
)
logger = logging.getLogger(__name__)

# --- Constants Path (Preserved for compatibility) ---
# These are used by routers that haven't fully migrated their constants yet
from python.ai_service.constants import CANONICAL_INTENTS, _CLASSIFIER_SYSTEM_PROMPT

def create_app() -> FastAPI:
    app = FastAPI(
        title=settings.app_name,
        version=settings.version,
        description="Modular Unified AI Service for Laboratory and Inventory Management",
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
    app.include_router(prediction.router)
    app.include_router(reasoning.router)
    app.include_router(features.router)
    app.include_router(registry.router)
    app.include_router(registry.audit_router)
    app.include_router(registry.gov_router)
    app.include_router(etl_status.router)

    @app.on_event("startup")
    async def startup_event():
        logger.info(f"Starting {settings.app_name} v{settings.version}")
        # Add dependency checks here if needed
        logger.info("Service is ready to handle requests")

    @app.on_event("shutdown")
    async def shutdown_event():
        logger.info("Shutting down service")

    return app

app = create_app()

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "python.ai_service.main:app",
        host=settings.host,
        port=settings.port,
        reload=settings.debug
    )
