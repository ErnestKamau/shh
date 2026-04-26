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
        Schema::create('sample_progress', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_detail_id');
            $table->uuid('stage_header_id');
            $table->uuid('test_stage_id')->index('sample_progress_test_stage_id_foreign');
            $table->uuid('analyte_id')->index('sample_progress_analyte_id_foreign');
            $table->uuid('method_id')->index('sample_progress_method_id_foreign');
            $table->integer('current_day')->default(1);
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'cancelled'])->default('not_started');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->date('current_day_date')->nullable();
            $table->timestamps();
            $table->text('observations')->nullable();
            $table->text('remarks')->nullable();

            $table->index(['sample_detail_id', 'analyte_id'], 'idx_sample_progress_sample_detail_id_analyte_id_2d980d8e');
            $table->index(['stage_header_id', 'method_id'], 'idx_sample_progress_stage_header_id_method_id_6a9e5a44');
            $table->index(['status', 'current_day_date'], 'idx_sample_progress_status_current_day_date_e378a7f1');
            $table->foreign(['analyte_id'], 'fk_sample_progress_analyte_id_628b0345')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['method_id'], 'fk_sample_progress_method_id_f95a9f19')->references(['id'])->on('analysis_methods')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['sample_detail_id'], 'fk_sample_progress_sample_detail_id_b3d1e95e')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['stage_header_id'], 'fk_sample_progress_stage_header_id_9ad48958')->references(['id'])->on('stage_headers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['test_stage_id'], 'fk_sample_progress_test_stage_id_5cb1229d')->references(['id'])->on('test_stages')->onUpdate('no action')->onDelete('no action');
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
