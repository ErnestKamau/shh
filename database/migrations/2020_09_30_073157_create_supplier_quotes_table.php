<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierQuotesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('supplier_quotes', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('supplier_id');
			$table->integer('request_id');
			$table->integer('request_item_id');
			$table->float('quote_amount', 10, 0);
			$table->timestamps(6);
			$table->dateTime('awarded_at')->nullable();
			$table->boolean('is_awarded')->default(0);
			$table->integer('registered_by')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('supplier_quotes');
	}

}
