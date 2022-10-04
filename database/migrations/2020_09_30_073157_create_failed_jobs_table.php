<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFailedJobsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('failed_jobs', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('connection');
			$table->string('queue');
			$table->string('payload');
			$table->string('exception');
			$table->timestamp('failed_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('failed_jobs');
	}

}
