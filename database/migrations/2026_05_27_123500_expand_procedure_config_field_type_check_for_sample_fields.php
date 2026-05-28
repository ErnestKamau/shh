<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('procedure_config_fields')) {
            return;
        }

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE procedure_config_fields DROP CONSTRAINT IF EXISTS procedure_config_fields_field_type_check');
        DB::statement("
            ALTER TABLE procedure_config_fields
            ADD CONSTRAINT procedure_config_fields_field_type_check
            CHECK (
                field_type IN (
                    'input',
                    'datetime',
                    'date',
                    'number',
                    'checkbox',
                    'textarea',
                    'dataset',
                    'dataset_multiselect',
                    'customer_select',
                    'lab_select',
                    'sample_select'
                )
            )
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('procedure_config_fields')) {
            return;
        }

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE procedure_config_fields DROP CONSTRAINT IF EXISTS procedure_config_fields_field_type_check');
        DB::statement("
            ALTER TABLE procedure_config_fields
            ADD CONSTRAINT procedure_config_fields_field_type_check
            CHECK (
                field_type IN (
                    'input',
                    'datetime',
                    'date',
                    'number',
                    'checkbox',
                    'textarea',
                    'dataset',
                    'dataset_multiselect'
                )
            )
        ");
    }
};
