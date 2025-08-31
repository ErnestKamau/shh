<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionFormElementHoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submission_form_element_holders', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('submission_form_section_id');
            $table->enum('holder_type', ['field', 'text']);
            $table->integer('max_elements')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('submission_form_section_id', 'sf_element_holders_section_id_foreign')->references('id')->on('submission_form_sections')->onDelete('cascade');

            // Indexes for performance
            $table->index('submission_form_section_id', 'sf_element_holders_section_id_idx');
            $table->index(['submission_form_section_id', 'sort_order'], 'sf_element_holders_section_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submission_form_element_holders');
    }
}