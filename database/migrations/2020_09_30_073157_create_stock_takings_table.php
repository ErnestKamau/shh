<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTakingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('stock_takings', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('description');
			$table->integer('created_by');
			$table->integer('updated_by')->nullable();
			$table->dateTime('completed_at')->nullable();
			$table->string('status')->default('In Preparation');
			$table->string('stores');
			$table->string('store_names');
			$table->integer('approved_by')->nullable();
			$table->integer('inventory_location_id')->nullable()->default(0);
			$table->timestamps(6);
			$table->string('code', 100)->nullable();
			$table->smallInteger('stores_frozen')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('stock_takings');
	}

}
