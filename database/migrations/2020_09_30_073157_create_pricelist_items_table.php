<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricelistItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pricelist_items', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('pricelist_id');
			$table->integer('analysis_id');
			$table->integer('sample_type_id');
			$table->float('cost_price', 10, 0);
			$table->float('selling_price', 10, 0);
			$table->float('changed_price', 10, 0);
			$table->boolean('vat');
			$table->boolean('internal_use');
			$table->boolean('external_view');
			$table->boolean('active');
			$table->timestamps(6);
			$table->integer('level')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('pricelist_items');
	}

}
