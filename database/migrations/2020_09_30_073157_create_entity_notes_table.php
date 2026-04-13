<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntityNotesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('entity_notes', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('type');
			$table->string('description');
			$table->string('model');
			$table->integer('model_id');
			$table->integer('created_by');
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
		Schema::drop('entity_notes');
	}

}
