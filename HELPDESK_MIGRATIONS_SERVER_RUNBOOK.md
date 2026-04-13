# Help Desk Migration Runbook

## 0) Deploy code first (required on MySQL)

If you see **`Too big precision 10 specified for 'created_at'. Maximum is 6`**, the server is running **old migration files** that use `timestamps(10)`. MySQL only allows **0–6** for fractional seconds.

**Fix:** Pull/deploy the latest `fivet` branch (migrations use `timestamps(6)`), **or** patch on the server (emergency):

```bash
cd /var/www/html/fivet/polucon
find database/migrations -name '*.php' -exec sed -i 's/timestamps(10)/timestamps(6)/g' {} +
```

Then re-run the failed `migrate --path=...` commands.

If a failed `CREATE TABLE` left no table, you can migrate again as usual. If a broken table exists (rare), drop it only when safe: `DROP TABLE IF EXISTS chat_message;` / `DROP TABLE IF EXISTS conversation;`

Run these commands on server in this exact order using explicit `--path`.

## 1) Base dependencies (if not already migrated)

```bash
php artisan migrate --path=database/migrations/2020_09_30_073157_create_conversation_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2020_09_30_073157_create_chat_message_table.php --force --no-interaction
```

## 2) Help Desk module migrations

```bash
php artisan migrate --path=database/migrations/2025_09_02_140725_add_ticket_no_in_complaints_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2025_12_21_104930_create_ticket_categories_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2025_12_21_104936_add_ticketing_fields_to_complaints_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2025_12_21_104941_create_ticket_comments_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2025_12_21_104945_create_ticket_change_history_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2025_12_21_104952_create_ticket_team_chat_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2025_12_21_104957_update_complaintattachments_for_tickets.php --force --no-interaction
php artisan migrate --path=database/migrations/2025_12_21_120026_create_ticket_statuses_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2025_12_21_120036_create_ticket_priorities_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_01_02_195408_create_ticket_chat_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_01_02_211354_create_ticket_chat_attachments_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_01_06_111548_create_ticket_assignments_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_01_06_113636_add_tat_to_ticket_assignments_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_01_07_123000_add_missing_ticket_columns_to_complaints_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_01_07_160713_make_tat_required_in_ticket_assignments_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_01_08_173734_create_ticket_permissions_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_01_20_181500_make_user_id_nullable_in_ticket_change_history.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_01_27_151712_add_external_id_to_ticket_chat_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_01_29_094627_make_user_id_nullable_in_ticket_chat_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_04_110441_add_developer_ticket_no_to_complaints.php --force --no-interaction
```

## 3) Seeders

No Help Desk-specific seeder class found in the replaced `fivet` branch.

## 4) Quick verification

```bash
php artisan migrate:status --no-interaction
```
