<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_category_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('custom_field_category_id');
            $table->unsignedBigInteger('crm_customer_id');
            $table->timestamps();

            $table->unique(['custom_field_category_id', 'crm_customer_id'], 'unique_category_customer');
            $table->index('custom_field_category_id');
            $table->index('crm_customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_category_customers');
    }
};
