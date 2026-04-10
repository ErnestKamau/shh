# Risk Management Migration Runbook

Run these commands on server in this exact order (all are specific `--path` commands).

## 1) Required dependency migrations

```bash
php artisan migrate --path=database/migrations/2025_11_26_064815_create_audit_module_tables.php --no-interaction
php artisan migrate --path=database/migrations/2025_11_27_174332_create_iso_audits_table.php --no-interaction
php artisan migrate --path=database/migrations/2025_12_01_113504_create_audit_workflow_approvers_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_12_083707_add_module_to_audit_workflow_approvers_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_08_170345_add_severity_and_likelihood_scales_tables.php --no-interaction
```

## 2) Risk module migrations

```bash
php artisan migrate --path=database/migrations/2026_01_09_142158_add_iso_compliance_fields_to_risks_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_09_162500_create_risk_configuration_tables.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_09_162413_create_risk_management_module_tables.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_10_123145_create_risk_assessments_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_10_123208_create_risk_evaluations_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_10_123250_create_risk_treatment_implementations_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_10_123319_add_fields_to_risk_treatment_plans_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_10_123339_add_fields_to_risk_reviews_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_10_123407_add_fields_to_risks_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_10_123428_create_risk_configuration_options_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_10_123448_seed_risk_configuration_options.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_10_150820_add_reassess_risk_to_risk_reviews_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_15_000000_seed_risk_workflow_step_2_statuses.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_15_000001_fix_risk_statuses_workflow_steps.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_15_000002_fix_risk_statuses_workflow_mapping.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_15_000003_sync_risk_workflow_steps.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_16_074157_create_risk_business_processes_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_16_074210_create_risk_process_links_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_20_000001_update_risks_source_fields.php --no-interaction
```

## 3) Risk seeders

```bash
php artisan db:seed --class=RiskCategoriesTableSeeder --no-interaction
php artisan db:seed --class=RiskSourcesTableSeeder --no-interaction
php artisan db:seed --class=RiskStatusesTableSeeder --no-interaction
php artisan db:seed --class=RiskBusinessProcessSeeder --no-interaction
php artisan db:seed --class=RiskWorkflowApproverSeeder --no-interaction
```

## 4) Quick verification

```bash
php artisan migrate:status --no-interaction
```
