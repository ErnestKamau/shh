"""
Model Discovery Service
Scans the local filesystem for .joblib models and registers them in the DB.
"""
from __future__ import annotations

import os
import re
from pathlib import Path
from typing import List, Dict, Any
from loguru import logger

from python.ai_service.core.model_registry_service import ModelRegistryService, MODEL_TYPES

# ARTIFACT_DIR should point to the models/ folder in the project root
ROOT_DIR = Path(__file__).resolve().parents[3]
MODELS_DIR = ROOT_DIR / "models"

class ModelDiscoveryService:
    def __init__(self) -> None:
        self.registry = ModelRegistryService()
        if not MODELS_DIR.exists():
            MODELS_DIR.mkdir(parents=True, exist_ok=True)

    def discover_and_register(self) -> Dict[str, Any]:
        """
        Scans MODELS_DIR for .joblib files and registers any that are missing.
        Returns a summary of actions taken.
        """
        registered_count = 0
        skipped_count = 0
        found_files = list(MODELS_DIR.glob("*.joblib"))
        
        logger.info(f"Found {len(found_files)} .joblib files in {MODELS_DIR}")

        # Regex to match {model_type}_{version/timestamp}.joblib
        # Example: tat_prediction_20260401093656.joblib
        pattern = re.compile(r"^(.+?)_(\d{14})\.joblib$")

        for file_path in found_files:
            match = pattern.match(file_path.name)
            if not match:
                logger.debug(f"Skipping file with unrecognized name format: {file_path.name}")
                skipped_count += 1
                continue

            model_type_candidate = match.group(1)
            version = match.group(2)

            # Validate against allowed model types in the registry
            if model_type_candidate not in MODEL_TYPES:
                logger.debug(f"Skipping file with unknown model type '{model_type_candidate}': {file_path.name}")
                skipped_count += 1
                continue

            # Check if this specific version is already registered
            existing = self.registry.list_by_type(model_type_candidate, include_deprecated=True)
            if any(m['version'] == version for m in existing):
                logger.trace(f"Model {model_type_candidate} v{version} already registered.")
                skipped_count += 1
                continue

            # Register the model
            try:
                model_name = model_type_candidate.replace('_', ' ').title()
                self.registry.register(
                    model_name=model_name,
                    model_type=model_type_candidate,
                    version=version,
                    framework="sklearn",
                    artifact_path=str(file_path.absolute()),
                    training_rows=0, # Unknown from filename
                    metrics={}, # Unknown from filename
                    is_active=False # Trained models require manual activation
                )
                logger.info(f"Successfully registered orphaned model: {file_path.name}")
                registered_count += 1
            except Exception as e:
                logger.error(f"Failed to register {file_path.name}: {e}")

        return {
            "total_found": len(found_files),
            "registered": registered_count,
            "skipped": skipped_count
        }

    def sync_ollama_models(self) -> Dict[str, Any]:
        """
        Synchronizes LLM models from the local Ollama instance.
        Matches the behavior of the original PHP implementation.
        """
        import requests
        from python.py_etl.config.config import settings
        from sqlalchemy import text
        from python.py_etl.core.database import db_manager

        host = settings.ollama_host
        try:
            response = requests.get(f"{host}/api/tags", timeout=5)
            response.raise_for_status()
            models = response.json().get('models', [])
        except Exception as e:
            logger.error(f"Ollama connection failed at {host}: {e}")
            return {"success": False, "error": str(e)}

        synced_count = 0
        seen_ids = []

        for m in models:
            full_name = m['name']
            parts = full_name.split(':')
            name = parts[0]
            version = parts[1] if len(parts) > 1 else "latest"

            # Register/Update model (Ollama models are auto-activated)
            try:
                model_id = self.registry.register(
                    model_name=name,
                    model_type="llm",
                    version=version,
                    framework="Ollama",
                    is_active=True
                )
                seen_ids.append(model_id)
                synced_count += 1
            except Exception as e:
                logger.error(f"Failed to sync Ollama model {full_name}: {e}")

        # Deactivate any Ollama models that are no longer present
        deactivated_count = 0
        if seen_ids:
            sql = text("""
                UPDATE ai.ai_model_registry
                SET is_active = FALSE, updated_at = NOW()
                WHERE framework = 'Ollama'
                  AND is_active = TRUE
                  AND id NOT IN :seen_ids
            """)
            with db_manager.postgres_connection() as conn:
                res = conn.execute(sql, {"seen_ids": tuple(seen_ids)})
                deactivated_count = res.rowcount
                conn.commit()

        return {
            "success": True,
            "synced_count": synced_count,
            "deactivated_count": deactivated_count
        }

if __name__ == "__main__":
    service = ModelDiscoveryService()
    result = service.discover_and_register()
    print(result)
