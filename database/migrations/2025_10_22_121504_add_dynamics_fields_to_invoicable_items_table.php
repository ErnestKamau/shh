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
        Schema::table('invoicable_items', function (Blueprint $table) {
            // Inventory & Stock Information
            $table->integer('inventory_field')->nullable()->after('item_type')->comment('Current inventory level from Dynamics');
            $table->string('shelf_no')->nullable()->after('base_unit_of_measure');
            $table->string('costing_method')->nullable()->after('unit_cost')->comment('FIFO, LIFO, Average, etc.');
            
            // Cost Information
            $table->decimal('standard_cost', 15, 2)->default(0)->after('unit_cost');
            $table->decimal('last_direct_cost', 15, 2)->default(0)->after('standard_cost');
            $table->decimal('overhead_rate', 15, 4)->default(0)->after('last_direct_cost');
            $table->decimal('indirect_cost_percent', 15, 4)->default(0)->after('overhead_rate');
            
            // Pricing Information
            $table->string('price_profit_calculation')->nullable()->after('unit_price')->comment('Profit=Price-Cost, etc.');
            $table->decimal('profit_percent', 15, 4)->default(0)->after('price_profit_calculation');
            
            // Posting Groups
            $table->string('inventory_posting_group')->nullable()->after('item_category_code');
            $table->string('gen_prod_posting_group')->nullable()->after('inventory_posting_group');
            $table->string('vat_prod_posting_group')->nullable()->after('gen_prod_posting_group');
            $table->string('item_disc_group')->nullable()->after('vat_prod_posting_group');
            
            // Vendor Information
            $table->string('vendor_no')->nullable()->after('item_disc_group');
            $table->string('vendor_item_no')->nullable()->after('vendor_no');
            
            // Additional Item Details
            $table->string('tariff_no')->nullable()->after('vendor_item_no');
            $table->string('search_description')->nullable()->after('tariff_no');
            $table->date('last_date_modified')->nullable()->after('search_description');
            
            // Units of Measure
            $table->string('sales_unit_of_measure')->nullable()->after('base_unit_of_measure');
            $table->string('purch_unit_of_measure')->nullable()->after('sales_unit_of_measure');
            
            // Manufacturing & Replenishment
            $table->string('replenishment_system')->nullable()->after('purch_unit_of_measure')->comment('Purchase, Prod. Order, etc.');
            $table->string('manufacturing_policy')->nullable()->after('replenishment_system');
            $table->string('assembly_policy')->nullable()->after('manufacturing_policy');
            $table->string('flushing_method')->nullable()->after('assembly_policy');
            $table->string('item_tracking_code')->nullable()->after('flushing_method');
            $table->string('production_bom_no')->nullable()->after('item_tracking_code');
            $table->string('routing_no')->nullable()->after('production_bom_no');
            $table->string('lead_time_calculation')->nullable()->after('routing_no');
            
            // Boolean Flags
            $table->boolean('created_from_nonstock_item')->default(false)->after('item_type');
            $table->boolean('substitutes_exist')->default(false)->after('created_from_nonstock_item');
            $table->boolean('stockkeeping_unit_exists')->default(false)->after('substitutes_exist');
            $table->boolean('assembly_bom')->default(false)->after('stockkeeping_unit_exists');
            $table->boolean('cost_is_adjusted')->default(false)->after('costing_method');
            $table->boolean('coupled_to_crm')->default(false)->after('blocked');
            $table->boolean('coupled_to_dataverse')->default(false)->after('coupled_to_crm');
            
            // Deferral Template
            $table->string('default_deferral_template_code')->nullable()->after('item_tracking_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoicable_items', function (Blueprint $table) {
            $table->dropColumn([
                'inventory_field',
                'shelf_no',
                'costing_method',
                'standard_cost',
                'last_direct_cost',
                'overhead_rate',
                'indirect_cost_percent',
                'price_profit_calculation',
                'profit_percent',
                'inventory_posting_group',
                'gen_prod_posting_group',
                'vat_prod_posting_group',
                'item_disc_group',
                'vendor_no',
                'vendor_item_no',
                'tariff_no',
                'search_description',
                'last_date_modified',
                'sales_unit_of_measure',
                'purch_unit_of_measure',
                'replenishment_system',
                'manufacturing_policy',
                'assembly_policy',
                'flushing_method',
                'item_tracking_code',
                'production_bom_no',
                'routing_no',
                'lead_time_calculation',
                'created_from_nonstock_item',
                'substitutes_exist',
                'stockkeeping_unit_exists',
                'assembly_bom',
                'cost_is_adjusted',
                'coupled_to_crm',
                'coupled_to_dataverse',
                'default_deferral_template_code',
            ]);
        });
    }
};
