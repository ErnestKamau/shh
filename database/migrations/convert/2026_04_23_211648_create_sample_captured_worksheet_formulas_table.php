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
        Schema::create('sample_captured_worksheet_formulas', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_header_id')->index('idx_sample_captured_worksheet_formulas_sample_header_i_36255b98');
            $table->uuid('sample_detail_id')->index('idx_sample_captured_worksheet_formulas_sample_detail_i_a8e63c78');
            $table->uuid('captured_result_id')->index('idx_sample_captured_worksheet_formulas_captured_result_eabd55d2');
            $table->uuid('formular_id');
            $table->date('date');
            $table->string('lab_no');
            $table->string('sample_details');
            $table->time('time_in')->nullable();
            $table->uuid('done_by_user_id')->nullable();
            $table->time('time_out')->nullable();
            $table->uuid('read_by_user_id')->nullable();
            $table->date('read_date')->nullable();
            $table->string('final_result')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->uuid('posted_by_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sample_header_id', 'formular_id'], 'idx_sample_captured_worksheet_formulas_sample_header_i_2b010a8f');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_captured_worksheet_formulas');
    }
};
