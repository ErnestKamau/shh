import re
from typing import Dict, Any, List, Optional

class QueryClassifier:
    """
    Analyzes user queries to determine intent, domain, and extract key identifiers.
    Enables more precise RAG retrieval by scoping searches.
    """
    
    # Patterns for LIMS identifiers
    ID_PATTERNS = {
        "document": r"(?:DOC|SOP)-\d{4}-\d+",
        "equipment": r"EQ-\d+",
        "sample": r"(?:PO-QC|SAM)-[A-Z0-9-]+",
        "inventory": r"INV-\d+",
        "audit": r"(?:AUDIT|AUD|AF)-\d+",
        "capa": r"CAPA-\d+"
    }
    
    # Keywords mapped to RAG collections
    DOMAIN_KEYWORDS = {
        "equipment": ["equipment", "machine", "instrument", "calibrate", "maintenance", "incubator", "meter", "repair"],
        "inventory": ["stock", "quantity", "inventory", "level", "item", "order", "supply", "reagent", "analyte"],
        "compliance": ["sop", "policy", "audit", "finding", "corrective", "capa", "compliance", "regulation", "guideline"],
        "samples": ["sample", "batch", "result", "test", "analyzed", "processed", "data"]
    }

    def classify(self, query: str) -> Dict[str, Any]:
        """
        Detect intent, domains, and extract IDs from the query string.
        """
        query_lower = query.lower()
        
        # 1. Extract IDs
        extracted_ids = []
        for entity, pattern in self.ID_PATTERNS.items():
            matches = re.findall(pattern, query, re.IGNORECASE)
            if matches:
                extracted_ids.extend([{"type": entity, "id": m} for m in matches])
        
        # 2. Detect Domains
        detected_domains = []
        for domain, keywords in self.DOMAIN_KEYWORDS.items():
            if any(kw in query_lower for kw in keywords):
                detected_domains.append(domain)
        
        # 3. Determine Search Mode
        # If an exact ID is found, we should prioritize Lexical/Exact search
        search_mode = "hybrid"
        if extracted_ids:
            search_mode = "lexical_first"
        
        return {
            "query": query,
            "domains": detected_domains or ["all"],
            "identifiers": extracted_ids,
            "search_mode": search_mode,
            "is_operational": any(d in ["samples", "inventory", "equipment"] for d in detected_domains)
        }

    def get_search_hints(self, classification: Dict[str, Any]) -> Dict[str, Any]:
        """
        Convert classification results into hints for the RetrievalService.
        """
        hints = {
            "collections": classification["domains"] if classification["domains"] != ["all"] else None,
            "mode": classification["search_mode"],
        }
        
        if classification["identifiers"]:
            # If multiple, just take the first for exact match hint
            hints["exact_id"] = classification["identifiers"][0]["id"]
            
        return hints
