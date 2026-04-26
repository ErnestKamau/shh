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
        Schema::create('crm_customers', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('code')->index('code');
            $table->string('name')->index('idx_crm_customers_name_a7f4f548');
            $table->string('postal_address')->nullable();
            $table->string('physical_address')->nullable();
            $table->string('fax')->nullable();
            $table->string('email');
            $table->string('telephone1');
            $table->string('telephone2')->nullable();
            $table->string('website')->nullable();
            $table->uuid('country_id')->index('idx_crm_customers_country_id_0e02874b');
            $table->uuid('company_id')->index('company_id');
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
            $table->uuid('currency_id')->nullable()->index('idx_crm_customers_currency_id_55af0da6');
            $table->string('zoho_currency_id')->nullable();
            $table->uuid('zoho_customer_id')->nullable()->index('idx_crm_customers_zoho_customer_id_76c1bda9');
            $table->uuid('lab_id')->nullable()->index('idx_crm_customers_lab_id_cfe78dc3');
            $table->string('customer_type', 20)->default('external');

            $table->index(['company_id', 'active'], 'idx_crm_customers_company_id_active_23ad0612');
            $table->foreign(['company_id'], 'fk_crm_customers_company_id_6e16447d')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['country_id'], 'fk_crm_customers_country_id_2e574f72')->references(['id'])->on('countries')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['currency_id'], 'fk_crm_customers_currency_id_83bed6d4')->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['lab_id'], 'fk_crm_customers_lab_id_7bbf6b5a')->references(['id'])->on('labs')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['zoho_customer_id'], 'fk_crm_customers_zoho_customer_id_bbf97fa2')->references(['id'])->on('zoho_customers')->onUpdate('no action')->onDelete('set null');





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
