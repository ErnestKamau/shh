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
        Schema::create('customer_submission_form_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crm_customer_id');
            $table->unsignedBigInteger('custom_field_id');
            $table->boolean('is_required')->default(false);
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['crm_customer_id', 'custom_field_id'], 'unique_customer_field');
            $table->index('crm_customer_id');
            $table->index('custom_field_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_submission_form_custom_fields');
    }
};
