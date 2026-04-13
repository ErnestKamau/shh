#!/usr/bin/env bash
set -euo pipefail

cd /home/ernest/development/Nuvemite/2/polucon

php artisan migrate --force --path=database/migrations/2026_03_09_160049_add_is_internal_to_crm_customers_table.php
php artisan migrate --force --path=database/migrations/2026_03_09_165039_add_other_customers_to_crm_customer_contacts_table.php
php artisan migrate --force --path=database/migrations/2026_03_09_194108_seed_default_crm_evaluation_metrics.php
php artisan migrate --force --path=database/migrations/2026_03_10_000439_add_crm_contact_columns_to_users_table.php

echo "OK: recent CRM migrations ran."
