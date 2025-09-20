<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleSequencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sample_sequences', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('batch_code', 255);
            $table->integer('sample_sequence')->default(1);
            $table->timestamps();

            // Unique constraint to ensure one sequence per batch
            $table->unique('batch_code', 'unique_batch');

            // Indexes for performance
            $table->index('batch_code', 'sample_seq_batch_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sample_sequences');
    }
}