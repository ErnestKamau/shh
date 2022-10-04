<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleToSampleAnalysisStagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sample_to_sample_analysis_stages', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('sample_type_id');
			$table->integer('sample_analysis_stage_id');
			$table->timestamps(10);
			$table->smallInteger('active')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sample_to_sample_analysis_stages');
	}

}
