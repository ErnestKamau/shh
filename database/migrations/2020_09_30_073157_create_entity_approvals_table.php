<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntityApprovalsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('entity_approvals', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('approval_id');
			$table->string('model');
			$table->integer('model_id');
			$table->integer('user_id')->nullable();
			$table->dateTime('approved_at')->nullable();
			$table->string('status')->default('Pending');
			$table->timestamps(10);
			$table->string('description', 1024)->nullable();
			$table->integer('inventory_location_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('entity_approvals');
	}

}
