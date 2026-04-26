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
            $table->uuid('crm_customer_id')->index('idx_crm_company_sections_crm_customer_id_32df5641');
            $table->uuid('company_id')->index('idx_crm_company_sections_company_id_be79b97d');
            $table->tinyInteger('active')->default(1);
            $table->timestamps();
            $table->foreign(['crm_customer_id'], 'fk_crm_company_sections_crm_customer_id_81f517eb')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_crm_company_sections_company_id_6771526d')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

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
