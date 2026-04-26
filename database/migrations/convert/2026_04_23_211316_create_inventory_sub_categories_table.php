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
        Schema::create('inventory_sub_categories', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('description');
            $table->string('image')->default('');
            $table->uuid('inventory_category_id')->index('idx_inventory_sub_categories_inventory_category_id_1feafc37');
            $table->string('manufacturer')->nullable();
            $table->timestamps();
            $table->double('minimum_level')->nullable()->default(1);
            $table->string('unit_type', 100)->nullable();
            $table->string('secondary_unit_type', 100)->nullable();
            $table->double('unit_price')->nullable();
            $table->double('unit_price_credit')->nullable();
            $table->double('annual_consumption')->nullable();
            $table->integer('delivery_days')->default(7);
            $table->string('code', 100)->nullable();
            $table->uuid('company_id')->nullable()->index('idx_inventory_sub_categories_company_id_94d9ada1');
            $table->integer('location_id')->nullable();
            $table->string('parent', 100)->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('internal_lead_time')->default(5);
            $table->integer('external_lead_time')->default(10);
            $table->integer('material_type_id')->nullable()->default(0);
            $table->boolean('is_lab')->default(false);
            $table->integer('item_classification')->nullable();
            $table->string('sap_code', 100)->nullable();
            $table->integer('estimated_variation_in_demand_average_consumption')->nullable();
            $table->double('maximum_order_quantity')->default(100);
            $table->double('available_stock')->nullable();
            $table->double('reaorder_level')->nullable();
            $table->boolean('requires_reorder')->nullable();
            $table->integer('working_days')->default(365);
            $table->integer('sub_category_id')->nullable();
            $table->tinyInteger('active')->nullable()->default(1);
            $table->string('zoho_account_id')->nullable();
            $table->string('zoho_item_code')->nullable();
            $table->foreign(['company_id'], 'fk_inventory_sub_categories_company_id_e88e1013')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_category_id'], 'fk_inventory_sub_categories_inventory_category_id_9c931fea')->references(['id'])->on('inventory_categories')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_sub_categories');
    }
};
