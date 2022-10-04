<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntityAttachmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('entity_attachments', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('title');
			$table->string('type');
			$table->string('file');
			$table->string('description');
			$table->string('model');
			$table->integer('model_id');
			$table->integer('created_by');
			$table->timestamps(10);
			$table->string('mime', 100)->nullable();
			$table->string('size', 100)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('entity_attachments');
	}

}
