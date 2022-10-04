<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalysisGuidesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('analysis_guides', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('guide_name');
			$table->integer('analyte_id');
			$table->integer('analysis_type_id');
			$table->float('value', 10, 0);
			$table->string('comments', 1024)->nullable();
			$table->string('recommendations', 1024)->nullable();
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
		Schema::drop('analysis_guides');
	}

}
