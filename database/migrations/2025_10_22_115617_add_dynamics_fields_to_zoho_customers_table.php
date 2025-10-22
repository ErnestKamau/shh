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
        Schema::table('zoho_customers', function (Blueprint $table) {
            // Dynamics 365 specific fields
            $table->string('customer_no')->nullable()->after('id')->index()->comment('Dynamics Customer No');
            $table->string('name_2')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('contact')->nullable();
            $table->string('responsibility_center')->nullable();
            $table->string('location_code')->nullable();
            $table->string('post_code')->nullable();
            $table->string('country_region_code')->nullable();
            $table->string('ic_partner_code')->nullable();
            $table->string('salesperson_code')->nullable();
            $table->string('customer_posting_group')->nullable();
            $table->boolean('allow_multiple_posting_groups')->default(false);
            $table->string('gen_bus_posting_group')->nullable();
            $table->string('vat_bus_posting_group')->nullable();
            $table->string('customer_price_group')->nullable();
            $table->string('customer_disc_group')->nullable();
            $table->string('payment_terms_code')->nullable();
            $table->string('reminder_terms_code')->nullable();
            $table->string('fin_charge_terms_code')->nullable();
            $table->string('language_code')->nullable();
            $table->string('search_name')->nullable();
            $table->decimal('credit_limit_lcy', 15, 2)->nullable();
            $table->string('blocked')->nullable();
            $table->boolean('privacy_blocked')->default(false);
            $table->date('last_date_modified')->nullable();
            $table->string('application_method')->nullable();
            $table->boolean('combine_shipments')->default(false);
            $table->string('reserve')->nullable();
            $table->string('ship_to_code')->nullable();
            $table->string('shipping_advice')->nullable();
            $table->string('shipping_agent_code')->nullable();
            $table->string('base_calendar_code')->nullable();
            $table->decimal('balance_lcy', 15, 2)->nullable();
            $table->decimal('balance_due_lcy', 15, 2)->nullable();
            $table->decimal('sales_lcy', 15, 2)->nullable();
            $table->decimal('payments_lcy', 15, 2)->nullable();
            $table->boolean('coupled_to_crm')->default(false);
            $table->boolean('coupled_to_dataverse')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zoho_customers', function (Blueprint $table) {
            $table->dropColumn([
                'customer_no',
                'name_2',
                'phone_no',
                'contact',
                'responsibility_center',
                'location_code',
                'post_code',
                'country_region_code',
                'ic_partner_code',
                'salesperson_code',
                'customer_posting_group',
                'allow_multiple_posting_groups',
                'gen_bus_posting_group',
                'vat_bus_posting_group',
                'customer_price_group',
                'customer_disc_group',
                'payment_terms_code',
                'reminder_terms_code',
                'fin_charge_terms_code',
                'language_code',
                'search_name',
                'credit_limit_lcy',
                'blocked',
                'privacy_blocked',
                'last_date_modified',
                'application_method',
                'combine_shipments',
                'reserve',
                'ship_to_code',
                'shipping_advice',
                'shipping_agent_code',
                'base_calendar_code',
                'balance_lcy',
                'balance_due_lcy',
                'sales_lcy',
                'payments_lcy',
                'coupled_to_crm',
                'coupled_to_dataverse',
            ]);
        });
    }
};
