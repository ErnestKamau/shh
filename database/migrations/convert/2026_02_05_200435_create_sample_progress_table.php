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
        if (Schema::hasTable('sample_progress')) {
            return;
        }
        Schema::create('sample_progress', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_detail_id');
            $table->uuid('stage_header_id');
            $table->uuid('test_stage_id')->index('idx_sample_progress_test_stage_id_78694bcb');
            $table->uuid('analyte_id')->index('idx_sample_progress_analyte_id_397dda06');
            $table->uuid('method_id')->index('idx_sample_progress_method_id_c85f2144');
            $table->integer('current_day')->default(1);
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'cancelled'])->default('not_started');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->date('current_day_date')->nullable();
            $table->timestamps();
            $table->text('observations')->nullable();
            $table->text('remarks')->nullable();

            $table->index(['sample_detail_id', 'analyte_id'], 'idx_sample_progress_sample_detail_id_analyte_id_acd39d52');
            $table->index(['stage_header_id', 'method_id'], 'idx_sample_progress_stage_header_id_method_id_83798036');
            $table->index(['status', 'current_day_date'], 'idx_sample_progress_status_current_day_date_58d6154d');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_progress');
    }
};
