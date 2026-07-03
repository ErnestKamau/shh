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
        Schema::create('crm_company_sections', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->uuid('crm_customer_id')->index('idx_crm_company_sections_crm_customer_id_f059006b');
            $table->uuid('company_id')->index('idx_crm_company_sections_company_id_bcb14c46');
            $table->tinyInteger('active')->default(1);
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_company_sections');
    }
};
