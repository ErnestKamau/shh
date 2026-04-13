<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleAnalysisStagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sample_analysis_stages', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name');
			$table->boolean('active');
			$table->timestamps(6);
			$table->integer('company_id')->nullable();
			$table->string('sample_workflow', 100)->nullable();
			$table->integer('level')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sample_analysis_stages');
	}

}
