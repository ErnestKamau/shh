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
        Schema::create('crm_company_units', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->uuid('company_id')->index('idx_crm_company_units_company_id_c6ee609a');
            $table->uuid('crm_customer_id')->index('idx_crm_company_units_crm_customer_id_8378aae2');
            $table->uuid('crm_company_section_id')->nullable()->index('crm_company_units_crm_company_section_id_foreign');
            $table->integer('active');
            $table->timestamps();
            $table->foreign(['crm_company_section_id'], 'fk_crm_company_units_crm_company_section_id_51fb10eb')->references(['id'])->on('crm_company_sections')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_crm_company_units_company_id_07c30929')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['crm_customer_id'], 'fk_crm_company_units_crm_customer_id_72d7152d')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_company_units');
    }
};
