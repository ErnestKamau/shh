<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerqualificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('customerqualifications', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->timestamps();
			$table->integer('qualification_id');
			$table->integer('customer_id');
			$table->dateTime('certification_date');
			$table->dateTime('expire_date');
			$table->string('certification_body');
			$table->string('certificate');
			$table->string('edited')->nullable();
			$table->boolean('status')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('customerqualifications');
	}

}
