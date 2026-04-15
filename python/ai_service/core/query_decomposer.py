import json
import logging
from typing import Dict, Any, List
from python.ai_service.core.reasoning_schemas import ReasoningGraph, ReasoningStep, QueryClass
from python.ai_service.services.ollama_service import OllamaService

logger = logging.getLogger(__name__)

class QueryDecomposer:
    """
    Analyzes a complex user query and decomposes it into a structured sequence 
    of retrieval steps (ReasoningGraph).
    """
    
    def __init__(self, ollama: OllamaService):
        self.ollama = ollama
        self.model = "qwen2.5:3b" # Could be a faster/smaller model for planning

    def decompose(self, query: str, mode: str = "balanced") -> ReasoningGraph:
        """
        Uses LLM to perform query classification and generate a mode-aware structured plan.
        """
        prompt = f"""
Analyze the following user query for a Laboratory Information Management System (LIMS).
First, classify the query and then generate a structured execution plan.

Query: "{query}"
Active Mode: "{mode}"

Output a JSON object following the ReasoningGraph schema.

### CORE TASK: Multi-Intent Detection
A single sentence may contain MULTIPLE intents. You MUST extract every specific goal.
Example: "How many samples (Intent 1) and what is the SOP (Intent 2)?" -> Hybrid query with 2 steps.

### Classification Rules:
1. **query_class**: 
   - 'aggregate': Counts, totals, sums, averages.
   - 'lookup': Status of specific IDs.
   - 'semantic': Explanations, SOPs, manual lookups.
   - 'hybrid': BOTH facts/stats AND semantic explanations in one query.
2. **requires_authoritative_source**: Set to true if ANY part of the query is 'aggregate' or 'lookup'.

### Canonical Manifest Keys (USE THESE for 'sql' sub_queries):
- 'sample_count_total', 'sample_count_pending_review', 'sample_count_in_lab', 'samples_by_status'
- 'batch_count_total', 'batch_count_in_lab'
- 'inventory_low_stock', 'inventory_order_status'
- 'equipment_utilization', 'equipment_maintenance_schedule', 'equipment_downtime_summary'
- 'qc_pass_rate', 'capa_pending'

### Step Generation Rules:
- **type**: 'sql' for aggregate facts; 'rag' for procedures or summaries.
- **sub_query**: 
  - For 'sql' type: MUST BE ONE OF THE CANONICAL KEYS ABOVE.
  - For 'rag' type: A semantic search string.
- **sql_params**: For 'sql' type, output a dictionary of any required parameter bindings (e.g., {{"status": "Samples In Lab"}}).
- A query for "low stock" MUST use 'inventory_low_stock'.
- A query for "equipment status" or "downtime" SHOULD use 'equipment_downtime_summary'.
- A query for "maintenance" or "next service" SHOULD use 'equipment_maintenance_schedule'.
- A query asking for "samples" (counts/totals) MUST use the relevant 'sample_*' key.
- A query for "waiting for review", "pending review", "awaiting verification", or "sign-off" MUST use 'sample_count_pending_review'.
- A query asking for "batches" MUST use the relevant 'batch_*' key.

Guidelines:
- If query is 'how many samples', one 'sql' step is enough.
- If query is 'how many batches', one 'sql' step is enough.
- Composite Query: "Explain procedure X (rag) and check low stock (sql)." -> 2 steps.
- Composite Query: "SOP for calibration (rag) and check equipment status (sql)." -> 2 steps.
- Response must be ONLY valid JSON.
"""
        try:
            response_text = self.ollama.generate(prompt)
            # Find JSON in response
            start_idx = response_text.find("{")
            end_idx = response_text.rfind("}") + 1
            if start_idx == -1 or end_idx == 0:
                return self._default_graph(query, mode)
                
            data = json.loads(response_text[start_idx:end_idx])
            # Ensure mode is injected into graph
            data["mode_policy"] = mode
            # Auto-assign step IDs if the LLM omitted them
            for i, step in enumerate(data.get("steps", [])):
                if "id" not in step or not step["id"]:
                    step["id"] = f"step_{i+1}"
            return ReasoningGraph(**data)
        except Exception as e:
            logger.error(f"QueryDecomposer failed: {e}")
            return self._default_graph(query, mode)

    def _default_graph(self, query: str, mode: str) -> ReasoningGraph:
        return ReasoningGraph(
            query_class=QueryClass.SEMANTIC,
            mode_policy=mode,
            steps=[ReasoningStep(
                id="step_1",
                goal="General retrieval",
                domain="all",
                sub_query=query,
                type="rag"
            )]
        )
