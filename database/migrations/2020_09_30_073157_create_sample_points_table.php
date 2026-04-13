<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSamplePointsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sample_points', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name');
			$table->integer('crm_company_unit_id');
			$table->timestamps();
			$table->smallInteger('active')->nullable();
			$table->string('gps', 512);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sample_points');
	}

}
