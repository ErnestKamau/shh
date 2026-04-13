<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Stores which extra fields to show in the report info block (Client/Sample Info Fields).
     * Sources: sample_header, sample_detail, or custom_field.
     */
    public function up(): void
    {
        Schema::create('crm_report_info_columns', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('crm_customer_id')->index();
            $table->string('source', 32); // sample_header, sample_detail, custom_field
            $table->string('source_key', 128); // column name or custom_field_id
            $table->string('display_label', 128)->nullable(); // optional override
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->foreign('crm_customer_id')->references('id')->on('crm_customers')->onDelete('cascade');
            $table->unique(['crm_customer_id', 'source', 'source_key'], 'crm_report_info_columns_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_report_info_columns');
    }
};
