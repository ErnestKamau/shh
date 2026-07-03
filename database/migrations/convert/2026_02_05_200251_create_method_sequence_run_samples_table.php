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
        if (Schema::hasTable('method_sequence_run_samples')) {
            return;
        }
        Schema::create('method_sequence_run_samples', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('run_id')->index('idx_method_sequence_run_samples_run_id_69f20a63');
            $table->uuid('captured_result_id')->index('idx_method_sequence_run_samples_captured_result_id_72107646');
            $table->uuid('sample_detail_id')->index('idx_method_sequence_run_samples_sample_detail_id_ca5276a2');
            $table->uuid('sample_header_id')->index('idx_method_sequence_run_samples_sample_header_id_78c581ee');
            $table->timestamps();

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
