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
            $table->id();
            $table->unsignedBigInteger('sample_header_id');
            $table->unsignedBigInteger('sample_detail_id');
            $table->unsignedBigInteger('captured_result_id');
            $table->unsignedBigInteger('formular_id');
            $table->date('date');
            $table->string('lab_no');
            $table->string('sample_details');
            $table->time('time_in')->nullable();
            $table->unsignedBigInteger('done_by_user_id')->nullable();
            $table->time('time_out')->nullable();
            $table->unsignedBigInteger('read_by_user_id')->nullable();
            $table->date('read_date')->nullable();
            $table->string('final_result')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['sample_header_id', 'formular_id'], 'idx_worksheet_formula_batch');
            $table->index('captured_result_id', 'idx_worksheet_formula_captured');
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
