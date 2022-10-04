<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('conversation', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('from_user_id')->nullable();
			$table->integer('to_user_id')->nullable();
			$table->timestamps(10);
			$table->integer('company_id')->nullable();
			$table->smallInteger('active')->default(1);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('conversation');
	}

}
