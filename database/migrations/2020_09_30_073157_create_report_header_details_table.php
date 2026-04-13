<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportHeaderDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('report_header_details', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('title');
			$table->string('to');
			$table->string('cc');
			$table->date('date');
			$table->string('ref');
			$table->string('re');
			$table->string('for');
			$table->integer('sample_header_id');
			$table->integer('specific_analyst_id');
			$table->integer('approved_by_id');
			$table->integer('verified_by_id');
			$table->string('model');
			$table->integer('model_id');
			$table->timestamps(6);
			$table->string('from')->nullable();
			$table->string('outgoing_email_body', 2048)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('report_header_details');
	}

}
