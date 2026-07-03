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
        if (Schema::hasTable('currency_conversions')) {
            return;
        }
        Schema::create('currency_conversions', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('currency_1');
            $table->integer('currency_2');
            $table->double('ratio');
            $table->timestamps();
            $table->uuid('inventory_location_id')->nullable()->index('idx_currency_conversions_inventory_location_id_14bcb001');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_conversions');
    }
};
