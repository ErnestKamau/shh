<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentUsageTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('equipment_usage', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('operator');
			$table->integer('sample_header');
			$table->dateTime('end_date')->nullable();
			$table->timestamps(6);
			$table->integer('equipment_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('equipment_usage');
	}

}
