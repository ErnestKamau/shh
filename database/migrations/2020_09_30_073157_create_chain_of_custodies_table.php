<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChainOfCustodiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('chain_of_custodies', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('workflow_stage');
			$table->integer('tracking_stage_id');
			$table->integer('moved_in_by');
			$table->integer('moved_out_by')->nullable();
			$table->dateTime('moved_out_date')->nullable();
			$table->integer('sample_header_id');
			$table->string('comments', 1024)->nullable();
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
		Schema::drop('chain_of_custodies');
	}

}
