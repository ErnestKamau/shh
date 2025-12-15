#!/bin/bash

# Script to run all migrations created between 2025-11-24 and 2025-12-15
# This script runs migrations in chronological order

echo "Running migrations from 2025-11-24 to 2025-12-15..."
echo "=================================================="

# Prerequisite tables (TemplateEngine module)
echo ""
echo "Running prerequisite TemplateEngine migrations..."
php artisan migrate --path=Modules/TemplateEngine/Database/Migrations/2024_12_08_000001_create_form_templates_table.php
php artisan migrate --path=Modules/TemplateEngine/Database/Migrations/2024_12_08_000003_create_form_fields_table.php

# December 5, 2025
echo ""
echo "Running December 5, 2025 migrations..."
php artisan migrate --path=database/migrations/2025_12_05_001856_create_sampletype_sample_point_relation_table.php
php artisan migrate --path=database/migrations/2025_12_05_001917_create_sampletype_area_relation_table.php

# December 7, 2025
echo ""
echo "Running December 7, 2025 migrations..."
php artisan migrate --path=database/migrations/2025_12_07_132900_create_equipment_disposals_table.php
php artisan migrate --path=database/migrations/2025_12_07_132901_create_equipment_disposal_files_table.php
php artisan migrate --path=database/migrations/2025_12_07_132902_create_equipment_disposal_approval_workflows_table.php
php artisan migrate --path=database/migrations/2025_12_07_132903_create_equipment_disposal_approval_workflow_steps_table.php
php artisan migrate --path=database/migrations/2025_12_07_132904_create_equipment_disposal_approvals_table.php
php artisan migrate --path=database/migrations/2025_12_07_132905_create_equipment_disposal_audit_logs_table.php

# December 10, 2025
echo ""
echo "Running December 10, 2025 migrations..."
php artisan migrate --path=database/migrations/2025_12_10_121613_add_parent_id_to_form_fields_table.php
php artisan migrate --path=database/migrations/2025_12_10_212940_create_equipment_evaluations_table.php
php artisan migrate --path=database/migrations/2025_12_10_213042_create_equipment_disposal_methods_table.php
php artisan migrate --path=database/migrations/2025_12_10_213537_add_decommissioning_fields_to_equipment_disposals_table.php

# December 14, 2025
echo ""
echo "Running December 14, 2025 migrations..."
php artisan migrate --path=database/migrations/2025_12_14_140219_add_parent_field_id_to_form_fields_table.php
php artisan migrate --path=database/migrations/2025_12_14_143657_add_process_columns_to_form_templates_table.php
php artisan migrate --path=database/migrations/2025_12_15_114915_make_picture_nullable_in_equipment_table.php

echo ""
echo "=================================================="
echo "All migrations completed!"
