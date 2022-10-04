<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTakingCountersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_taking_counters', function (Blueprint $table) {
					$table->id();
					$table->integer('stock_taking_id');
					$table->integer('counter_id');
					$table->integer('store_id')->nullable();
					$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_taking_counters');
    }
}
