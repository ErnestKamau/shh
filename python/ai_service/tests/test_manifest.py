"""
Test Suite: SQL Manifest Integrity

Ensures the live_data_manifest.json is well-formed and safe:
  1. Valid JSON
  2. All templates have required fields
  3. All SQL statements are SELECT-only
  4. All templates have TTL set
  5. No duplicate intent names across domains
  6. All intents referenced by ManifestIntentRouter exist in manifest
"""

import json
import re
import pytest


class TestManifestStructure:
    """Validate the structure and safety of the SQL manifest."""

    def _load_manifest(self, manifest_path):
        with open(manifest_path, "r") as f:
            return json.load(f)

    def test_manifest_is_valid_json(self, manifest_path):
        """Manifest should parse without errors."""
        with open(manifest_path, "r") as f:
            data = json.load(f)
        assert isinstance(data, dict)
        assert len(data) > 0

    def test_all_templates_have_required_fields(self, manifest_path):
        """Every template must have sql, description, output_format."""
        manifest = self._load_manifest(manifest_path)
        required = {"sql", "description", "output_format"}

        for domain, templates in manifest.items():
            for intent_name, template in templates.items():
                missing = required - set(template.keys())
                assert not missing, (
                    f"Intent '{intent_name}' in domain '{domain}' "
                    f"is missing required fields: {missing}"
                )

    def test_all_sql_is_select_only(self, manifest_path):
        """No INSERT, UPDATE, DELETE, DROP, TRUNCATE allowed."""
        manifest = self._load_manifest(manifest_path)
        forbidden = re.compile(
            r"\b(INSERT|UPDATE|DELETE|DROP|TRUNCATE|ALTER|CREATE)\b",
            re.IGNORECASE,
        )

        for domain, templates in manifest.items():
            for intent_name, template in templates.items():
                sql = template["sql"]
                match = forbidden.search(sql)
                assert match is None, (
                    f"Intent '{intent_name}' contains forbidden SQL keyword: "
                    f"'{match.group()}' in: {sql[:80]}..."
                )

    def test_all_templates_have_ttl(self, manifest_path):
        """Every template should have a ttl_seconds value."""
        manifest = self._load_manifest(manifest_path)

        for domain, templates in manifest.items():
            for intent_name, template in templates.items():
                assert "ttl_seconds" in template, (
                    f"Intent '{intent_name}' in domain '{domain}' "
                    f"is missing ttl_seconds"
                )
                assert isinstance(template["ttl_seconds"], (int, float)), (
                    f"Intent '{intent_name}' ttl_seconds is not numeric"
                )
                assert template["ttl_seconds"] > 0, (
                    f"Intent '{intent_name}' ttl_seconds must be positive"
                )

    def test_no_duplicate_intents(self, manifest_path):
        """Intent names must be unique across all domains."""
        manifest = self._load_manifest(manifest_path)
        seen = {}

        for domain, templates in manifest.items():
            for intent_name in templates.keys():
                assert intent_name not in seen, (
                    f"Duplicate intent '{intent_name}' found in domains "
                    f"'{seen[intent_name]}' and '{domain}'"
                )
                seen[intent_name] = domain

    def test_output_format_is_valid(self, manifest_path):
        """output_format must be one of: count, percentage, table."""
        manifest = self._load_manifest(manifest_path)
        valid_formats = {"count", "percentage", "table"}

        for domain, templates in manifest.items():
            for intent_name, template in templates.items():
                fmt = template["output_format"]
                assert fmt in valid_formats, (
                    f"Intent '{intent_name}' has invalid output_format '{fmt}'. "
                    f"Must be one of: {valid_formats}"
                )

    def test_visualize_config_is_complete(self, manifest_path):
        """If visualize is present, it must have type, labels, values."""
        manifest = self._load_manifest(manifest_path)

        for domain, templates in manifest.items():
            for intent_name, template in templates.items():
                if "visualize" in template:
                    viz = template["visualize"]
                    for key in ("type", "labels", "values"):
                        assert key in viz, (
                            f"Intent '{intent_name}' visualize config "
                            f"is missing '{key}'"
                        )


class TestManifestRouterCoverage:
    """Verify that ManifestIntentRouter keywords map to real manifest intents."""

    def test_all_keyword_routes_exist_in_manifest(self, manifest_path):
        """Every intent referenced by the router must exist in the manifest."""
        manifest = self._load_manifest(manifest_path)

        # Collect all intent names from manifest
        manifest_intents = set()
        for domain, templates in manifest.items():
            manifest_intents.update(templates.keys())

        # Collect all intent names from the router
        from python.ai_service.core.manifest_intent_router import ManifestIntentRouter
        router = ManifestIntentRouter()

        router_intents = set()
        for domain, rules in router._group_a.items():
            for _, intent in rules:
                router_intents.add(intent)
        for domain, rules in router._group_b.items():
            for _, intent in rules:
                router_intents.add(intent)

        missing = router_intents - manifest_intents
        assert not missing, (
            f"ManifestIntentRouter references intents not in manifest: {missing}"
        )

    def _load_manifest(self, manifest_path):
        with open(manifest_path, "r") as f:
            return json.load(f)
