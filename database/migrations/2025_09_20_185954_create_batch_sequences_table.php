<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchSequencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_sequences', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('submission_form_instance_id');
            $table->integer('year');
            $table->integer('batch_sequence')->default(1);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('submission_form_instance_id', 'batch_seq_instance_id_foreign')
                  ->references('id')
                  ->on('submission_form_instances')
                  ->onDelete('cascade');

            // Unique constraint to ensure one sequence per form instance per year
            $table->unique(['submission_form_instance_id', 'year'], 'unique_form_year');

            // Indexes for performance
            $table->index('submission_form_instance_id', 'batch_seq_instance_idx');
            $table->index('year', 'batch_seq_year_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_sequences');
    }
}