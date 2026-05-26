import os
import logging
from pydantic import model_validator
from pydantic_settings import BaseSettings

logger = logging.getLogger(__name__)

def detect_hardware_profile() -> str:
    """
    Detects if the hardware profile is 'minimum' or 'optimum'.
    Allows manual override via 'AI_HARDWARE_PROFILE' environment variable.
    """
    # 1. Manual override
    profile = os.getenv("AI_HARDWARE_PROFILE", "").lower().strip()
    if profile in ("minimum", "optimum"):
        logger.info(f"Hardware Profile: Manual override active -> '{profile}'")
        return profile
        
    # 2. Dynamic detection (RAM-based on Linux)
    try:
        if os.path.exists("/proc/meminfo"):
            with open("/proc/meminfo", "r") as f:
                for line in f:
                    if line.startswith("MemTotal:"):
                        parts = line.split()
                        total_kb = int(parts[1])
                        total_gb = total_kb / (1024 * 1024)
                        logger.info(f"Hardware Profile: Detected MemTotal = {total_gb:.2f} GB")
                        # Classify systems under 12GB RAM as 'minimum'
                        if total_gb < 12.0:
                            logger.info("Hardware Profile: Classifying as 'minimum' (< 12GB RAM)")
                            return "minimum"
                        else:
                            logger.info("Hardware Profile: Classifying as 'optimum' (>= 12GB RAM)")
                            return "optimum"
    except Exception as e:
        logger.warning(f"Hardware Profile: RAM detection failed: {e}")
        
    # Fallback default
    logger.info("Hardware Profile: Falling back to default 'minimum'")
    return "minimum"

# Resolve defaults based on profile
profile = detect_hardware_profile()
if profile == "minimum":
    default_heavy = "gemma3:1b"
    default_chat = "gemma3:1b"
    default_coder = "gemma3:1b"
else:
    default_heavy = "qwen2.5:3b"
    default_chat = "gemma3:1b"
    default_coder = "qwen2.5-coder"

class Settings(BaseSettings):
    # App Settings
    app_name: str = os.getenv("APP_NAME", "LIMS").replace('"', '').replace("'", "").strip() + " AI Service"
    version: str = "1.0.0"
    debug: bool = os.getenv("DEBUG", "false").lower() == "true"
    
    # Dynamic Hardware Profile
    hardware_profile: str = os.getenv("AI_HARDWARE_PROFILE", profile)
    
    # Port & Host
    host: str = "0.0.0.0"
    port: int = int(os.getenv("AI_SERVICE_PORT", "8081"))
    
    # Orchestration Mode: rag_only, live_data_only, balanced
    orchestration_mode: str = os.getenv("AI_ORCHESTRATION_MODE", "balanced")
    
    # Ollama Settings
    ollama_host: str = os.getenv("OLLAMA_HOST", "http://localhost:11434")
    
    # Dynamic Hardware-based Model Selection
    ollama_model: str = os.getenv("AI_HEAVY_MODEL", os.getenv("OLLAMA_MODEL", default_heavy))
    chat_model: str = os.getenv("AI_CHAT_MODEL", default_chat)
    heavy_model: str = os.getenv("AI_HEAVY_MODEL", os.getenv("OLLAMA_MODEL", default_heavy))
    coder_model: str = os.getenv("AI_CODER_MODEL", default_coder)
    
    # database & Schema
    ai_schema: str = os.getenv("AI_SCHEMA", "ai")
    embedding_dim: int = int(os.getenv("AI_EMBEDDING_DIM", "1536"))
    embedding_model: str = os.getenv("AI_EMBEDDING_MODEL", "all-MiniLM-L6-v2")

    @model_validator(mode="after")
    def append_ai_service(self) -> 'Settings':
        raw_name = self.app_name.replace('"', '').replace("'", "").strip()
        if not raw_name.endswith(" AI Service"):
            self.app_name = f"{raw_name} AI Service"
        else:
            self.app_name = raw_name
        return self

    class Config:
        env_file = ".env"
        extra = "ignore"

settings = Settings()
