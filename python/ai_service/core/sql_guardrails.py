import re
import logging
import sqlglot
from sqlglot import exp

logger = logging.getLogger(__name__)

class SQLGuardrails:
    """
    Validation engine for SQL queries generated or expanded by AI.
    Enforces strict read-only access and prevents dangerous constructs
    using an Abstract Syntax Tree (AST) parser via sqlglot.
    """
    
    BLOCK_NODES = (
        exp.Insert, exp.Update, exp.Delete, exp.Drop, 
        exp.Alter, exp.TruncateTable, exp.Create, exp.Command, exp.Execute
    )
    
    @staticmethod
    def validate_query(sql: str) -> bool:
        """
        Returns True if the query parses into a safe, read-only SELECT or CTE.
        """
        if not sql or not sql.strip():
            logger.warning("SQL Guardrail: Empty query submitted.")
            return False
            
        try:
            # 1. Parse all statements using sqlglot
            parsed_list = sqlglot.parse(sql, read="postgres")
            
            # 2. Block multi-statement queries
            if len(parsed_list) > 1:
                logger.warning("SQL Guardrail: Multiple SQL statements detected.")
                return False
                
            if not parsed_list or parsed_list[0] is None:
                logger.warning("SQL Guardrail: No valid SQL statement found.")
                return False
                
            expression = parsed_list[0]
            
            # 3. Walk the AST to detect blocked nodes recursively
            for node in expression.walk():
                if isinstance(node, SQLGuardrails.BLOCK_NODES):
                    logger.warning(f"SQL Guardrail: Blocked AST node '{type(node).__name__}' detected.")
                    return False
                    
            # 4. Enforce that the base query is a read-only SELECT, Union, Subquery, or CTE
            if not isinstance(expression, (exp.Select, exp.Subquery, exp.Union, exp.CTE)):
                logger.warning(
                    f"SQL Guardrail: Base query expression type '{type(expression).__name__}' "
                    f"is invalid (must be read-only SELECT/CTE)."
                )
                return False
                
            return True
            
        except Exception as e:
            logger.error(f"SQL Guardrail: AST validation parser failed: {e}")
            return False

    @staticmethod
    def sanitize_parameter(value: str) -> str:
        """Simple escaping for parameters to prevent SQL injection in templates."""
        return re.sub(r"['\";\-]", "", str(value))
