import os
import json
import logging
from typing import List, Dict, Any, Optional
from python.ai_service.services.ollama_service import OllamaService

logger = logging.getLogger(__name__)

class ReasoningService:
    def __init__(self, ollama_service: OllamaService):
        self.ollama = ollama_service
        self.model = os.getenv("AI_HEAVY_MODEL", os.getenv("OLLAMA_MODEL", "qwen2.5:3b"))

    def classify_intent(self, message: str, canonical_intents: Dict[str, Any], system_prompt: str) -> Optional[Dict[str, Any]]:
        """Classifies user intent into canonical keys using Ollama."""
        try:
            response = self.ollama.client.chat(
                model=self.model,
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": message},
                ],
                options={"temperature": 0, "num_predict": 300},
                format="json",
            )

            raw_text = response["message"]["content"].strip()
            parsed = json.loads(raw_text)

            # Basic validation
            intent = parsed.get("intent", "fallback_rag")
            if intent not in canonical_intents:
                intent = "fallback_rag"

            return {
                "intent": intent,
                "confidence": max(0.0, min(1.0, float(parsed.get("confidence", 0.0)))),
                "type": parsed.get("type", "meta"),
                "entities": parsed.get("entities", {}),
                "confidence_reason": str(parsed.get("confidence_reason", ""))[:200],
            }
        except Exception as e:
            logger.error(f"ReasoningService.classify_intent failed: {e}")
            return None

    def interpret_prediction(self, kind: str, prediction: Dict[str, Any], features: Optional[Dict[str, Any]], context: Optional[List[Dict[str, Any]]] = None) -> Dict[str, Any]:
        """Produce structured reasoning over ML predictions."""
        prompts = {
            "tat": "You are a lab operations reasoning assistant. Interpret TAT prediction outputs in strict JSON.",
            "maintenance": "You are a lab equipment reliability reasoning assistant. Analyze equipment failure risk in strict JSON.",
            "qc": "You are a quality-control reasoning assistant for laboratory analytes. Explain anomaly/drift prediction in strict JSON."
        }
        
        system_prompt = prompts.get(kind, "You are a specialized AI assistant. Interpret the prediction in strict JSON.")
        
        payload = {
            "task": f"{kind}_interpretation",
            "prediction": prediction,
            "features": features or {},
            "context": context or [],
        }

        try:
            # Reusing ollama_service.chat but enforcing JSON format if supported natively by service wrapper
            # For now, use raw client to ensure 'format="json"'
            response = self.ollama.client.chat(
                model=self.model,
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": json.dumps(payload, default=str)},
                ],
                options={"temperature": 0.1, "num_predict": 500},
                format="json",
            )
            
            result = json.loads(response["message"]["content"].strip())
            result["reasoning_model"] = self.model
            result["features_used"] = list((features or {}).keys())[:12]
            return result
        except Exception as exc:
            logger.warning(f"ReasoningService fallback for {kind}: {exc}")
            return self._fallback_reasoning(f"{kind.upper()} Interpretation", prediction, features)

    def _fallback_reasoning(self, headline: str, prediction: Dict[str, Any], features: Optional[Dict[str, Any]]) -> Dict[str, Any]:
        return {
            "headline": headline,
            "interpretation": "Reasoning engine fallback mode active.",
            "key_factors": ["Prediction confidence", "Risk level"],
            "recommendations": ["Review latest snapshot freshness."],
            "confidence_note": f"Model confidence: {prediction.get('confidence', 0)}",
            "reasoning_model": "fallback",
            "features_used": list((features or {}).keys())[:12],
        }
