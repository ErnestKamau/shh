<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryDepartmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inventory_departments', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name');
			$table->timestamps(10);
			$table->integer('company_id')->nullable()->default(0);
			$table->string('module', 100)->nullable();
			$table->integer('active')->nullable()->default(1);
			$table->integer('location_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inventory_departments');
	}

}
