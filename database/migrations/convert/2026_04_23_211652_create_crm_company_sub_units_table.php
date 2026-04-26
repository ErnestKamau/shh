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
        Schema::create('crm_company_sub_units', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code');
            $table->uuid('crm_customer_id')->index('idx_crm_company_sub_units_crm_customer_id_22509da3');
            $table->uuid('crm_company_unit_id')->index('idx_crm_company_sub_units_crm_company_unit_id_1ddf0888');
            $table->tinyInteger('active')->default(1);
            $table->timestamps();
            $table->foreign(['crm_company_unit_id'], 'fk_crm_company_sub_units_crm_company_unit_id_b912448f')->references(['id'])->on('crm_company_units')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['crm_customer_id'], 'fk_crm_company_sub_units_crm_customer_id_fe5a66b5')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_company_sub_units');
    }
};
