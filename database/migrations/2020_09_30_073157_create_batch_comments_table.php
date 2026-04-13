<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('batch_comments', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('comments');
			$table->integer('created_by');
			$table->integer('reminder_for');
			$table->string('personnel_to_cc');
			$table->date('completed_at')->nullable();
			$table->timestamps(6);
			$table->integer('sample_header_id')->nullable();
			$table->string('comment_type')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('batch_comments');
	}

}
