import re
from typing import List, Dict, Any, Optional
from dataclasses import dataclass
from python.ai_service.core.reasoning_schemas import ReasoningStep

@dataclass
class SearchPlan:
    query: str
    mode: str = "hybrid"
    collections: Optional[List[str]] = None
    exact_id: Optional[str] = None
    candidate_limit: int = 20
    abstention_logic: str = "domain" # ["exact", "domain", "exploratory"]
    metadata_filters: Optional[Dict[str, Any]] = None

class RetrievalPlanner:
    """
    Translates a ReasoningStep (from QueryDecomposer) into a concrete SearchPlan.
    """
    
    def plan_step(self, step: ReasoningStep, prev_results: List[Dict[str, Any]] = None) -> SearchPlan:
        query = step.sub_query
        
        # 1. Parameter Injection (Sequential Binding)
        # If this step depends on a prior step, try to extract IDs from prev_results
        enriched_query = query
        metadata_filters = {}
        
        if prev_results and step.depends_on:
            # Simple heuristic: find 'entity_id' of the domain we want or equipment_id
            target_ids = []
            for res in prev_results:
                # Look for metadata that matches Step requirements
                eid = res.get('entity_id')
                if eid:
                    target_ids.append(eid)
            
            if target_ids:
                # Enrich the query string or add metadata filter
                enriched_query = f"{query} for {' '.join(target_ids[:2])}"
                # For exact ID extraction in retrieval service, we set exact_id if found
                # metadata_filters['entity_id'] = target_ids[0]

        # 2. Build Plan
        plan = SearchPlan(
            query=enriched_query,
            mode="hybrid",
            collections=[step.domain] if step.domain != "all" else None,
            candidate_limit=20,
            abstention_logic="domain" if step.domain != "all" else "exploratory",
            metadata_filters=metadata_filters if metadata_filters else None
        )
        
        # Override for exact lookups if the sub_query looks like an ID
        if re.search(r"[A-Z]{2,}-\d+", enriched_query):
            plan.mode = "lexical_first"
            plan.abstention_logic = "exact"
            # Extract id
            match = re.search(r"([A-Z]{2,}-\d+)", enriched_query)
            if match:
                plan.exact_id = match.group(1)

        return plan
