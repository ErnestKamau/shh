from typing import List, Dict, Any, Set
import logging

logger = logging.getLogger(__name__)

class RrfFusionService:
    """
    Implements Reciprocal Rank Fusion (RRF) to merge multiple search result sets.
    """
    
    def fuse(self, 
             result_sets: List[List[Dict[str, Any]]], 
             k: int = 60, 
             limit: int = 10) -> List[Dict[str, Any]]:
        """
        Merge ranked lists and compute a unified RRF score.
        """
        scores = {} # (chunk_id) -> total_rrf_score
        chunk_map = {} # (chunk_id) -> original_data
        
        for result_set in result_sets:
            for rank, item in enumerate(result_set):
                cid = item['id']
                # RRF Formula: 1 / (k + rank + 1)
                scores[cid] = scores.get(cid, 0) + 1.0 / (k + rank + 1)
                
                # Keep the data from whichever set first provided it
                if cid not in chunk_map:
                    chunk_map[cid] = item

        # Sort by score descending
        sorted_ids = sorted(scores.keys(), key=lambda x: scores[x], reverse=True)[:limit]
        
        fused_results = []
        for cid in sorted_ids:
            res = chunk_map[cid]
            res['rrf_score'] = scores[cid]
            fused_results.append(res)
            
        return fused_results
