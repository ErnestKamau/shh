from pydantic import BaseModel, Field, field_validator
from typing import Optional, List, Dict, Any, Union
import re
import unicodedata

# Phase 2: allowed collection names whitelist
_ALLOWED_COLLECTIONS: frozenset[str] = frozenset({
    "sops", "corrective_actions", "anomalies", "findings",
    "incidents", "audits", "sample_enrichments", "manual_docs",
    "inventory_reports", "inventory_procedures", "inventory_guidelines",
    "equipment_maintenance_docs", "equipment_manuals", "equipment_procedures",
})

# Phase 2: entity_type values are PHP class-name strings (word chars + backslash).
_ENTITY_TYPE_RE = re.compile(r"^[A-Za-z0-9_\\]+$")
# Phase 4: zero-width / directional-control characters to strip.
_ZERO_WIDTH_RE = re.compile(r"[\u200b-\u200f\ufeff]")

def _normalize_text(text: str) -> str:
    """Phase 4: NFKC-normalise and strip zero-width characters."""
    text = unicodedata.normalize("NFKC", text)
    return _ZERO_WIDTH_RE.sub("", text)

class EmbeddingRequest(BaseModel):
    text: str = Field(min_length=1, max_length=20000)

    @field_validator("text")
    @classmethod
    def normalize_text(cls, v: str) -> str:
        return _normalize_text(v)

class SearchRequest(BaseModel):
    query: str = Field(min_length=1, max_length=1000)
    limit: int = Field(default=5, ge=1, le=20)
    collections: List[str] = Field(default_factory=list)
    entity_types: List[str] = Field(default_factory=list)
    metadata: Dict[str, Union[str, int, float, bool]] = Field(default_factory=dict)

    @field_validator("query")
    @classmethod
    def normalize_query(cls, v: str) -> str:
        return _normalize_text(v)

    @field_validator("collections")
    @classmethod
    def validate_collections(cls, v: List[str]) -> List[str]:
        for item in v:
            if item not in _ALLOWED_COLLECTIONS:
                raise ValueError(f"Unknown collection: {item!r}")
        return v

    @field_validator("entity_types")
    @classmethod
    def validate_entity_types(cls, v: List[str]) -> List[str]:
        for item in v:
            if len(item) > 120 or not _ENTITY_TYPE_RE.match(item):
                raise ValueError(f"Invalid entity_type format: {item!r}")
        return v
