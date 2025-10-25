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
        Schema::create('worksheet_executions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('formula_version_id');
            $table->bigInteger('sample_id')->nullable();
            $table->bigInteger('batch_id')->nullable();
            $table->bigInteger('executed_by');
            $table->json('execution_data'); // Complete execution data including inputs, derived values, lookups
            $table->text('final_result')->nullable();
            $table->enum('execution_mode', ['workflow', 'standalone']);
            $table->boolean('is_saved')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['formula_version_id']);
            $table->index(['sample_id']);
            $table->index(['batch_id']);
            $table->index(['executed_by']);
            $table->index(['execution_mode']);
            $table->index(['is_saved']);
        });

        // Add foreign key constraints
        Schema::table('worksheet_executions', function (Blueprint $table) {
            $table->foreign('formula_version_id')->references('id')->on('formula_versions')->onDelete('cascade');
            $table->foreign('sample_id')->references('id')->on('sample_details')->onDelete('set null');
            $table->foreign('batch_id')->references('id')->on('sample_headers')->onDelete('set null');
            $table->foreign('executed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worksheet_executions');
    }
};