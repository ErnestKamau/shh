<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplaintsresolutionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('complaintsresolutions', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->timestamps();
			$table->string('car_no');
			$table->text('action');
			$table->string('officer_responsible');
			$table->string('registered_by');
			$table->boolean('reject')->default(0);
			$table->boolean('approve')->default(0);
			$table->string('approved_by')->nullable();
			$table->integer('complaint_id');
			$table->integer('workflow_stage');
			$table->string('edited_by')->nullable();
			$table->string('request_approve')->default('0');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('complaintsresolutions');
	}

}
