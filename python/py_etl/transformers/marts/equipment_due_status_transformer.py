"""
Transformer: equipment + maintenance/calibration/verification logs → reporting.equipment_due_status & reporting.equipment_department_summary

Calculates maintenance/calibration/verification due dates, status, and risk windows
from equipment assets and their associated logs.
"""
from __future__ import annotations

import pandas as pd
from datetime import datetime, timedelta
from py_etl.transformers.base_transformer import BaseTransformer
from py_etl.transformers.type_converters import to_date, to_timestamp
from py_etl.config.config import settings as config


class EquipmentDueStatusTransformer(BaseTransformer):
    """
    Transforms equipment assets + logs into the equipment_due_status mart.
    
    This is a derived/materialized mart that:
    1. Gets all active equipment from equipment_assets
    2. Joins with equipment_logs to find last maintenance/calibration/verification dates
    3. Calculates next due dates based on frequency cycles
    4. Classifies status (overdue/warning/stable) and risk_window
    5. Produces one row per equipment with all due-date calculations
    """
    
    table_key = "equipment_due_status_mart"
    required_columns = ["id"]

    def __init__(self, equipment_df: pd.DataFrame = None, logs_df: pd.DataFrame = None):
        """
        Initialize with both equipment assets and logs data.
        These DataFrames should come from PostgreSQL reporting tables.
        """
        self.equipment_df = equipment_df or pd.DataFrame()
        self.logs_df = logs_df or pd.DataFrame()

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        """
        Main transform. In practice, we'll call this with joined data.
        For now, accept equipment assets and join client-side.
        """
        if df.empty:
            return pd.DataFrame()

        records = []
        
        for _, equipment in df.iterrows():
            equipment_id = equipment.get("id")
            
            # Find last log dates for this equipment
            last_maintenance_date = self._get_last_log_date(equipment_id, "maintainance")
            last_calibration_date = self._get_last_log_date(equipment_id, "calibration")
            last_verification_date = self._get_last_log_date(equipment_id, "verification")
            
            # Calculate due dates
            maintenance_due_date = self._calculate_due_date(
                last_maintenance_date or equipment.get("date_purchased"),
                equipment.get("maintainance_days", 365)
            )
            calibration_due_date = self._calculate_due_date(
                last_calibration_date or equipment.get("date_purchased"),
                equipment.get("calibration_days", 365)
            )
            verification_due_date = self._calculate_due_date(
                last_verification_date or equipment.get("date_purchased"),
                equipment.get("verification_days", 365)
            )
            
            # Determine status (overdue/warning/stable)
            maintenance_status = self._calculate_status(
                maintenance_due_date,
                equipment.get("maintainance_notification_in_days", 14)
            )
            calibration_status = self._calculate_status(
                calibration_due_date,
                equipment.get("calibration_notification_in_days", 14)
            )
            verification_status = self._calculate_status(
                verification_due_date,
                equipment.get("verification_notification_in_days", 14)
            )
            
            # Determine overall risk_window
            nearest_due_days = self._nearest_due_days([
                self._days_until(maintenance_due_date),
                self._days_until(calibration_due_date),
                self._days_until(verification_due_date),
            ])
            risk_window = self._calculate_risk_window(nearest_due_days)
            
            records.append({
                "source_id":                       int(equipment_id),
                "name":                            self._get(equipment, "name"),
                "assigned_department":             self._get(equipment, "assigned_department"),
                "maintenance_status":              maintenance_status,
                "calibration_status":              calibration_status,
                "verification_status":             verification_status,
                "risk_window":                     risk_window,
                "maintenance_days_until_due":      self._days_until(maintenance_due_date),
                "calibration_days_until_due":      self._days_until(calibration_due_date),
                "verification_days_until_due":     self._days_until(verification_due_date),
                "maintenance_due_date":            to_date(maintenance_due_date),
                "calibration_due_date":            to_date(calibration_due_date),
                "verification_due_date":           to_date(verification_due_date),
                "last_maintenance_date":           to_date(last_maintenance_date) if last_maintenance_date else None,
                "last_calibration_date":           to_date(last_calibration_date) if last_calibration_date else None,
                "last_verification_date":          to_date(last_verification_date) if last_verification_date else None,
                "refreshed_at":                    to_timestamp(datetime.now()),
                "synced_at":                       equipment.get("_synced_at"),
                "payload":                         equipment.get("_raw_payload"),
            })
        
        return pd.DataFrame(records)

    def _get_last_log_date(self, equipment_id: int, log_type: str) -> datetime | None:
        """Get the most recent log date for an equipment of given type."""
        if self.logs_df.empty:
            return None
        
        filtered = self.logs_df[
            (self.logs_df.get("equipment_id") == equipment_id) &
            (self.logs_df.get("event_type") == log_type)
        ]
        
        if filtered.empty:
            return None
        
        # Find most recent date
        date_col = filtered.get("event_date")
        if date_col is None:
            return None
        
        # Convert to datetime if needed and get max
        try:
            dates = pd.to_datetime(date_col, errors='coerce')
            return dates.max()
        except:
            return None

    def _calculate_due_date(self, last_date: datetime | str | None, frequency_days: int) -> datetime:
        """Calculate the next due date."""
        if last_date is None:
            return datetime.now() + timedelta(days=frequency_days)
        
        if isinstance(last_date, str):
            try:
                last_date = pd.to_datetime(last_date)
            except:
                return datetime.now() + timedelta(days=frequency_days)
        
        return last_date + timedelta(days=frequency_days)

    def _calculate_status(self, due_date: datetime, notification_days: int) -> str:
        """Calculate status: overdue, warning, or stable."""
        today = datetime.now().date()
        due_date_only = due_date.date() if isinstance(due_date, datetime) else due_date
        
        days_until = (due_date_only - today).days
        
        if days_until < 0:
            return "overdue"
        elif days_until <= notification_days:
            return "warning"
        else:
            return "stable"

    def _days_until(self, due_date: datetime) -> int | None:
        """Calculate days until due date."""
        if due_date is None:
            return None
        
        today = datetime.now().date()
        due_date_only = due_date.date() if isinstance(due_date, datetime) else due_date
        
        return (due_date_only - today).days

    def _nearest_due_days(self, days_list: list[int | None]) -> int | None:
        """Get the nearest due days from a list."""
        valid_days = [d for d in days_list if d is not None]
        return min(valid_days) if valid_days else None

    def _calculate_risk_window(self, nearest_due_days: int | None) -> str:
        """Classify risk window based on days until due."""
        if nearest_due_days is None:
            return "stable"
        
        risk_days_1 = getattr(config, 'AI_REPORTING_EQUIPMENT_RISK_DAYS_1', 7)
        risk_days_2 = getattr(config, 'AI_REPORTING_EQUIPMENT_RISK_DAYS_2', 14)
        risk_days_3 = getattr(config, 'AI_REPORTING_EQUIPMENT_RISK_DAYS_3', 30)
        
        if nearest_due_days < 0:
            return "overdue"
        elif nearest_due_days <= risk_days_1:
            return "7_days"
        elif nearest_due_days <= risk_days_2:
            return "14_days"
        elif nearest_due_days <= risk_days_3:
            return "30_days"
        else:
            return "stable"

    def _get(self, row: pd.Series, col: str, default=None):
        """Safe column access."""
        try:
            val = row.get(col, default)
            return val if pd.notna(val) else default
        except:
            return default


