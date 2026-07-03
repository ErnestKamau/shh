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
            $table->uuid('id');
            $table->uuid('formula_version_id')->index('idx_worksheet_executions_formula_version_id_46a547d2');
            $table->uuid('sample_id')->nullable()->index('idx_worksheet_executions_sample_id_1f646435');
            $table->uuid('batch_id')->nullable()->index('idx_worksheet_executions_batch_id_7d4d37be');
            $table->uuid('executed_by')->index('idx_worksheet_executions_executed_by_ae648f51');
            $table->longText('execution_data');
            $table->text('final_result')->nullable();
            $table->enum('execution_mode', ['workflow', 'standalone'])->index('idx_worksheet_executions_execution_mode_c5eabd3f');
            $table->boolean('is_saved')->default(false)->index('idx_worksheet_executions_is_saved_0269b4d7');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['id']);
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
