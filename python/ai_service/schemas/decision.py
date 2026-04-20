"""
Decision Engine Schemas
Fully typed models for decision outputs, feedback, and training data.
"""
from typing import Dict, List, Optional, Any
from pydantic import BaseModel, Field
from datetime import datetime
from enum import Enum


class ActionPriority(str, Enum):
    """Priority levels for recommended actions."""
    HIGH = "high"
    MEDIUM = "medium"
    LOW = "low"


class DecisionOutput(BaseModel):
    """Output from the decision engine combining rules, risk, and models."""
    suggested_action: str = Field(..., description="Recommended action (e.g., 'Review sample', 'Schedule calibration')")
    action_priority: ActionPriority = Field(..., description="Priority level: high, medium, low")
    confidence_score: float = Field(..., ge=0, le=1, description="Confidence in this decision (0–1)")
    reason: str = Field(..., description="Explainable reason combining risk, rules, and model scores")
    key_factors: List[str] = Field(default_factory=list, description="Top 3–5 features influencing the decision")
    trace_id: Optional[str] = Field(None, description="Correlation ID for tracing")
    model_used: Optional[str] = Field(None, description="Name/version of model used (e.g., 'sample_decision_model_v1')")
    decision_source: Optional[str] = Field(None, description="'rule_based', 'model_based', or 'hybrid'")
    
    # Internal scoring breakdown for explainability
    risk_score: Optional[float] = Field(None, ge=0, le=1, description="Raw risk score (0–1)")
    model_score: Optional[float] = Field(None, ge=0, le=1, description="Model prediction (0–1)")
    blended_score: Optional[float] = Field(None, ge=0, le=1, description="Weighted combination of risk + model")
    
    class Config:
        use_enum_values = True


class DecisionFeedback(BaseModel):
    """Stores decision context for training loop."""
    trace_id: str = Field(..., description="Unique request ID")
    decision_id: Optional[str] = Field(None, description="Can be trace_id or separate UUID")
    entity_id: int = Field(..., description="Sample/Equipment/QC ID")
    module: str = Field(..., description="Domain: 'samples', 'equipment', 'qc', 'inventory'")
    suggested_action: str = Field(..., description="The action recommended by AI")
    
    # Feature snapshot at decision time
    features_snapshot: Dict[str, Any] = Field(default_factory=dict, description="All features used in decision")
    model_version: str = Field(..., description="Version of model used (e.g., 'v1')")
    
    # Decision output stored for traceability
    decision_output: Dict[str, Any] = Field(default_factory=dict, description="Full DecisionOutput dict")
    
    # User feedback (filled after human sees recommendation)
    user_action: Optional[str] = Field(None, description="'approved', 'modified', 'rejected'")
    outcome_result: Optional[str] = Field(None, description="'action_taken', 'sample_passed', 'flagged', 'pending'")
    user_feedback_text: Optional[str] = Field(None, description="Free-form feedback")
    
    created_at: datetime = Field(default_factory=datetime.utcnow)
    updated_at: Optional[datetime] = Field(None)
    
    class Config:
        arbitrary_types_allowed = True


class TrainingDataRow(BaseModel):
    """Single labeled row for model training."""
    trace_id: str
    features: Dict[str, float]  # Normalized numeric features
    label: int  # 0: negative outcome, 1: positive/flagged
    module: str
    weight: float = 1.0  # Sample weight (more recent = higher)
    created_at: datetime


class TrainingDataset(BaseModel):
    """Batch of training rows."""
    module: str
    rows: List[TrainingDataRow]
    total: int = 0
    date_range: tuple = ("2026-01-01", "2026-12-31")
    
    def __init__(self, **data):
        super().__init__(**data)
        self.total = len(self.rows)


class DecisionTrace(BaseModel):
    """Summary of decision for response metadata."""
    model_used: Optional[str] = None
    decision_source: str = Field("hybrid", description="'rule_based', 'model_based', 'hybrid'")
    confidence: float = Field(..., ge=0, le=1)
