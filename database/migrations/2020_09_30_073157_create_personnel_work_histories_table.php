<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonnelWorkHistoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('personnel_work_histories', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('department_id');
			$table->integer('job_id');
			$table->integer('user_id');
			$table->date('end_date')->nullable();
			$table->timestamps(6);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('personnel_work_histories');
	}

}
