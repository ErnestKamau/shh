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
        // Extend field_type enum to support more configurable field input types
        DB::statement("
            ALTER TABLE procedure_config_fields
            MODIFY COLUMN field_type ENUM('input', 'datetime', 'date', 'number', 'checkbox', 'textarea') NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum definition
        DB::statement("
            ALTER TABLE procedure_config_fields
            MODIFY COLUMN field_type ENUM('input', 'datetime', 'date') NOT NULL
        ");
    }
};
