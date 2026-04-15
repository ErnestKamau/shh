import json
from pathlib import Path


MANIFEST_PATH = Path(__file__).resolve().parents[1] / "core" / "live_data_manifest.json"


def test_sample_and_batch_in_lab_metrics_use_distinct_tables():
    manifest = json.loads(MANIFEST_PATH.read_text())
    samples = manifest["samples"]

    sample_sql = samples["sample_count_in_lab"]["sql"].lower()
    batch_sql = samples["batch_count_in_lab"]["sql"].lower()

    assert "sample_details" in sample_sql
    assert "sample_headers" in sample_sql
    assert "count(*) as n" in batch_sql
    assert "from sample_headers" in batch_sql
    assert "sample_details" not in batch_sql


def test_sample_total_metric_counts_samples_not_batches():
    manifest = json.loads(MANIFEST_PATH.read_text())
    sample_total_sql = manifest["samples"]["sample_count_total"]["sql"].lower()

    assert "sample_details" in sample_total_sql
    assert "sample_headers" in sample_total_sql
