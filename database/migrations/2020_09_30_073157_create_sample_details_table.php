<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sample_details', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('sample_code');
			$table->string('analysis_type_id');
			$table->string('sample_condition_id');
			$table->string('barcode')->nullable();
			$table->string('comments')->nullable();
			$table->string('gps')->nullable();
			$table->string('photo_url', 512)->nullable();
			$table->timestamps(6);
			$table->bigInteger('sample_header_id');
			$table->integer('sample_point_id')->nullable();
			$table->integer('company_product_id')->nullable();
			$table->string('main_body', 4000)->nullable();
			$table->string('header_body', 4000)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sample_details');
	}

}
