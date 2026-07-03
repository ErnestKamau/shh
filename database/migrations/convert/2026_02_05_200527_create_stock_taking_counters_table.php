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
        if (Schema::hasTable('stock_taking_counters')) {
            return;
        }
        Schema::create('stock_taking_counters', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('stock_taking_id')->index('idx_stock_taking_counters_stock_taking_id_7c9a4856');
            $table->integer('counter_id');
            $table->integer('store_id')->nullable();
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_taking_counters');
    }
};
