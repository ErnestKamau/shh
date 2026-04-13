<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalysisMethodElementsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('analysis_method_elements', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('analysis_method_id');
			$table->integer('analyte_id');
			$table->float('quantity', 10, 0);
			$table->boolean('active');
			$table->timestamps(6);
			$table->integer('company_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('analysis_method_elements');
	}

}
