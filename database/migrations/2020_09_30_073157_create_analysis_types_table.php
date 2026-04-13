<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalysisTypesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('analysis_types', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('code');
			$table->string('name');
			$table->string('description')->nullable();
			$table->integer('sample_type_id');
			$table->integer('lab_id');
			$table->integer('company_id');
			$table->boolean('active')->default(1);
			$table->timestamps(6);
			$table->string('short_name', 100)->nullable()->default('n/a');
			$table->integer('reporting_time')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('analysis_types');
	}

}
