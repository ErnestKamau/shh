<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('companies', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name');
			$table->string('logo')->default('/images/no-logo.png');
			$table->string('location');
			$table->string('address');
			$table->integer('country_id');
			$table->string('website');
			$table->timestamps(6);
			$table->string('license_key', 512)->nullable();
			$table->date('license_expiry')->nullable();
			$table->boolean('active')->default(0);
			$table->boolean('show_on_reports')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('companies');
	}

}
