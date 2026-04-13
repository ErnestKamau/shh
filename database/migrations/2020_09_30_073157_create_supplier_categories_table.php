<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('supplier_categories', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('supplier_id');
			$table->integer('inventory_sub_category_id');
			$table->timestamps(6);
			$table->string('supplier_image', 100)->default('/images/no-logo.png');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('supplier_categories');
	}

}
