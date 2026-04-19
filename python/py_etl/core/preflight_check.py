"""
Preflight checks for ETL pipeline execution.

Validates database connectivity, schema compatibility, and required columns
before any extraction occurs. Failures are BLOCKING - ETL does not proceed.
"""

import logging
from typing import Dict, List, Set, Tuple
from loguru import logger

logger.enable("python.py_etl.core.preflight_check")


class SchemaValidationError(Exception):
    """Raised when schema validation fails (blocking)."""
    pass


class ConnectivityError(Exception):
    """Raised when database connectivity fails (blocking)."""
    pass


class PreflightCheck:
    """
    Pre-flight validation for ETL pipeline.
    
    Validates:
    1. MySQL source database connectivity
    2. PostgreSQL target database connectivity
    3. Source table structure (required columns exist)
    4. Target table structure (columns match expectations)
    5. Column type compatibility
    
    All checks must pass before ETL extraction begins.
    If any check fails, raises exception and exits immediately.
    """
    
    def __init__(self, db_manager, schema_config: Dict):
        """
        Initialize preflight checker.
        
        Args:
            db_manager: Database manager instance (handles MySQL/PostgreSQL)
            schema_config: Table config dict with source/target column specs
        """
        self.db_manager = db_manager
        self.schema_config = schema_config
        self.errors = []
        
    def run(self) -> bool:
        """
        Execute all preflight checks.
        
        Returns:
            True if all checks pass
            
        Raises:
            ConnectivityError: If database connectivity fails
            SchemaValidationError: If schema validation fails
        """
        logger.info("Starting preflight checks...")
        
        try:
            # 1. Connectivity check
            self.validate_connectivity()
            logger.info("✓ Database connectivity checks passed")
            
            # 2. Schema validation for all configured tables
            for table_key in self.schema_config.keys():
                self.validate_source_schema(table_key)
                logger.info(f"✓ Source schema validation passed: {table_key}")
                
                self.validate_target_schema(table_key)
                logger.info(f"✓ Target schema validation passed: {table_key}")
            
            logger.info("✅ All preflight checks PASSED")
            return True
            
        except (SchemaValidationError, ConnectivityError) as e:
            logger.error(f"❌ Preflight check FAILED: {e}")
            raise
    
    def validate_connectivity(self) -> None:
        """
        Test connectivity to both MySQL and PostgreSQL.
        
        Raises:
            ConnectivityError: If either database is unreachable
        """
        try:
            logger.debug("Testing MySQL connectivity...")
            self.db_manager.test_mysql_connection()
        except Exception as e:
            raise ConnectivityError(f"MySQL connection failed: {e}")
        
        try:
            logger.debug("Testing PostgreSQL connectivity...")
            self.db_manager.test_postgres_connection()
        except Exception as e:
            raise ConnectivityError(f"PostgreSQL connection failed: {e}")
    
    def validate_source_schema(self, table_key: str) -> None:
        """
        Validate source MySQL table has all required columns.
        
        Args:
            table_key: Table configuration key
            
        Raises:
            SchemaValidationError: If required columns are missing
        """
        if table_key not in self.schema_config:
            raise SchemaValidationError(f"Table '{table_key}' not in schema config")
        
        config = self.schema_config[table_key]
        source_table = config.get('source_table')
        
        if not source_table:
            raise SchemaValidationError(f"No source_table defined for '{table_key}'")
        
        try:
            # Get actual MySQL schema
            mysql_schema = self.db_manager.get_mysql_schema(source_table)
            mysql_columns = set(mysql_schema.keys())
        except Exception as e:
            raise SchemaValidationError(f"Cannot read source table '{source_table}': {e}")
        
        # Check required columns
        source_columns = set(config.get('source_columns', []))
        missing = source_columns - mysql_columns
        
        if missing:
            raise SchemaValidationError(
                f"Source table '{source_table}' missing required columns: {missing}"
            )
        
        logger.debug(f"Source schema OK: {source_table} has all {len(source_columns)} required columns")
    
    def validate_target_schema(self, table_key: str) -> None:
        """
        Validate target PostgreSQL table structure matches expectations.
        
        Args:
            table_key: Table configuration key
            
        Raises:
            SchemaValidationError: If target has missing critical columns
        """
        if table_key not in self.schema_config:
            raise SchemaValidationError(f"Table '{table_key}' not in schema config")
        
        config = self.schema_config[table_key]
        target_table = config.get('target_table')
        
        if not target_table:
            raise SchemaValidationError(f"No target_table defined for '{table_key}'")
        
        try:
            # Get actual PostgreSQL schema
            target_schema = config.get('schema', 'reporting')
            pg_schema = self.db_manager.get_postgres_schema(target_table, schema=target_schema)
            pg_columns = set(pg_schema.keys())
        except Exception as e:
            raise SchemaValidationError(f"Cannot read target table '{target_table}': {e}")
        
        # Check required columns (critical - must exist)
        target_columns = set(config.get('target_columns', []))
        missing = target_columns - pg_columns
        
        if missing:
            raise SchemaValidationError(
                f"Target table '{target_table}' missing required columns: {missing}"
            )
        
        # Check type compatibility (warnings only)
        expected_types = config.get('column_types', {})
        for col, expected_type in expected_types.items():
            if col in pg_schema:
                actual_type = pg_schema[col].get('type', 'unknown')
                if not self._types_compatible(actual_type, expected_type):
                    logger.warning(
                        f"Type mismatch in target '{target_table}' column '{col}': "
                        f"expected {expected_type}, got {actual_type}"
                    )
        
        logger.debug(f"Target schema OK: {target_table} has all {len(target_columns)} required columns")
    
    @staticmethod
    def _types_compatible(actual: str, expected: str) -> bool:
        """
        Check if actual column type is compatible with expected type.
        
        Args:
            actual: Actual PostgreSQL column type
            expected: Expected column type from config
            
        Returns:
            True if types are compatible
        """
        # Mapping of compatible types
        compatible_map = {
            'bigint': ['integer', 'bigint', 'serial', 'bigserial'],
            'integer': ['integer', 'serial'],
            'text': ['text', 'varchar', 'character varying'],
            'varchar': ['text', 'varchar', 'character varying'],
            'timestamp': ['timestamp', 'timestamp without time zone', 'timestamp with time zone'],
            'boolean': ['boolean'],
            'json': ['json', 'jsonb'],
            'jsonb': ['json', 'jsonb'],
        }
        
        actual_normalized = actual.lower().strip()
        expected_normalized = expected.lower().strip()
        
        if actual_normalized == expected_normalized:
            return True
        
        for key, compatible_list in compatible_map.items():
            if expected_normalized.startswith(key):
                return any(c in actual_normalized for c in compatible_list)
        
        return False
