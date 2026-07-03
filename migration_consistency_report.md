# Migration Consistency Report

Generated: 2026-07-03T08:52:39.076067

## Executive Summary

| Metric | Count |
|--------|------:|
| Migrations scanned | 734 |
| Files skipped (no timestamp) | 0 |
| Tables with multiple CREATE migrations | 0 |
| Views created more than once | 0 |
| Indexes created more than once | 0 |
| Types created more than once | 0 |
| Schemas created more than once | 2 |
| Extensions created more than once | 0 |
| Foreign keys created more than once | 0 |
| Tables created then dropped (lifecycle) | 9 |
| down() creates duplicating up() elsewhere | 6 |
| Dependency ordering violations | 0 |
| Unknown table references | 20 |
| Circular dependencies | 0 |

> **Policy:** This report never recommends silent deletion. All consolidation requires manual review and confirmation against production migration history.

## Duplicate CREATE TABLE Migrations

No tables are created by more than one migration.

## Duplicate CREATE VIEW Migrations

No views created by more than one migration.

## Tables Created Then Dropped (Forward Lifecycle)

### `sample_point_area`
- Created: `database/migrations/convert/2026_02_05_200433_create_sample_point_area_table.php` (2026_02_05_200433)
- Dropped: `database/migrations/2026_05_06_130000_drop_header_sample_tables_and_extend_sample_points.php` (2026_05_06_130000)
- **Note:** Not a duplicate CREATE on forward migrate if drop runs after create. Verify ordering is correct for fresh installs.

### `crm_sample_points`
- Created: `database/migrations/convert/2026_02_05_200118_create_crm_sample_points_table.php` (2026_02_05_200118)
- Dropped: `database/migrations/2026_05_06_130000_drop_header_sample_tables_and_extend_sample_points.php` (2026_05_06_130000)
- **Note:** Not a duplicate CREATE on forward migrate if drop runs after create. Verify ordering is correct for fresh installs.

### `crm_areas`
- Created: `database/migrations/convert/2026_02_05_200109_create_crm_areas_table.php` (2026_02_05_200109)
- Dropped: `database/migrations/2026_05_06_130000_drop_header_sample_tables_and_extend_sample_points.php` (2026_05_06_130000)
- **Note:** Not a duplicate CREATE on forward migrate if drop runs after create. Verify ordering is correct for fresh installs.

### `attachment_forms`
- Created: `database/migrations/2026_05_06_000000_create_attachment_forms_table.php` (2026_05_06_000000)
- Dropped: `database/migrations/2026_05_07_100000_refactor_submission_forms_type_and_attachment_pivot.php` (2026_05_07_100000)
- **Note:** Not a duplicate CREATE on forward migrate if drop runs after create. Verify ordering is correct for fresh installs.

### `attachment_form_template_type`
- Created: `database/migrations/2026_05_06_000000_create_attachment_forms_table.php` (2026_05_06_000000)
- Dropped: `database/migrations/2026_05_07_100000_refactor_submission_forms_type_and_attachment_pivot.php` (2026_05_07_100000)
- **Note:** Not a duplicate CREATE on forward migrate if drop runs after create. Verify ordering is correct for fresh installs.

### `email_sents`
- Created: `database/migrations/convert/2026_02_05_200138_create_email_sents_table.php` (2026_02_05_200138)
- Dropped: `database/migrations/2026_05_12_120000_convert_email_sents_id_to_uuid.php` (2026_05_12_120000)
- **Note:** Not a duplicate CREATE on forward migrate if drop runs after create. Verify ordering is correct for fresh installs.

### `monitoring_template_configured_field_sets`
- Created: `database/migrations/2026_05_24_160000_create_monitoring_template_configured_field_tables.php` (2026_05_24_160000)
- Dropped: `database/migrations/2026_05_24_170000_flatten_monitoring_template_configured_fields.php` (2026_05_24_170000)
- **Note:** Not a duplicate CREATE on forward migrate if drop runs after create. Verify ordering is correct for fresh installs.

### `test_request_forms`
- Created: `database/migrations/2026_06_08_182833_create_test_request_forms_table.php` (2026_06_08_182833)
- Dropped: `database/migrations/2026_06_26_160000_drop_deprecated_test_request_form_tables.php` (2026_06_26_160000)
- **Note:** Not a duplicate CREATE on forward migrate if drop runs after create. Verify ordering is correct for fresh installs.

### `test_request_form_instances`
- Created: `database/migrations/2026_06_08_182854_create_test_request_form_instances_table.php` (2026_06_08_182854)
- Dropped: `database/migrations/2026_06_26_160000_drop_deprecated_test_request_form_tables.php` (2026_06_26_160000)
- **Note:** Not a duplicate CREATE on forward migrate if drop runs after create. Verify ordering is correct for fresh installs.

## down() CREATE Duplicating up() Elsewhere (Rollback Path)

