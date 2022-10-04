<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNamingConvensionConsensusesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('naming_convension_consensuses', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('string_part');
			$table->string('integer_part');
			$table->string('model');
			$table->timestamps(10);
			$table->integer('company_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('naming_convension_consensuses');
	}

}
