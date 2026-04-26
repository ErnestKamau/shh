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
        Schema::create('sample_sequences', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('batch_code')->index('sample_seq_batch_idx');
            $table->integer('sample_sequence')->default(1);
            $table->timestamps();

            $table->unique(['batch_code'], 'unique_batch');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_sequences');
    }
};
