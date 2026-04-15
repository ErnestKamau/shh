import re
import logging
from typing import List, Dict, Any
from python.ai_service.core.reasoning_schemas import HopResult

logger = logging.getLogger(__name__)

class GroundingVerifier:
    """
    Final safety gate that checks generated LLM responses against 
    retrieved metadata (status, IDs, dates) to prevent industrial contradictions.
    """
    
    def verify(self, answer: str, source_chunks: List[Dict[str, Any]]) -> Dict[str, Any]:
        """
        Returns a dict with 'is_grounded' and 'violations'.
        """
        violations = []
        
        # 1. Pattern Matching for Status Contradictions
        # If metadata says 'open/failed' but answer says 'closed/passed'
        status_map = {}
        for chunk in source_chunks:
            eid = str(chunk.get('entity_id'))
            status = chunk.get('metadata', {}).get('status', '').lower()
            if eid and status:
                status_map[eid] = status

        # Simple check for each entity ID mentioned in the answer
        for eid, expected_status in status_map.items():
            if eid in answer:
                # Basic contradiction detection
                if expected_status in ['open', 'failed', 'overdue', 'warning'] and \
                   re.search(r'\b(closed|passed|completed|healthy|resolved)\b', answer.lower()):
                    violations.append(f"Contradiction: Answer suggests {eid} is resolved/passed, but metadata says '{expected_status}'.")

        # 2. Citation Check
        # Does the answer cite at least one entity ID if chunks were provided?
        if source_chunks and not re.search(r'\[.*\]|[A-Z]{2,}-\d+', answer):
            violations.append("Missing Citation: Answer does not cite specific entity IDs despite finding context.")

        return {
            "is_grounded": len(violations) == 0,
            "violations": violations
        }
