<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('suppliers', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name');
			$table->string('logo');
			$table->string('email')->unique();
			$table->string('phone')->unique();
			$table->string('building');
			$table->string('street');
			$table->string('town');
			$table->string('address');
			$table->timestamps(6);
			$table->integer('company_id')->nullable();
			$table->smallInteger('active')->default(1);
			$table->integer('inventory_location_id')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('suppliers');
	}

}
