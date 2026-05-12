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
        // Fix company_id type mismatch in pgsql_ai connection
        DB::connection('pgsql_ai')->statement('ALTER TABLE ai.ai_knowledge_chunks ALTER COLUMN company_id TYPE VARCHAR(128) USING company_id::VARCHAR');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to BIGINT if possible, though data loss might occur if UUIDs were stored
        DB::connection('pgsql_ai')->statement('ALTER TABLE ai.ai_knowledge_chunks ALTER COLUMN company_id TYPE BIGINT USING company_id::BIGINT');
    }
};
