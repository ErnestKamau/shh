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
        if (Schema::hasTable('customer_submission_form_columns')) {
            return;
        }
        Schema::create('customer_submission_form_columns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crm_customer_id');
            $table->string('source', 50); // 'sample_detail' | 'custom_field'
            $table->string('source_key', 100); // sample_details column name or custom_field_id
            $table->string('display_label', 200)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['crm_customer_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_submission_form_columns');
    }
};
