<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalysisMethodsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('analysis_methods', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name');
			$table->string('code');
			$table->integer('company_id');
			$table->string('description')->nullable();
			$table->boolean('active');
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
		Schema::drop('analysis_methods');
	}

}
