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
        DB::statement("ALTER TABLE formula_steps MODIFY step_type ENUM('input', 'derived', 'lookup', 'parameter_result') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE formula_steps MODIFY step_type ENUM('input', 'derived', 'lookup') NOT NULL");
    }
};
