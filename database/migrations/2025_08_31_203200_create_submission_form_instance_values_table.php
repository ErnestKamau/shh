<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionFormInstanceValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submission_form_instance_values', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('submission_form_instance_id');
            $table->bigInteger('submission_form_element_id');
            $table->text('value')->nullable();
            $table->string('file_path', 500)->nullable(); // For file uploads
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('submission_form_instance_id', 'sf_values_instance_id_foreign')->references('id')->on('submission_form_instances')->onDelete('cascade');
            $table->foreign('submission_form_element_id', 'sf_values_element_id_foreign')->references('id')->on('submission_form_elements');

            // Unique constraint to prevent duplicate values for same instance/element combination
            $table->unique(['submission_form_instance_id', 'submission_form_element_id'], 'sf_values_unique_instance_element');

            // Indexes for performance
            $table->index(['submission_form_instance_id', 'submission_form_element_id'], 'sf_values_instance_element_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submission_form_instance_values');
    }
}