- `attachment_form_template_type`: up in `database/migrations/2026_05_06_000000_create_attachment_forms_table.php`; down() create in `database/migrations/2026_05_07_100000_refactor_submission_forms_type_and_attachment_pivot.php`
- `attachment_forms`: up in `database/migrations/2026_05_06_000000_create_attachment_forms_table.php`; down() create in `database/migrations/2026_05_07_100000_refactor_submission_forms_type_and_attachment_pivot.php`
- `crm_areas`: up in `database/migrations/convert/2026_02_05_200109_create_crm_areas_table.php`; down() create in `database/migrations/2026_05_06_130000_drop_header_sample_tables_and_extend_sample_points.php`
- `crm_sample_points`: up in `database/migrations/convert/2026_02_05_200118_create_crm_sample_points_table.php`; down() create in `database/migrations/2026_05_06_130000_drop_header_sample_tables_and_extend_sample_points.php`
- `monitoring_template_configured_field_sets`: up in `database/migrations/2026_05_24_160000_create_monitoring_template_configured_field_tables.php`; down() create in `database/migrations/2026_05_24_170000_flatten_monitoring_template_configured_fields.php`
- `sample_point_area`: up in `database/migrations/convert/2026_02_05_200433_create_sample_point_area_table.php`; down() create in `database/migrations/2026_05_06_130000_drop_header_sample_tables_and_extend_sample_points.php`

These do not duplicate on `php artisan migrate` forward, but rollback paths may recreate schemas already defined elsewhere.

## Duplicate CREATE INDEX Migrations

No indexes created by more than one migration.

## Duplicate CREATE TYPE Statements

No duplicate CREATE TYPE statements detected.

## Duplicate CREATE SCHEMA Statements

### Schema: `ai`
- `database/migrations/2024_12_08_000000_create_ai_schema.php` (2024_12_08_000000) (DROP SCHEMA first)
- `database/migrations/convert/2026_02_05_200004_create_ai_feature_snapshots_table.php` (2026_02_05_200004) (IF NOT EXISTS)
- `database/migrations/convert/2026_02_05_200006_create_ai_model_registry_table.php` (2026_02_05_200006) (IF NOT EXISTS)
- `database/migrations/2026_05_08_120000_create_missing_ai_schema_runtime_tables.php` (2026_05_08_120000) (IF NOT EXISTS)
- **Recommendation:** `IF NOT EXISTS` makes re-runs safe; duplicate statements are redundant but usually harmless. Consider consolidating to the earliest migration.

### Schema: `reporting`
- `database/migrations/2024_12_08_000000_create_ai_schema.php` (2024_12_08_000000) (DROP SCHEMA first)
- `database/migrations/2026_05_08_120000_create_missing_ai_schema_runtime_tables.php` (2026_05_08_120000) (IF NOT EXISTS)
- **Recommendation:** `IF NOT EXISTS` makes re-runs safe; duplicate statements are redundant but usually harmless. Consider consolidating to the earliest migration.

## Duplicate CREATE EXTENSION Statements

No duplicate CREATE EXTENSION statements detected.

## Foreign Keys Created More Than Once

No foreign keys created by more than one migration.

## Dependency Ordering Violations

No timestamp ordering violations detected (by filename order).

## Unknown Table References

- `2026_05_06_130000_drop_header_sample_tables_and_extend_sample_points.php` references `sample_pointsn` (no CREATE migration found)
- `2026_05_07_100000_refactor_submission_forms_type_and_attachment_pivot.php` references `attachment_form_customers` (no CREATE migration found)
- `2026_05_07_100000_refactor_submission_forms_type_and_attachment_pivot.php` references `attachment_form_sample_types` (no CREATE migration found)
- `2026_05_08_215900_add_context_to_ai_conversations.php` references `public.ai_conversations` (no CREATE migration found)
- `2026_05_20_000000_create_public_qc_views.php` references `failure_counts` (no CREATE migration found)
- `2026_05_20_000000_create_public_qc_views.php` references `public.analytes` (no CREATE migration found)
- `2026_05_20_000000_create_public_qc_views.php` references `public.qc_processed_result` (no CREATE migration found)
- `2026_05_20_000000_create_public_qc_views.php` references `public.qc_results` (no CREATE migration found)
- `2026_05_20_000100_create_public_equipment_views.php` references `public.equipment` (no CREATE migration found)
- `2026_05_20_000100_create_public_equipment_views.php` references `public.maintainance_calibration_logs` (no CREATE migration found)
- `2026_05_20_000100_create_public_equipment_views.php` references `v_equipment_last_logs` (no CREATE migration found)
- `2026_05_20_000100_create_public_equipment_views.php` references `v_equipment_reliability` (no CREATE migration found)
- `2026_05_21_140100_create_manifest_intent_patterns_table.php` references `manifest_intents` (no CREATE migration found)
- `2026_05_24_170000_flatten_monitoring_template_configured_fields.php` references `monitoring_template_configured_fields as f` (no CREATE migration found)
- `2026_07_02_133902_create_qc_results_view.php` references `public.captured_results` (no CREATE migration found)
- `2026_07_02_133902_create_qc_results_view.php` references `public.qc_results` (no CREATE migration found)
- `2026_07_02_133902_create_qc_results_view.php` references `public.sample_headers` (no CREATE migration found)
- `2026_07_02_133902_create_qc_results_view.php` references `public.sample_types` (no CREATE migration found)
- `2026_07_02_133902_create_qc_results_view.php` references `public.users` (no CREATE migration found)
- `2026_07_02_133902_create_qc_results_view.php` references `sh.sample_type_id` (no CREATE migration found)

## Methodology

- Parsed `up()` and `down()` bodies from all `database/migrations/**/*.php` files.
- Duplicate CREATE TABLE analysis uses **`up()` only** (forward migrate path).
- Detected `Schema::create`, `DB::statement` SQL, blueprint columns/indexes/FKs.
- Table schema comparison uses column names, normalized types, and nullable/unique modifiers.
- Dependency analysis reuses `scripts/migration_dependency_analyzer.py`.
- Comparisons are static; runtime guards (`Schema::hasTable`, `IF NOT EXISTS`) are not executed.
