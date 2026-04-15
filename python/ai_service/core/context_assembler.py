from typing import List, Dict, Any, Optional

class ContextAssembler:
    """
    Refines and packages retrieved knowledge chunks for the LLM prompt.
    Handles deduplication, source grouping, and formatting.
    """
    
    def assemble(self, results: List[Dict[str, Any]]) -> str:
        """
        Convert results into a structured context string.
        """
        if not results:
            return ""
            
        # 1. Deduplicate by entity_id + content hash (preventing near-duplicates)
        seen_ids = set()
        unique_results = []
        for res in results:
            # Composite key for deduplication
            key = f"{res.get('entity_type')}:{res.get('entity_id')}"
            if key not in seen_ids:
                unique_results.append(res)
                seen_ids.add(key)
        
        # 2. Group by Domain/Collection for cleaner reading
        grouped = {}
        for res in unique_results:
            domain = res.get('collection_name', 'general').capitalize()
            if domain not in grouped:
                grouped[domain] = []
            grouped[domain].append(res)
            
        # 3. Format as structured context
        lines = ["CONTEXT INFORMATION:"]
        for domain, items in grouped.items():
            lines.append(f"\n--- Domain: {domain} ---")
            for item in items:
                content = item['content'].strip()
                lines.append(f"- {content}")
                
        return "\n".join(lines)

    def get_sources_metadata(self, results: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        """
        Extract professional source references for the UI.
        """
        sources = []
        for r in results:
            sources.append({
                "id": r.get('id'),
                "entity": r.get('entity_type'),
                "entity_id": r.get('entity_id'),
                "domain": r.get('collection_name'),
                "score": round(float(r.get('rrf_score', 0)), 4)
            })
        return sources
