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
        if (Schema::hasTable('custom_field_category_customers')) {
            return;
        }
        Schema::create('custom_field_category_customers', function (Blueprint $table) {
            $table->uuid('id');
            $table->unsignedBigInteger('custom_field_category_id')->index('idx_custom_field_category_customers_custom_field_categ_b97c4318');
            $table->uuid('crm_customer_id')->index('idx_custom_field_category_customers_crm_customer_id_74151f5e');
            $table->timestamps();

            $table->unique(['custom_field_category_id', 'crm_customer_id'], 'unique_category_customer');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_category_customers');
    }
};
