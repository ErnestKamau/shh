<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add tsvector column for full-text search if it doesn't exist
        if (!Schema::connection('pgsql_ai')->hasColumn('ai_knowledge_chunks', 'tsvector_content')) {
            Schema::connection('pgsql_ai')->table('ai_knowledge_chunks', function (Blueprint $table) {
                // Add tsvector column for full-text search
                // This will be automatically updated via generated column (computed column)
                DB::connection('pgsql_ai')->statement(
                    'ALTER TABLE ai.ai_knowledge_chunks ADD COLUMN tsvector_content TSVECTOR 
                    GENERATED ALWAYS AS (to_tsvector(\'english\', COALESCE(content, \'\'))) STORED'
                );
            });

            // Create GIN index on tsvector_content for efficient FTS queries
            DB::connection('pgsql_ai')->statement(
                'CREATE INDEX idx_ai_knowledge_chunks_tsvector_content 
                ON ai.ai_knowledge_chunks USING GIN (tsvector_content)'
            );

            // Create index on entity_type and entity_id for quick ACL filtering
            DB::connection('pgsql_ai')->statement(
                'CREATE INDEX IF NOT EXISTS idx_ai_knowledge_chunks_entity 
                ON ai.ai_knowledge_chunks (entity_type, entity_id)'
            );

            // Create index on required_permission for ACL enforcement
            DB::connection('pgsql_ai')->statement(
                'CREATE INDEX IF NOT EXISTS idx_ai_knowledge_chunks_permission 
                ON ai.ai_knowledge_chunks (required_permission)'
            );

            // Create composite index for common retrieval patterns
            DB::connection('pgsql_ai')->statement(
                'CREATE INDEX IF NOT EXISTS idx_ai_knowledge_chunks_composite 
                ON ai.ai_knowledge_chunks (collection_name, entity_type, created_at)'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql_ai')->table('ai_knowledge_chunks', function (Blueprint $table) {
            // Drop indexes
            DB::connection('pgsql_ai')->statement(
                'DROP INDEX IF EXISTS idx_ai_knowledge_chunks_tsvector_content'
            );
            DB::connection('pgsql_ai')->statement(
                'DROP INDEX IF EXISTS idx_ai_knowledge_chunks_entity'
            );
            DB::connection('pgsql_ai')->statement(
                'DROP INDEX IF EXISTS idx_ai_knowledge_chunks_permission'
            );
            DB::connection('pgsql_ai')->statement(
                'DROP INDEX IF EXISTS idx_ai_knowledge_chunks_composite'
            );

            // Drop tsvector column
            DB::connection('pgsql_ai')->statement(
                'ALTER TABLE ai.ai_knowledge_chunks DROP COLUMN IF EXISTS tsvector_content'
            );
        });
    }
};
