<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $conn = 'pgsql_ai';
        Schema::connection($conn)->table('ai.ai_knowledge_chunks', function (Blueprint $table) use ($conn) {
            if (!Schema::connection($conn)->hasColumn('ai.ai_knowledge_chunks', 'chunk_id')) {
                $table->string('chunk_id')->nullable()->unique()->index();
            }
            if (!Schema::connection($conn)->hasColumn('ai.ai_knowledge_chunks', 'updated_at')) {
                $table->timestampTz('updated_at')->useCurrent()->useCurrentOnUpdate();
            }
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql_ai')->table('ai_knowledge_chunks', function (Blueprint $table) {
            $table->dropColumn(['chunk_id', 'updated_at']);
        });
    }
};
