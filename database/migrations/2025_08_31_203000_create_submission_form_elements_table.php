<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionFormElementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submission_form_elements', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('submission_form_element_holder_id');
            $table->enum('element_type', [
                'text', 'number', 'email', 'date', 'datetime', 'textarea', 
                'select', 'radio', 'checkbox', 'file', 'signature', 'calculation'
            ]);
            $table->string('label');
            $table->string('name');
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_readonly')->default(false);
            $table->text('default_value')->nullable();
            $table->json('validation_rules')->nullable();
            $table->json('options')->nullable(); // For select, radio, checkbox options
            $table->text('calculation_formula')->nullable(); // For calculated fields
            $table->json('conditional_logic')->nullable(); // Show/hide based on other fields
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('submission_form_element_holder_id', 'sf_elements_holder_id_foreign')->references('id')->on('submission_form_element_holders')->onDelete('cascade');

            // Indexes for performance
            $table->index('submission_form_element_holder_id', 'sf_elements_holder_id_idx');
            $table->index(['submission_form_element_holder_id', 'sort_order'], 'sf_elements_holder_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submission_form_elements');
    }
}