<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCapturedResultsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('captured_results', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('sample_detail_code');
			$table->integer('sample_detail_id');
			$table->integer('sample_header_id');
			$table->integer('analyte_id');
			$table->string('analyte_code');
			$table->integer('equipment_id')->default(0);
			$table->float('result', 10, 0)->nullable();
			$table->integer('user_id');
			$table->timestamps(6);
			$table->integer('analysis_type_id')->nullable();
			$table->integer('operator_id')->nullable();
			$table->integer('method_id')->nullable();
			$table->dateTime('machine_update_date')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('captured_results');
	}

}
