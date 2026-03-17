# Migration Commands for 2025-12-25 to 2026-01-06

This document contains the migration commands for all migrations created between December 25, 2025 and January 6, 2026.

## Migrations Found

### 1. TemplateEngine Module Migration
- **File**: `Modules/TemplateEngine/Database/Migrations/2025_12_27_140612_create_form_template_variables_table.php`
- **Date**: December 27, 2025
- **Description**: Creates the `form_template_variables` table for storing template variables

### 2. Main Migration
- **File**: `database/migrations/2026_01_04_172124_add_type_to_form_templates_table.php`
- **Date**: January 4, 2026
- **Description**: Adds `type` column to `form_templates` table

## Migration Commands

### Option 1: Run All Pending Migrations (Recommended)
Since TemplateEngine module migrations are auto-loaded via the service provider, the simplest approach is to run all pending migrations:

```bash
php artisan migrate
```

This will automatically discover and run both migrations in the correct order.

### Option 2: Run Specific Migrations

#### For the TemplateEngine Module Migration:
Since module migrations are loaded via `loadMigrationsFrom()` in the service provider, they are automatically discovered. However, you can ensure it runs by:

```bash
# Run all pending migrations (module migrations are auto-discovered)
php artisan migrate
```

#### For the Main Migration:
```bash
php artisan migrate --path=database/migrations/2026_01_04_172124_add_type_to_form_templates_table.php
```

### Option 3: Using the Migration Script
A bash script has been created to run these migrations:

```bash
./run_migrations_2025_12_25_to_2026_01_06.sh
```

## Migration Details

### Migration 1: create_form_template_variables_table
**Location**: `Modules/TemplateEngine/Database/Migrations/2025_12_27_140612_create_form_template_variables_table.php`

**Creates Table**: `form_template_variables`

**Columns**:
- `id` (bigint, primary key)
- `form_template_id` (foreign key to `form_templates`)
- `name` (string)
- `type` (string) - static, database, system
- `data_type` (string) - string, number, boolean, date, collection, record
- `config` (json, nullable)
- `validation_rules` (json, nullable)
- `created_by` (bigint, nullable, foreign key to `users`)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Constraints**:
- Unique constraint on `[form_template_id, name]`
- Foreign key constraint on `form_template_id` (cascade delete)
- Foreign key constraint on `created_by` (set null on delete)

### Migration 2: add_type_to_form_templates_table
**Location**: `database/migrations/2026_01_04_172124_add_type_to_form_templates_table.php`

**Modifies Table**: `form_templates`

**Adds Column**:
- `type` (string, default: 'form') - Added after `category` column
  - Possible values: 'form', 'report'

## Verification

After running the migrations, verify they were executed:

```bash
php artisan migrate:status | grep -E "(2025_12_27|2026_01_04)"
```

Expected output:
```
2025_12_27_140612_create_form_template_variables_table ........... [XXX] Ran
2026_01_04_172124_add_type_to_form_templates_table ............... [XXX] Ran
```

## Rollback

To rollback these migrations:

```bash
# Rollback the last batch
php artisan migrate:rollback

# Or rollback specific number of steps
php artisan migrate:rollback --step=2
```

## Notes

- TemplateEngine module migrations are automatically discovered by Laravel because they are registered in `TemplateEngineServiceProvider::boot()` using `loadMigrationsFrom()`
- The migrations should be run in order, but Laravel handles this automatically based on the timestamp in the filename
- Both migrations are already tracked in the migrations table (as shown in `migrate:status`)
