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
        if (Schema::hasTable('crm_company_sub_units')) {
            return;
        }
        Schema::create('crm_company_sub_units', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code');
            $table->uuid('crm_customer_id')->index('idx_crm_company_sub_units_crm_customer_id_d77c97fa');
            $table->uuid('crm_company_unit_id')->index('idx_crm_company_sub_units_crm_company_unit_id_6d5d2d30');
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
        Schema::dropIfExists('crm_company_sub_units');
    }
};
