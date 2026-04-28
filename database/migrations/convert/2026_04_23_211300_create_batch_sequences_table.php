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
        Schema::create('batch_sequences', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_instance_id')->index('idx_batch_sequences_submission_form_instance_id_1f4bdd79');
            $table->integer('year')->index('idx_batch_sequences_year_cd1f4a7f');
            $table->integer('batch_sequence')->default(1);
            $table->timestamps();

            $table->unique(['submission_form_instance_id', 'year'], 'unique_form_year');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_sequences');
    }
};
