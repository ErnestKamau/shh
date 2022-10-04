<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventorySubCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inventory_sub_categories', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name');
			$table->string('description');
			$table->string('image')->default('');
			$table->integer('inventory_category_id');
			$table->string('manufacturer')->nullable();
			$table->timestamps(10);
			$table->float('minimum_level', 10, 0)->nullable()->default(1);
			$table->string('unit_type', 100)->nullable();
			$table->float('unit_price', 10, 0)->nullable();
			$table->smallInteger('reporting_decimal_places')->nullable()->default(0);
			$table->string('code', 100)->nullable();
			$table->integer('company_id')->nullable();
			$table->integer('location_id')->nullable();
			$table->string('parent', 100)->nullable();
			$table->integer('parent_id')->nullable();
			$table->integer('material_type_id')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inventory_sub_categories');
	}

}
