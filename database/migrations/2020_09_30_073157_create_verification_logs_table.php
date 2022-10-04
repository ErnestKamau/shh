<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerificationLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('verification_logs', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->date('verification_date');
			$table->text('procedure');
			$table->string('reference_standard');
			$table->text('response');
			$table->text('remarks');
			$table->integer('operator_id');
			$table->integer('edit_by')->nullable();
			$table->boolean('is_delete')->default(0);
			$table->integer('equipment_id')->nullable();
			$table->timestamps(10);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('verification_logs');
	}

}
