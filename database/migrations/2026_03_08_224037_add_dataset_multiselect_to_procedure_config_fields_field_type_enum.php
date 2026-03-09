<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE procedure_config_fields
            MODIFY COLUMN field_type ENUM('input', 'datetime', 'date', 'number', 'checkbox', 'textarea', 'dataset', 'dataset_multiselect') NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE procedure_config_fields
            MODIFY COLUMN field_type ENUM('input', 'datetime', 'date', 'number', 'checkbox', 'textarea', 'dataset') NOT NULL
        ");
    }
};
