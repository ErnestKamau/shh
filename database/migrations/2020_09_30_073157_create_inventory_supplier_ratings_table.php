<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventorySupplierRatingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inventory_supplier_ratings', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('supplier_id');
			$table->integer('inventory_item_id');
			$table->integer('rating');
			$table->string('title');
			$table->string('comments');
			$table->integer('rating_by');
			$table->timestamps(6);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inventory_supplier_ratings');
	}

}
