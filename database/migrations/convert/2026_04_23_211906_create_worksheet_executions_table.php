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
            $table->uuid('formula_version_id')->index('idx_worksheet_executions_formula_version_id_f68e03ca');
            $table->uuid('sample_id')->nullable()->index('idx_worksheet_executions_sample_id_f000d0e8');
            $table->uuid('batch_id')->nullable()->index('idx_worksheet_executions_batch_id_589d7792');
            $table->uuid('executed_by')->index('idx_worksheet_executions_executed_by_67cffca4');
            $table->longText('execution_data');
            $table->text('final_result')->nullable();
            $table->enum('execution_mode', ['workflow', 'standalone'])->index('idx_worksheet_executions_execution_mode_6f9895a5');
            $table->boolean('is_saved')->default(false)->index('idx_worksheet_executions_is_saved_5f18a690');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign(['batch_id'], 'fk_worksheet_executions_batch_id_bd61f8d9')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['executed_by'], 'fk_worksheet_executions_executed_by_4db9f8fe')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['formula_version_id'], 'fk_worksheet_executions_formula_version_id_bad11461')->references(['id'])->on('formula_versions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_id'], 'fk_worksheet_executions_sample_id_62862059')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('set null');
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
