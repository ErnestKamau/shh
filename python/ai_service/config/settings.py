import os
from pydantic_settings import BaseSettings

class Settings(BaseSettings):
    # App Settings
    app_name: str = "Polucon Unified AI Service"
    version: str = "1.0.0"
    debug: bool = os.getenv("DEBUG", "false").lower() == "true"
    
    # Port & Host
    host: str = "0.0.0.0"
    port: int = int(os.getenv("AI_SERVICE_PORT", "8081"))
    
    # Orchestration Mode: rag_only, live_data_only, balanced
    orchestration_mode: str = os.getenv("AI_ORCHESTRATION_MODE", "balanced")
    
    # Ollama Settings
    ollama_host: str = os.getenv("OLLAMA_HOST", "http://localhost:11434")
    # Backward compatibility: if AI_HEAVY_MODEL is not set, fall back to OLLAMA_MODEL.
    ollama_model: str = os.getenv("AI_HEAVY_MODEL", os.getenv("OLLAMA_MODEL", "qwen2.5:3b"))
    chat_model: str = os.getenv("AI_CHAT_MODEL", "gemma3:1b")
    heavy_model: str = os.getenv("AI_HEAVY_MODEL", os.getenv("OLLAMA_MODEL", "qwen2.5:3b"))
    
    # database & Schema
    ai_schema: str = os.getenv("AI_SCHEMA", "ai")
    embedding_dim: int = int(os.getenv("AI_EMBEDDING_DIM", "1536"))
    embedding_model: str = os.getenv("AI_EMBEDDING_MODEL", "all-MiniLM-L6-v2")

    class Config:
        env_file = ".env"
        extra = "ignore"

settings = Settings()
