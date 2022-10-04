<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('results', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('captured_result_id');
			$table->string('sample_detail_code');
			$table->integer('sample_detail_id');
			$table->integer('sample_header_id');
			$table->integer('analyte_id');
			$table->string('analyte_code');
			$table->float('result', 10, 0)->nullable();
			$table->decimal('guide', 8, 6)->nullable();
			$table->string('comments')->nullable();
			$table->boolean('recheck')->default(0);
			$table->decimal('guide_low', 8, 6)->nullable();
			$table->decimal('guide_high', 8, 6)->nullable();
			$table->string('unit_code')->nullable();
			$table->integer('status_code')->nullable();
			$table->string('reporting_symbol')->nullable();
			$table->boolean('qc')->nullable();
			$table->decimal('correct_target', 8, 6)->nullable();
			$table->decimal('standard_target', 8, 6)->nullable();
			$table->string('recommendations')->nullable();
			$table->decimal('initial_result', 8, 6)->nullable();
			$table->string('initial_reporting_symbol')->nullable();
			$table->decimal('very_low_guide', 8, 6)->nullable();
			$table->decimal('very_high_guide', 8, 6)->nullable();
			$table->timestamps(10);
			$table->integer('analysis_type_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('results');
	}

}
