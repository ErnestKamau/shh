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
            $table->uuid('sample_header_id')->index('idx_sample_captured_worksheet_formulas_sample_header_i_c7f01a6e');
            $table->uuid('sample_detail_id')->index('idx_sample_captured_worksheet_formulas_sample_detail_i_1b2c9e45');
            $table->uuid('captured_result_id')->index('idx_worksheet_formula_captured');
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

            $table->index(['sample_header_id', 'formular_id'], 'idx_worksheet_formula_batch');
            $table->foreign(['captured_result_id'], 'fk_sample_captured_worksheet_formulas_captured_result_f84b2efd')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_detail_id'], 'fk_sample_captured_worksheet_formulas_sample_detail_id_d79e98f0')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_sample_captured_worksheet_formulas_sample_header_id_bc783e08')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['formular_id'], 'fk_sample_captured_worksheet_formulas_formular_id')->references(['id'])->on('formulas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['done_by_user_id'], 'fk_worksheet_done_by_user')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['read_by_user_id'], 'fk_worksheet_read_by_user')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['posted_by_user_id'], 'fk_worksheet_posted_by_user')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');



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
