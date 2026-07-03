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
        Schema::create('company_products', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->uuid('crm_company_unit_id')->index('idx_company_products_crm_company_unit_id_67ec760d');
            $table->timestamps();
            $table->smallInteger('active')->nullable()->default(0);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_products');
    }
};