class EquipmentDepartmentSummaryTransformer(BaseTransformer):
    """
    Transforms equipment_due_status rows into department-level summary.
    
    Aggregates equipment counts by:
    - total_assets
    - maintenance_overdue, maintenance_warning
    - calibration_overdue, calibration_warning
    - verification_overdue, verification_warning
    - due_within_30_days
    """
    
    table_key = "equipment_department_summary_mart"
    required_columns = ["assigned_department"]

    def transform(self, df: pd.DataFrame) -> pd.DataFrame:
        """
        Aggregate equipment_due_status data by department.
        Input df should be the output from EquipmentDueStatusTransformer.
        """
        if df.empty:
            return pd.DataFrame()
        
        records = []
        
        # Group by department
        for department_id, group in df.groupby("assigned_department"):
            if pd.isna(department_id):
                continue
            
            maintenance_overdue = len(group[group["maintenance_status"] == "overdue"])
            maintenance_warning = len(group[group["maintenance_status"] == "warning"])
            calibration_overdue = len(group[group["calibration_status"] == "overdue"])
            calibration_warning = len(group[group["calibration_status"] == "warning"])
            verification_overdue = len(group[group["verification_status"] == "overdue"])
            verification_warning = len(group[group["verification_status"] == "warning"])
            
            due_within_30 = len(group[group["risk_window"].isin(["overdue", "7_days", "14_days", "30_days"])])
            
            records.append({
                "assigned_department":     int(department_id),
                "total_assets":            len(group),
                "maintenance_overdue":     maintenance_overdue,
                "maintenance_warning":     maintenance_warning,
                "calibration_overdue":     calibration_overdue,
                "calibration_warning":     calibration_warning,
                "verification_overdue":    verification_overdue,
                "verification_warning":    verification_warning,
                "due_within_30_days":      due_within_30,
                "active_count":            len(group[group.get("source_id").notna()]),
                "disposed_count":          0,  # Could derive from payload if needed
                "refreshed_at":            to_timestamp(datetime.now()),
                "synced_at":               group.iloc[0].get("synced_at"),
                "payload":                 None,
            })
        
        return pd.DataFrame(records)
