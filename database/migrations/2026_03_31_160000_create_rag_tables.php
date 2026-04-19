<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'pgsql_ai';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = $this->getConnection();

        // 1. Enable pgvector extension
        DB::connection($connection)->statement('CREATE EXTENSION IF NOT EXISTS vector');

        // 2. Knowledge Chunks Table
        if (!Schema::connection($connection)->hasTable('ai.ai_knowledge_chunks')) {
            DB::connection($connection)->statement("
                CREATE TABLE ai.ai_knowledge_chunks (
                    id SERIAL PRIMARY KEY,
                    collection_name TEXT NOT NULL, -- e.g., 'audits', 'sops', 'risks'
                    entity_type TEXT,              -- e.g., 'App\\Models\\Audit'
                    entity_id TEXT,                -- Reference to source entity (Support for BIGINT, UUID, etc.)
                    content TEXT NOT NULL,         -- Chunks of raw text
                    embedding vector(1536),        -- Default matches OpenAI/Gemini standard
                    metadata JSONB,                -- Useful for filtering (e.g., tags, authors)
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                )
            ");

            // Index for vector similarity (Cosine search)
            DB::connection($connection)->statement(
                "CREATE INDEX idx_knowledge_chunks_embedding ON ai.ai_knowledge_chunks USING hnsw (embedding vector_cosine_ops)"
            );
            
            DB::connection($connection)->statement(
                "CREATE INDEX idx_knowledge_chunks_ref ON ai.ai_knowledge_chunks (entity_type, entity_id)"
            );
            
            DB::connection($connection)->statement(
                "CREATE INDEX idx_knowledge_chunks_collection ON ai.ai_knowledge_chunks (collection_name)"
            );
        }

        // 3. RAG Traces (Observability/Feedback)
        if (!Schema::connection($connection)->hasTable('ai.ai_rag_traces')) {
            DB::connection($connection)->statement("
                CREATE TABLE ai.ai_rag_traces (
                    id SERIAL PRIMARY KEY,
                    query TEXT NOT NULL,
                    retrieved_chunk_ids INT[],
                    response TEXT,
                    model_version TEXT,
                    latency_ms INT,
                    feedback_score INT, -- 1-5 or thumb up/down
                    metadata JSONB,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                )
            ");

            DB::connection($connection)->statement(
                "CREATE INDEX idx_rag_traces_created ON ai.ai_rag_traces (created_at DESC)"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = $this->getConnection();
        DB::connection($connection)->statement('DROP TABLE IF EXISTS ai.ai_rag_traces CASCADE');
        DB::connection($connection)->statement('DROP TABLE IF EXISTS ai.ai_knowledge_chunks CASCADE');
    }
};
