"""
Structured logging configuration for ETL pipeline.

Configures loguru to output JSON logs with correlation IDs (run_id, table_name, chunk_id).
Logs are written to storage/logs/etl/ with automatic rotation and retention.
"""

import sys
import os
from datetime import datetime
from pathlib import Path
from loguru import logger

# Remove default handler
logger.remove()


def setup_etl_logging(run_id: str, log_dir: str = None) -> logger:
    """
    Configure structured JSON logging for ETL with correlation ID.
    
    Creates two handlers:
    1. JSON file output (machine-parseable) → storage/logs/etl/etl_<run_id>_<timestamp>.json.log
    2. Console output (human-readable) → stdout
    
    Args:
        run_id: Unique identifier for this ETL run (UUID)
        log_dir: Directory to write logs (default: storage/logs/etl)
        
    Returns:
        Configured logger instance with run_id binding
        
    Example:
        >>> etl_logger = setup_etl_logging("550e8400-e29b-41d4-a716-446655440000")
        >>> etl_logger = etl_logger.bind(table_name="sample_headers", chunk_id=1)
        >>> etl_logger.info("Processing chunk", rows_processed=100)
    """
    
    if log_dir is None:
        log_dir = "storage/logs/etl"
    
    # Create log directory if it doesn't exist
    Path(log_dir).mkdir(parents=True, exist_ok=True)
    
    # Construct log file path with run_id and timestamp
    log_file = os.path.join(
        log_dir,
        f"etl_{run_id}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json.log"
    )
    
    # Handler 1: JSON file output (machine-parseable)
    logger.add(
        log_file,
        format="{message}",
        serialize=True,  # JSON format
        rotation="100 MB",  # Rotate at 100 MB
        retention="14 days",  # Keep 14 days of logs
        enqueue=False,  # Synchronous writes (ensures flushed on exit)
        level="DEBUG"
    )
    
    # Handler 2: Console output (human-readable)
    logger.add(
        sys.stdout,
        format=(
            "<level>{time:YYYY-MM-DD HH:mm:ss}</level> | "
            "<level>{level: <8}</level> | "
            "<cyan>{name}</cyan>:<cyan>{function}</cyan> | "
            "<level>{message}</level>"
        ),
        level="INFO",
        colorize=True
    )
    
    # Bind run_id to all logs from this instance
    logger_with_run_id = logger.bind(run_id=run_id)
    
    logger_with_run_id.info(f"ETL logging initialized for run {run_id}")
    logger_with_run_id.debug(f"Log file: {log_file}")
    
    return logger_with_run_id


def get_contextual_logger(run_id: str, table_name: str = None, chunk_id: int = None):
    """
    Get a logger with specific context bindings.
    
    Binds correlation IDs (run_id, table_name, chunk_id) to all log messages.
    Useful for tracing activity across the pipeline.
    
    Args:
        run_id: ETL run UUID
        table_name: Source table being processed
        chunk_id: Chunk/batch ID (for chunked processing)
        
    Returns:
        Logger instance with context bindings
        
    Example:
        >>> logger = get_contextual_logger("abc123", "sample_headers", 5)
        >>> logger.info("Row validation failed", row_id=42)
        # Output: ... run_id=abc123 table=sample_headers chunk=5 row_id=42 ...
    """
    
    context = {"run_id": run_id}
    
    if table_name:
        context["table_name"] = table_name
    
    if chunk_id is not None:
        context["chunk_id"] = chunk_id
    
    return logger.bind(**context)


def setup_etl_logging_with_context(run_id: str, table_name: str = None, chunk_id: int = None):
    """
    Setup logging with full context binding.
    
    Convenience function combining setup_etl_logging + context binding.
    
    Args:
        run_id: ETL run UUID
        table_name: Table name for this context
        chunk_id: Chunk ID for this context
        
    Returns:
        Contextual logger instance
    """
    
    base_logger = setup_etl_logging(run_id)
    return get_contextual_logger(run_id, table_name, chunk_id)
