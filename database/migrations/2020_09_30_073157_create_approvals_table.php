<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('approvals', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('title');
			$table->string('for');
			$table->string('stage');
			$table->integer('role_id');
			$table->integer('level');
			$table->timestamps(10);
			$table->integer('inventory_location_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('approvals');
	}

}
