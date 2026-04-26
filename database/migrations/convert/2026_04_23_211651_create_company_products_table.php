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
            $table->uuid('crm_company_unit_id')->index('idx_company_products_crm_company_unit_id_ae319a87');
            $table->timestamps();
            $table->smallInteger('active')->nullable()->default(0);
            $table->foreign(['crm_company_unit_id'], 'fk_company_products_crm_company_unit_id_34fa1d2f')->references(['id'])->on('crm_company_units')->onUpdate('no action')->onDelete('cascade');

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
