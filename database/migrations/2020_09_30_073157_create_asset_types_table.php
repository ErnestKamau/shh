<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetTypesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('asset_types', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('asset_code');
			$table->string('descripton');
			$table->boolean('is_active')->default(0);
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
		Schema::drop('asset_types');
	}

}
