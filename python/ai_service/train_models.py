from __future__ import annotations

import argparse
import json
from dataclasses import asdict

from loguru import logger

from ai_service.training import ModelTrainer


VALID_TARGETS = {"all", "tat", "equipment"}


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(
        prog="polucon-ai-train",
        description="Train predictive AI models and register active artifacts.",
    )
    parser.add_argument(
        "--target",
        default="all",
        choices=sorted(VALID_TARGETS),
        help="Model target to train: all | tat | equipment",
    )
    parser.add_argument(
        "--version",
        default=None,
        help="Optional model version label. Defaults to UTC timestamp.",
    )
    parser.add_argument(
        "--no-activate",
        action="store_true",
        help="Register model without activating it.",
    )
    parser.add_argument(
        "--json",
        action="store_true",
        help="Emit machine-readable JSON summary.",
    )
    return parser


def _emit_output(payload: dict, as_json: bool) -> None:
    if as_json:
        print(json.dumps(payload, indent=2, default=str))
        return

    for key, value in payload.items():
        logger.info(f"{key}: {value}")


def main() -> int:
    parser = build_parser()
    args = parser.parse_args()

    trainer = ModelTrainer()
    activate = not args.no_activate

    try:
        if args.target == "all":
            results = trainer.train_all(version=args.version, activate=activate)
            payload = {
                model_type: asdict(result)
                for model_type, result in results.items()
            }
        elif args.target == "tat":
            result = trainer.train_tat_model(version=args.version, activate=activate)
            payload = {"tat_prediction": asdict(result)}
        else:
            result = trainer.train_equipment_model(version=args.version, activate=activate)
            payload = {"equipment_maintenance": asdict(result)}

        _emit_output(payload, args.json)
        return 0

    except Exception as exc:
        logger.exception(f"Model training failed: {exc}")
        if args.json:
            print(json.dumps({"status": "error", "message": str(exc)}))
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
