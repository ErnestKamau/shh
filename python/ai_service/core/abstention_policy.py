from typing import List, Dict, Any, Optional
import logging

logger = logging.getLogger(__name__)

class AbstentionPolicy:
    """
    Decides whether a set of retrieved results is "safe" enough to answer the user's query.
    Implements the Case A/B/C logic from the Stage 2 design rules.
    """
    
    def evaluate(self, 
                 query: str, 
                 results: List[Dict[str, Any]], 
                 logic_type: str = "domain",
                 exact_id: Optional[str] = None) -> bool:
        """
        Returns True if the results pass the policy, False if the system should abstain.
        """
        if not results:
            logger.info(f"AbstentionPolicy: No results found for query. Abstaining.")
            return False
            
        if logic_type == "exact":
            return self._evaluate_exact(query, results, exact_id)
        elif logic_type == "domain":
            return self._evaluate_domain(query, results)
        else: # exploratory
            return self._evaluate_exploratory(query, results)

    def _evaluate_exact(self, query: str, results: List[Dict[str, Any]], exact_id: str) -> bool:
        """
        Case A: Exact identifier query.
        Requirement: Must find a strong lexical hit OR very high semantic score.
        """
        if not exact_id:
            return True
            
        # Check top result for very high similarity or exact ID in metadata/content
        top_result = results[0]
        content = top_result['content'].lower()
        entity_id = str(top_result.get('entity_id', '')).lower()
        target_id = exact_id.lower()
        
        # Must exactly contain the ID string in content or match entity_id
        id_found = (target_id in content) or (target_id == entity_id)
        
        # Higher score requirement for 'Exact' confidence
        # rrf_score of 0.03+ usually means it was in the top 1-3 of a pool.
        is_strong = top_result.get('rrf_score', 0) >= 0.03
        
        if not id_found and not is_strong:
            logger.warning(f"AbstentionPolicy: Exact ID {exact_id} check failed (found={id_found}, score={top_result.get('rrf_score'):.4f}). Abstaining.")
            return False
            
        return True

    def _evaluate_domain(self, query: str, results: List[Dict[str, Any]]) -> bool:
        """
        Case B: Domain-scoped question.
        Requirement: Top score must be above semantic floor (0.4) OR have strong lexical rank.
        """
        top_result = results[0]
        semantic_score = top_result.get('score', 0) # 1 - distance
        lexical_score = top_result.get('ts_rank', 0) or 0
        rrf_score = top_result.get('rrf_score', 0)
        
        # Absolute Floor for Domain Questions
        if semantic_score < 0.2 and lexical_score < 0.1:
            logger.info(f"AbstentionPolicy: Absolute score floor (0.2/0.1) not met. score={semantic_score:.2f}, ts_rank={lexical_score:.2f}")
            return False

        # Domain consistency: if results are spread across too many domains, we might be hallucinating relevance
        domains = [r.get('collection_name') for r in results[:3]]
        is_consistent = len(set(domains)) <= 2 
        
        # Permissive threshold for exploratory/domain queries
        # 0.014 allows items that were ~70th in the pool, whereas 0.016 was ~60th.
        if rrf_score < 0.014: 
            logger.info(f"AbstentionPolicy: RRF score {rrf_score:.4f} below threshold 0.014. Abstaining.")
            return False
            
        return True

    def _evaluate_exploratory(self, query: str, results: List[Dict[str, Any]]) -> bool:
        """
        Case C: Broad explanatory question.
        Requirement: Require multiple chunks or decent high-level semantic match.
        """
        if len(results) < 2:
            return False
            
        top_semantic = results[0].get('score', 0)
        if top_semantic < 0.35:
            logger.info(f"AbstentionPolicy: Explanatory semantic match too weak ({top_semantic:.2f})")
            return False

        return True
