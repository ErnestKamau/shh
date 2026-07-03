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
            $table->uuid('company_id')->index('idx_crm_company_units_company_id_063f2480');
            $table->uuid('crm_customer_id')->index('idx_crm_company_units_crm_customer_id_0feb0baa');
            $table->uuid('crm_company_section_id')->nullable()->index('idx_crm_company_units_crm_company_section_id_fc8346d0');
            $table->integer('active');
            $table->timestamps();

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
