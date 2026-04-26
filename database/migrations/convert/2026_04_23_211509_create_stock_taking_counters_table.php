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
        Schema::create('stock_taking_counters', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('stock_taking_id')->index('idx_stock_taking_counters_stock_taking_id_911c7042');
            $table->integer('counter_id');
            $table->integer('store_id')->nullable();
            $table->timestamps();
            $table->foreign(['stock_taking_id'], 'fk_stock_taking_counters_stock_taking_id_946b5769')->references(['id'])->on('stock_takings')->onUpdate('no action')->onDelete('cascade');

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
