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
        Schema::create('custom_field_category_customers', function (Blueprint $table) {
            $table->uuid('id');
            $table->unsignedBigInteger('custom_field_category_id')->index('idx_custom_field_category_customers_custom_field_categ_31c6a1b3');
            $table->uuid('crm_customer_id')->index('idx_custom_field_category_customers_crm_customer_id_07f57c82');
            $table->timestamps();

            $table->unique(['custom_field_category_id', 'crm_customer_id'], 'unique_category_customer');
            $table->foreign(['crm_customer_id'], 'fk_custom_field_category_customers_crm_customer_id_0f1f11c5')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');

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
