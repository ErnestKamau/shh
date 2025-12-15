# Migration Summary: 2025-11-24 to 2025-12-15

This document lists all migration files created between November 24, 2025 and December 15, 2025.

## Migration Files (14 total)

### December 5, 2025
1. **2025_12_05_001856_create_sampletype_sample_point_relation_table.php**
   - Creates `sampletype_sample_point_relation` table
   - Links sample types to sample points
   - Foreign keys: `sample_types`, `crm_sample_points`
   - Unique constraint on `sample_type_id` and `sample_point_id`

2. **2025_12_05_001917_create_sampletype_area_relation_table.php**
   - Creates `sampletype_area_relation` table
   - Links sample types to areas
   - Foreign keys: `sample_types`, `crm_areas`
   - Unique constraint on `sample_type_id` and `area_id`

### December 7, 2025
3. **2025_12_07_132900_create_equipment_disposals_table.php**
   - Creates `equipment_disposals` table
   - Main table for equipment disposal requests
   - Fields: justification, proposed_method, risk_level, status, disposal_date, etc.
   - Foreign keys: `equipment`, `users` (requested_by, executed_by, witness_id)

4. **2025_12_07_132901_create_equipment_disposal_files_table.php**
   - Creates `equipment_disposal_files` table
   - Stores files related to equipment disposals
   - Fields: file_path, file_name, file_type, mime_type, file_size
   - Foreign key: `equipment_disposals`

5. **2025_12_07_132902_create_equipment_disposal_approval_workflows_table.php**
   - Creates `equipment_disposal_approval_workflows` table
   - Defines approval workflows for equipment disposal
   - Fields: workflow_name, equipment_type_id, location_id, is_active
   - Foreign keys: `asset_types`, `asset_locations`, `users`

6. **2025_12_07_132903_create_equipment_disposal_approval_workflow_steps_table.php**
   - Creates `equipment_disposal_approval_workflow_steps` table
   - Defines steps within approval workflows
   - Fields: step_order, step_name, assignee_type, assignee_id, is_required
   - Foreign key: `equipment_disposal_approval_workflows`

7. **2025_12_07_132904_create_equipment_disposal_approvals_table.php**
   - Creates `equipment_disposal_approvals` table
   - Stores approval decisions for disposal requests
   - Fields: step, approver_id, decision, remarks, signature_path
   - Foreign keys: `equipment_disposals`, `users`

8. **2025_12_07_132905_create_equipment_disposal_audit_logs_table.php**
   - Creates `equipment_disposal_audit_logs` table
   - Tracks all actions on disposal requests
   - Fields: action, user_id, ip_address, old_values, new_values
   - Foreign keys: `equipment_disposals`, `users`

### December 10, 2025
9. **2025_12_10_121613_add_parent_id_to_form_fields_table.php**
   - Adds `parent_id` column to `form_fields` table
   - Enables hierarchical form field structure
   - Foreign key: `form_fields` (self-referencing)

10. **2025_12_10_212940_create_equipment_evaluations_table.php**
    - Creates `equipment_evaluations` table
    - Stores equipment evaluation records
    - Fields: physical_condition, calibration_status, repair_cost, recommendation
    - Foreign keys: `equipment`, `users`

11. **2025_12_10_213042_create_equipment_disposal_methods_table.php**
    - Creates `equipment_disposal_methods` table
    - Defines available disposal methods
    - Fields: method, display_name, applicable_categories, regulatory_requirements
    - Unique constraint on `method`

12. **2025_12_10_213537_add_decommissioning_fields_to_equipment_disposals_table.php**
    - Adds decommissioning fields to `equipment_disposals` table
    - Fields: decommissioning_checklist_json, decommissioning_date, equipment_labeled, etc.
    - Adds transport and waste handler information
    - Foreign keys: `users` (decommissioned_by), `equipment_evaluations`

### December 14, 2025
13. **2025_12_14_140219_add_parent_field_id_to_form_fields_table.php**
    - Adds `parent_field_id` column to `form_fields` table
    - Note: Similar to migration #9, but uses different column name
    - Foreign key: `form_fields` (self-referencing)

14. **2025_12_14_143657_add_process_columns_to_form_templates_table.php**
    - Adds polymorphic `process` columns to `form_templates` table
    - Adds `process_id` and `process_type` columns
    - Enables form templates to be associated with various process types

## Running All Migrations

To run all these migrations, use:
```bash
php artisan migrate
```

Or to run them individually:
```bash
php artisan migrate --path=database/migrations/2025_12_05_001856_create_sampletype_sample_point_relation_table.php
# ... and so on for each file
```

## Notes

- Some migrations use the old class-based syntax (e.g., `CreateEquipmentDisposalsTable extends Migration`)
- Others use the new anonymous class syntax (e.g., `return new class extends Migration`)
- Migration #9 and #13 both add parent-related columns to `form_fields` - ensure this is intentional
- All equipment disposal-related migrations should be run in order due to foreign key dependencies
