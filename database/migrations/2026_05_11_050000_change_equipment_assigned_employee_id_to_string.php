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
        // Users use UUID IDs, so keep this as text-compatible to accept UUID values.
        DB::statement('ALTER TABLE equipment ALTER COLUMN assigned_employee_id TYPE varchar(255) USING assigned_employee_id::text');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to integer where possible; non-numeric values are set to NULL.
        DB::statement("ALTER TABLE equipment ALTER COLUMN assigned_employee_id TYPE integer USING (CASE WHEN assigned_employee_id ~ '^[0-9]+$' THEN assigned_employee_id::integer ELSE NULL END)");
    }
};
