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
        Schema::create('invoicable_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->string('item_type')->nullable();
            $table->boolean('created_from_nonstock_item')->default(false);
            $table->boolean('substitutes_exist')->default(false);
            $table->boolean('stockkeeping_unit_exists')->default(false);
            $table->boolean('assembly_bom')->default(false);
            $table->integer('inventory_field')->nullable()->comment('Current inventory level from Dynamics');
            $table->string('item_category_code')->nullable();
            $table->string('inventory_posting_group')->nullable();
            $table->string('gen_prod_posting_group')->nullable();
            $table->string('vat_prod_posting_group')->nullable();
            $table->string('item_disc_group')->nullable();
            $table->string('vendor_no')->nullable();
            $table->string('vendor_item_no')->nullable();
            $table->string('tariff_no')->nullable();
            $table->string('search_description')->nullable();
            $table->date('last_date_modified')->nullable();
            $table->decimal('unit_price', 15)->default(0);
            $table->string('price_profit_calculation')->nullable()->comment('Profit=Price-Cost, etc.');
            $table->decimal('profit_percent', 15, 4)->default(0);
            $table->decimal('unit_cost', 15)->default(0);
            $table->decimal('standard_cost', 15)->default(0);
            $table->decimal('last_direct_cost', 15)->default(0);
            $table->decimal('overhead_rate', 15, 4)->default(0);
            $table->decimal('indirect_cost_percent', 15, 4)->default(0);
            $table->string('costing_method')->nullable()->comment('FIFO, LIFO, Average, etc.');
            $table->boolean('cost_is_adjusted')->default(false);
            $table->uuid('currency_id')->nullable()->index('idx_invoicable_items_currency_id_f734edb9');
            $table->boolean('price_includes_tax')->default(false);
            $table->string('tax_group_code')->nullable();
            $table->string('base_unit_of_measure')->nullable();
            $table->string('sales_unit_of_measure')->nullable();
            $table->string('purch_unit_of_measure')->nullable();
            $table->string('replenishment_system')->nullable()->comment('Purchase, Prod. Order, etc.');
            $table->string('manufacturing_policy')->nullable();
            $table->string('assembly_policy')->nullable();
            $table->string('flushing_method')->nullable();
            $table->string('item_tracking_code')->nullable();
            $table->string('default_deferral_template_code')->nullable();
            $table->string('production_bom_no')->nullable();
            $table->string('routing_no')->nullable();
            $table->string('lead_time_calculation')->nullable();
            $table->string('shelf_no')->nullable();
            $table->string('gtin')->nullable();
            $table->boolean('blocked')->default(false);
            $table->boolean('coupled_to_crm')->default(false);
            $table->boolean('coupled_to_dataverse')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoicable_items');
    }
};
