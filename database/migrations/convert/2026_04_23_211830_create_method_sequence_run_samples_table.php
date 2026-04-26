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
        Schema::create('method_sequence_run_samples', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('run_id')->index('idx_ms_run_samples_run');
            $table->uuid('captured_result_id')->index('idx_ms_run_samples_captured');
            $table->uuid('sample_detail_id')->index('idx_method_sequence_run_samples_sample_detail_id_8725be80');
            $table->uuid('sample_header_id')->index('idx_method_sequence_run_samples_sample_header_id_95d63678');
            $table->timestamps();
            $table->foreign(['run_id'], 'fk_ms_run_samples_run')->references(['id'])->on('method_sequence_runs')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['captured_result_id'], 'fk_method_sequence_run_samples_captured_result_id_62e96e76')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_detail_id'], 'fk_method_sequence_run_samples_sample_detail_id_4f13d10b')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_method_sequence_run_samples_sample_header_id_f08806d6')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');



            $table->primary(['id']);



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_sequence_run_samples');
    }
};
