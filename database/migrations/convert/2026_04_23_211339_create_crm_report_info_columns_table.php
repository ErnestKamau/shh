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
        Schema::create('crm_report_info_columns', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('crm_customer_id')->index('idx_crm_report_info_columns_crm_customer_id_d04a005e');
            $table->string('source', 32);
            $table->string('source_key', 128);
            $table->string('display_label', 128)->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['crm_customer_id', 'source', 'source_key'], 'crm_report_info_columns_unique');
            $table->primary(['id']);
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
