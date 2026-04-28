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
        Schema::create('submission_form_instance_values', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_instance_id');
            $table->uuid('submission_form_element_id')->index('idx_submission_form_instance_values_submission_form_el_d96a67fe');
            $table->integer('array_index')->nullable();
            $table->text('value')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->timestamps();

            $table->index(['submission_form_instance_id', 'submission_form_element_id'], 'idx_submission_form_instance_values_submission_form_in_bbce8887');
            $table->unique(['submission_form_instance_id', 'submission_form_element_id', 'array_index'], 'sf_values_unique_instance_element');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_form_instance_values');
    }
};
