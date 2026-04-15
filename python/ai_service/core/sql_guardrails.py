import re
import logging

logger = logging.getLogger(__name__)

class SQLGuardrails:
    """
    Validation engine for SQL queries generated or expanded by AI.
    Enforces strict read-only access and prevents dangerous constructs.
    """
    
    # Block list for non-READ operations
    BLOCK_KEYWORDS = [
        "INSERT", "UPDATE", "DELETE", "DROP", "TRUNCATE", "ALTER", 
        "CREATE", "GRANT", "REVOKE", "REPLACE", "EXEC", "EXECUTE"
    ]
    
    @staticmethod
    def validate_query(sql: str) -> bool:
        """
        Returns True if the query passes safety checks.
        """
        sql_upper = sql.upper()
        
        # 1. Enforce SELECT only
        if not sql_upper.strip().startswith("SELECT") and not sql_upper.strip().startswith("SHOW"):
            logger.warning(f"SQL Guardrail: Query does not start with SELECT or SHOW: {sql[:50]}...")
            return False
            
        # 2. Check for blocked keywords
        for keyword in SQLGuardrails.BLOCK_KEYWORDS:
            # Match keyword as a whole word to avoid blocking things like 'DESCRIPTION'
            pattern = rf"\b{keyword}\b"
            if re.search(pattern, sql_upper):
                logger.warning(f"SQL Guardrail: Blocked keyword '{keyword}' detected.")
                return False
                
        # 3. Prevent multi-statements
        if ";" in sql.strip()[:-1]:
            logger.warning("SQL Guardrail: Multi-statement query detected.")
            return False
            
        return True

    @staticmethod
    def sanitize_parameter(value: str) -> str:
        """Simple escaping for parameters to prevent SQL injection in templates."""
        # In a real system, use parameterised queries (bound variables). 
        # This is a fallback for string replacement templates.
        return re.sub(r"['\";\-]", "", str(value))
