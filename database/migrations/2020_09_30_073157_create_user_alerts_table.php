<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAlertsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_alerts', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('type');
			$table->string('title');
			$table->string('description');
			$table->string('url');
			$table->integer('user_id');
			$table->integer('created_id');
			$table->string('model');
			$table->integer('model_id');
			$table->string('status');
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
		Schema::drop('user_alerts');
	}

}
