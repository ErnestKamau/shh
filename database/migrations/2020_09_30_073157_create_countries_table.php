<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('countries', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name');
			$table->string('iso_code_2', 2);
			$table->string('iso_code_3', 3);
			$table->string('address_format');
			$table->smallInteger('postcode_required');
			$table->smallInteger('status')->default(1);
			$table->timestamps(10);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('countries');
	}

}
