<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Submission Form ↔ CRM Customers
        Schema::create('submission_form_customers', function (Blueprint $table) {
            $table->uuid('submission_form_id');
            $table->uuid('crm_customer_id');
            $table->timestamps();

            $table->unique(['submission_form_id', 'crm_customer_id'], 'sf_customers_unique');
            $table->index('submission_form_id', 'sf_customers_sf_id_index');
            $table->index('crm_customer_id', 'sf_customers_customer_id_index');

            $table->foreign('submission_form_id', 'sf_customers_sf_id_foreign')
                ->references('id')
                ->on('submission_forms')
                ->onUpdate('no action')
                ->onDelete('cascade');
        });

        // Submission Form ↔ Sample Types
        Schema::create('submission_form_sample_types', function (Blueprint $table) {
            $table->uuid('submission_form_id');
            $table->uuid('sample_type_id');
            $table->timestamps();

            $table->unique(['submission_form_id', 'sample_type_id'], 'sf_sample_types_unique');
            $table->index('submission_form_id', 'sf_sample_types_sf_id_index');
            $table->index('sample_type_id', 'sf_sample_types_st_id_index');

            $table->foreign('submission_form_id', 'sf_sample_types_sf_id_foreign')
                ->references('id')
                ->on('submission_forms')
                ->onUpdate('no action')
                ->onDelete('cascade');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('submission_form_sample_types');
        Schema::dropIfExists('submission_form_customers');
    }
};
