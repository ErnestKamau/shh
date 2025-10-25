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
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->string('item_type')->nullable();
            $table->string('item_category_code')->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->integer('currency_id')->nullable();
            $table->boolean('price_includes_tax')->default(false);
            $table->string('tax_group_code')->nullable();
            $table->string('base_unit_of_measure')->nullable();
            $table->string('gtin')->nullable();
            $table->boolean('blocked')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
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
