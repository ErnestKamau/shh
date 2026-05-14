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
        if (! $this->hasCompanyIdColumn()) {
            return;
        }

        // Fix company_id type mismatch in pgsql_ai connection
        DB::connection('pgsql_ai')->statement('ALTER TABLE ai.ai_knowledge_chunks ALTER COLUMN company_id TYPE VARCHAR(128) USING company_id::VARCHAR');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->hasCompanyIdColumn()) {
            return;
        }

        // Revert to BIGINT if possible, though data loss might occur if UUIDs were stored
        DB::connection('pgsql_ai')->statement('ALTER TABLE ai.ai_knowledge_chunks ALTER COLUMN company_id TYPE BIGINT USING company_id::BIGINT');
    }

    private function hasCompanyIdColumn(): bool
    {
        $row = DB::connection('pgsql_ai')->selectOne(
            "SELECT EXISTS (
                SELECT 1
                FROM information_schema.columns
                WHERE table_schema = 'ai'
                  AND table_name = 'ai_knowledge_chunks'
                  AND column_name = 'company_id'
            ) AS exists_flag"
        );

        return (bool) ($row->exists_flag ?? false);
    }
};
