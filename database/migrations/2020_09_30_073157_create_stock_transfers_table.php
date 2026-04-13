<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTransfersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('stock_transfers', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('description');
			$table->string('created_by');
			$table->integer('location_id');
			$table->integer('department_id');
			$table->timestamps(6);
			$table->string('code')->nullable();
			$table->integer('inventory_location_id')->default(0);
			$table->string('status', 100)->default('Pending');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('stock_transfers');
	}

}
