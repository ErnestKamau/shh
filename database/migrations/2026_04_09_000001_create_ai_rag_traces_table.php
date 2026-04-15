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
        $tableName = 'ai.ai_rag_traces';

        // Create ai_rag_traces table for diagnostics and monitoring
        if (!Schema::connection('pgsql_ai')->hasTable($tableName)) {
            Schema::connection('pgsql_ai')->create($tableName, function (Blueprint $table) {
                $table->id();
                $table->uuid('query_id')->index();
                $table->text('query');
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                
                // Query classification info
                $table->string('query_type')->nullable(); // exact_code, semantic, mixed, ambiguous
                $table->float('query_confidence')->nullable(); // 0-1
                
                // Candidate counts at each stage
                $table->integer('vector_candidates_count')->default(0);
                $table->integer('keyword_candidates_count')->default(0);
                $table->integer('fused_candidates_count')->default(0);
                $table->integer('final_results_count')->default(0);
                
                // Reranker info
                $table->boolean('rerank_applied')->default(false);
                $table->float('rerank_latency_ms')->nullable();
                
                // Retrieval method used
                $table->string('retrieval_method_used')->nullable(); // vector_only, keyword_only, hybrid, reranked
                
                // ACL filtering
                $table->integer('acl_filter_excluded_count')->default(0);
                
                // Performance metrics
                $table->float('total_latency_ms')->nullable();
                
                // Retrieved chunks
                $table->json('retrieved_chunk_ids')->nullable();
                
                // User context
                $table->string('user_role')->nullable();
                
                $table->timestamps();
                
                // Indexes for common queries
                $table->index(['created_at']);
                $table->index(['user_id', 'created_at']);
                $table->index(['query_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql_ai')->dropIfExists('ai.ai_rag_traces');
    }
};
