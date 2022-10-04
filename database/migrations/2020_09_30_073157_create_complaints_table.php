<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplaintsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('complaints', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->timestamps(10);
			$table->string('complaint_id');
			$table->text('description');
			$table->string('priority');
			$table->string('received_from');
			$table->string('registered_by');
			$table->integer('complaint_workflow');
			$table->boolean('is_closed')->default(0);
			$table->string('edited_by')->nullable();
			$table->boolean('rejected')->default(0);
			$table->string('type')->nullable();
			$table->dateTime('date')->nullable();
			$table->integer('reject_workflow')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('complaints');
	}

}
