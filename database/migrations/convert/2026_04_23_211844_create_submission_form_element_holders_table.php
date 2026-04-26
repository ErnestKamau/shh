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
        Schema::create('submission_form_element_holders', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_section_id')->index('sf_element_holders_section_id_idx');
            $table->enum('holder_type', ['field', 'text']);
            $table->integer('max_elements')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['submission_form_section_id', 'sort_order'], 'sf_element_holders_section_sort_idx');
            $table->foreign(['submission_form_section_id'], 'sf_element_holders_section_id_foreign')->references(['id'])->on('submission_form_sections')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_form_element_holders');
    }
};
