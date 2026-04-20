import json
import logging
from typing import Dict, Any, List, Optional
import pandas as pd

logger = logging.getLogger(__name__)

class VisualizationService:
    """
    Transforms raw dataframes into structured Chart.js blocks based on manifest configurations.
    """

    def generate_chart_block(self, df: pd.DataFrame, config: Dict[str, Any]) -> str:
        """
        Generates a Markdown code block with Chart.js JSON metadata.
        """
        if df.empty:
            return ""

        try:
            chart_type = config.get("type", "bar")
            labels_col = config.get("labels")
            values_col = config.get("values")
            title = config.get("title", "")

            if not labels_col or not values_col:
                logger.warning(f"Visualization config missing required columns: {config}")
                return ""

            if labels_col not in df.columns or values_col not in df.columns:
                logger.warning(f"DataFrame missing required columns '{labels_col}' or '{values_col}'")
                return ""

            # Extract labels and data
            labels = df[labels_col].tolist()
            data = df[values_col].tolist()

            # Construct Chart.js compatible JSON
            chart_json = {
                "type": chart_type,
                "title": title,
                "labels": labels,
                "datasets": [
                    {
                        "label": title or values_col,
                        "data": data
                    }
                ]
            }

            return f"\n\n```chart\n{json.dumps(chart_json, indent=2)}\n```\n"

        except Exception as e:
            logger.error(f"Failed to generate chart block: {e}")
            return ""

visualization_service = VisualizationService()
