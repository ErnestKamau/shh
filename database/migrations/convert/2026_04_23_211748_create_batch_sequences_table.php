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
            $table->uuid('submission_form_instance_id')->index('batch_seq_instance_idx');
            $table->integer('year')->index('batch_seq_year_idx');
            $table->integer('batch_sequence')->default(1);
            $table->timestamps();

            $table->unique(['submission_form_instance_id', 'year'], 'unique_form_year');
            $table->foreign(['submission_form_instance_id'], 'batch_seq_instance_id_foreign')->references(['id'])->on('submission_form_instances')->onUpdate('no action')->onDelete('cascade');
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
