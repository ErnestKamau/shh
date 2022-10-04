<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inventory_categories', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name');
			$table->string('description');
			$table->string('image')->default('no-logo.png');
			$table->timestamps(10);
			$table->integer('company_id')->nullable();
			$table->integer('inventory_location_id')->nullable()->default(0);
			$table->string('category_type', 100)->default('normal');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inventory_categories');
	}

}
