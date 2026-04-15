<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds missing columns to AI tables for:
     * - Knowledge chunk metadata (document_title)
     * - RAG query tracing (query_id)
     * 
     * This migration is idempotent and safe for fresh installations
     * as it only adds columns if they don't exist.
     */
    public function up(): void
    {
        // Add missing columns to ai_knowledge_chunks table
        if (Schema::connection('pgsql_ai')->hasTable('ai.ai_knowledge_chunks')) {
            Schema::connection('pgsql_ai')->table('ai.ai_knowledge_chunks', function (Blueprint $table) {
                // Add document_title if it doesn't exist
                // Stores the title/name of the document the chunk belongs to
                if (!Schema::connection('pgsql_ai')->hasColumn('ai.ai_knowledge_chunks', 'document_title')) {
                    $table->string('document_title', 500)->nullable()->after('content')
                        ->comment('Title or name of the source document');
                }
            });
        }

        // Add missing columns to ai_rag_traces table (if it exists)
        if (Schema::connection('pgsql_ai')->hasTable('ai.ai_rag_traces')) {
            Schema::connection('pgsql_ai')->table('ai.ai_rag_traces', function (Blueprint $table) {
                // Add query_id if it doesn't exist
                // Used to correlate query logs across services
                if (!Schema::connection('pgsql_ai')->hasColumn('ai.ai_rag_traces', 'query_id')) {
                    $table->uuid('query_id')->nullable()->after('id')
                        ->comment('Unique query identifier for cross-service tracing');
                }
            });
        }

        // Ensure ai_rag_traces table exists with all required columns
        // This handles fresh installations where the table might not exist yet
        if (!Schema::connection('pgsql_ai')->hasTable('ai.ai_rag_traces')) {
            Schema::connection('pgsql_ai')->create('ai.ai_rag_traces', function (Blueprint $table) {
                $table->id();
                $table->uuid('query_id')->nullable()->comment('Unique query identifier');
                $table->text('query')->nullable()->comment('The user query text');
                $table->unsignedBigInteger('user_id')->nullable()->comment('User who made the query');
                $table->unsignedBigInteger('company_id')->nullable()->comment('Company context');
                $table->string('query_type', 50)->nullable()->comment('semantic|keyword|hybrid');
                $table->float('query_confidence')->nullable()->comment('Query classification confidence');
                $table->unsignedInteger('vector_candidates_count')->default(0)->comment('Number of vector search results');
                $table->unsignedInteger('final_results_count')->default(0)->comment('Number of final results returned');
                $table->boolean('rerank_applied')->default(false)->comment('Whether re-ranking was applied');
                $table->unsignedInteger('rerank_latency_ms')->default(0)->comment('Re-ranking latency in milliseconds');
                $table->string('retrieval_method_used', 50)->nullable()->comment('vector_only|keyword_only|hybrid');
                $table->unsignedInteger('acl_filter_excluded_count')->default(0)->comment('Results excluded by ACL filter');
                $table->float('total_latency_ms')->default(0)->comment('Total query latency');
                $table->json('retrieved_chunk_ids')->nullable()->comment('Array of retrieved knowledge chunk IDs');
                $table->string('user_role', 50)->nullable()->comment('User role at time of query');
                $table->timestamps();

                // Indexes for performance
                $table->index('user_id');
                $table->index('company_id');
                $table->index('created_at');
                $table->index('query_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns (drop table if newly created during this migration)
        if (Schema::connection('pgsql_ai')->hasTable('ai.ai_knowledge_chunks')) {
            Schema::connection('pgsql_ai')->table('ai.ai_knowledge_chunks', function (Blueprint $table) {
                if (Schema::connection('pgsql_ai')->hasColumn('ai.ai_knowledge_chunks', 'document_title')) {
                    $table->dropColumn('document_title');
                }
            });
        }

        if (Schema::connection('pgsql_ai')->hasTable('ai.ai_rag_traces')) {
            Schema::connection('pgsql_ai')->table('ai.ai_rag_traces', function (Blueprint $table) {
                if (Schema::connection('pgsql_ai')->hasColumn('ai.ai_rag_traces', 'query_id')) {
                    $table->dropColumn('query_id');
                }
            });
        }
    }
};
