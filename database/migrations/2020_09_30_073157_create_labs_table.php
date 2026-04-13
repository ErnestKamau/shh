<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('labs', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('code');
			$table->string('name');
			$table->string('address');
			$table->string('location');
			$table->string('fax')->nullable();
			$table->string('email');
			$table->string('website')->nullable();
			$table->integer('company_id');
			$table->boolean('is_external')->default(0);
			$table->string('phone1');
			$table->string('phone2')->nullable();
			$table->string('phone3')->nullable();
			$table->boolean('active')->default(1);
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
		Schema::drop('labs');
	}

}
