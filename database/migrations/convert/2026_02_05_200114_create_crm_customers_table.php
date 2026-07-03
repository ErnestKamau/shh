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
        if (Schema::hasTable('crm_customers')) {
            return;
        }
        Schema::create('crm_customers', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('code')->index('idx_crm_customers_code_976b39dd');
            $table->string('name')->index('idx_crm_customers_name_37be37bd');
            $table->string('postal_address')->nullable();
            $table->string('physical_address')->nullable();
            $table->string('fax')->nullable();
            $table->string('email');
            $table->string('telephone1');
            $table->string('telephone2')->nullable();
            $table->string('website')->nullable();
            $table->uuid('country_id')->index('idx_crm_customers_country_id_e60073b7');
            $table->uuid('company_id')->index('idx_crm_customers_company_id_19986e33');
            $table->boolean('active')->nullable()->default(true);
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
            $table->string('unit_configurable_name', 100)->nullable();
            $table->string('sub_unit_configurable_name')->nullable();
            $table->string('area_configurable_name')->nullable();
            $table->string('sample_point_configurable_name', 100)->nullable();
            $table->string('product_configurable_name', 100)->nullable();
            $table->integer('credit_days')->nullable();
            $table->integer('account_status')->nullable();
            $table->boolean('lpos_required')->default(false);
            $table->string('vat_no', 100)->nullable();
            $table->boolean('is_hidden')->nullable()->default(false);
            $table->string('zoho_id', 100)->nullable();
            $table->uuid('currency_id')->nullable()->index('idx_crm_customers_currency_id_31e8fa0a');
            $table->string('zoho_currency_id')->nullable();
            $table->uuid('zoho_customer_id')->nullable()->index('idx_crm_customers_zoho_customer_id_e7b28c42');
            $table->uuid('lab_id')->nullable()->index('idx_crm_customers_lab_id_acbf4651');
            $table->string('customer_type', 20)->default('external');

            $table->index(['company_id', 'active'], 'idx_crm_customers_company_id_active_901f123e');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_customers');
    }
};
