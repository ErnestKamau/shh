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
        if (Schema::hasTable('submission_form_elements')) {
            return;
        }
        Schema::create('submission_form_elements', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_element_holder_id')->index('idx_submission_form_elements_submission_form_element_h_27ebdd26');
            $table->string('element_type', 128);
            $table->string('label');
            $table->string('name');
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_readonly')->default(false);
            $table->text('default_value')->nullable();
            $table->longText('validation_rules')->nullable();
            $table->string('mapping_table')->nullable()->comment('Target table: sample_headers or sample_details');
            $table->string('mapping_field')->nullable()->comment('Target field name in the mapping table');
            $table->boolean('is_mapped')->default(false)->comment('Whether this element is mapped to a database field');
            $table->string('depends_on_type', 100)->nullable()->comment('Element type of the select field this element depends on (e.g. client_select)');
            $table->string('depends_on_field')->nullable()->comment('The name attribute of the select element to listen to');
            $table->string('source_table')->nullable()->comment('DB table to read the value from (e.g. crm_customers)');
            $table->string('source_field')->nullable()->comment('Column in source_table to read (e.g. postal_address)');
            $table->longText('options')->nullable();
            $table->text('calculation_formula')->nullable();
            $table->longText('conditional_logic')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['submission_form_element_holder_id', 'sort_order'], 'idx_submission_form_elements_submission_form_element_h_51a3ccb6');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_form_elements');
    }
};
