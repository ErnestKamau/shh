# Audit Module Migration Runbook

If `php artisan db:seed --class=AuditEmailTemplatesSeeder` fails with **Target class [Database\Seeders\...] does not exist**, run **`composer dump-autoload`** in the project root (see `composer.json` PSR-4 for `Database\Seeders\`).

**MySQL / MariaDB:** Legacy migrations used `timestamps(10)`, which MySQL rejects (maximum fractional precision is **6**). The repo replaces these with `timestamps(6)`. Deploy the updated migration files, then re-run the failed `--path` migration.

If `create_verification_logs` failed earlier, after fixing files run the same `migrate --path=...create_verification_logs_table.php` again. If the table exists from a bad attempt, drop it first: `DROP TABLE IF EXISTS verification_logs;` (only if you are sure it is empty / disposable).

Run these commands on server in this exact order using explicit `--path`.

> Note: This runbook intentionally excludes the package auditable `audits` table migration, as requested.

## 1) Core Audit module migrations

```bash
php artisan migrate --path=database/migrations/2025_11_26_064815_create_audit_module_tables.php --no-interaction
php artisan migrate --path=database/migrations/2025_11_27_174332_create_iso_audits_table.php --no-interaction
php artisan migrate --path=database/migrations/2025_12_01_113504_create_audit_workflow_approvers_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_12_083707_add_module_to_audit_workflow_approvers_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_08_170345_add_severity_and_likelihood_scales_tables.php --no-interaction
php artisan migrate --path=database/migrations/2026_01_08_170711_add_severity_and_likelihood_scale_ids_to_non_conformances.php --no-interaction
php artisan migrate --path=database/migrations/2020_09_30_073157_create_verification_logs_table.php --no-interaction
```

## 2) Audit module seeder(s)

```bash
php artisan db:seed --class=AuditEmailTemplatesSeeder --no-interaction
```

## 3) Verification

```bash
php artisan migrate:status --no-interaction
```

## 4) Compliance statuses hotfix (if missing table error appears)

If you hit:
`Table '...compliance_statuses' doesn't exist`

run this exact migration path:

```bash
php artisan migrate --path=database/migrations/2026_04_10_195417_create_compliance_statuses_table.php --no-interaction
```

## 5) Audit email templates hotfix (if missing table error appears)

If you hit:
`Table '...audit_email_templates' doesn't exist`

run this exact migration path:

```bash
php artisan migrate --path=database/migrations/2026_04_10_195951_create_audit_email_templates_table.php --no-interaction
```

## 6) Audit statuses workflow step hotfix (if unknown column error appears)

If you hit:
`Unknown column 'workflow_step' in 'order clause'`

run this exact migration path:

```bash
php artisan migrate --path=database/migrations/2026_04_10_200228_add_workflow_step_to_audit_statuses_table.php --no-interaction
```

## 7) Workflow actions hotfix (if missing table error appears)

If you hit:
`Table '...workflow_actions' doesn't exist`

run this exact migration path:

```bash
php artisan migrate --path=database/migrations/2026_04_10_200811_create_workflow_actions_table.php --no-interaction
```
