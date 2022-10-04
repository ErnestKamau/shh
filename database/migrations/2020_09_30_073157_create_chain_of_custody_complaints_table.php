<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChainOfCustodyComplaintsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('chain_of_custody_complaints', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->timestamps(10);
			$table->integer('complaint_id');
			$table->string('action');
			$table->integer('action_taker_id');
			$table->string('comments')->nullable();
			$table->integer('workflow_stage');
			$table->dateTime('move_out_date')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('chain_of_custody_complaints');
	}

}
