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
        DB::statement('DROP SCHEMA IF EXISTS ai CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS reporting CASCADE');
        DB::statement('CREATE SCHEMA ai');
        DB::statement('CREATE SCHEMA reporting');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop schemas in down to avoid catastrophic data loss in development
    }
};
