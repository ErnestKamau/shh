# Daily Log Migrations Runbook

Run these commands from the project root (`c:\Imara_Lims\polucon`).

## Daily Log migration files

1. `database/migrations/2026_04_10_000000_add_requires_daily_log_to_equipment_table.php`
2. `database/migrations/2026_04_10_000001_add_daily_log_fields_to_equipment_table.php`
3. `database/migrations/2026_04_10_000002_add_daily_log_expected_values_to_equipment_table.php`
4. `database/migrations/2026_04_10_000003_add_daily_log_reporting_unit_to_equipment_table.php`
5. `database/migrations/2026_04_10_000004_add_daily_log_frequency_to_equipment_table.php`
6. `database/migrations/2026_04_11_000001_create_equipment_daily_log_entries_table.php`

## Run on server (production/staging)

```bash
php artisan migrate --path=database/migrations/2026_04_10_000000_add_requires_daily_log_to_equipment_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_04_10_000001_add_daily_log_fields_to_equipment_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_04_10_000002_add_daily_log_expected_values_to_equipment_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_04_10_000003_add_daily_log_reporting_unit_to_equipment_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_04_10_000004_add_daily_log_frequency_to_equipment_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_04_11_000001_create_equipment_daily_log_entries_table.php --no-interaction
```

## Run locally

```bash
php artisan migrate --path=database/migrations/2026_04_10_000000_add_requires_daily_log_to_equipment_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_04_10_000001_add_daily_log_fields_to_equipment_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_04_10_000002_add_daily_log_expected_values_to_equipment_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_04_10_000003_add_daily_log_reporting_unit_to_equipment_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_04_10_000004_add_daily_log_frequency_to_equipment_table.php --no-interaction
php artisan migrate --path=database/migrations/2026_04_11_000001_create_equipment_daily_log_entries_table.php --no-interaction
```

## Verify status

```bash
php artisan migrate:status --no-interaction
```

