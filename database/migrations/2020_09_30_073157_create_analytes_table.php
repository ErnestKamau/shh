<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalytesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('analytes', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('code');
			$table->string('name');
			$table->integer('decimal_places');
			$table->float('equivalent_weight', 10, 0)->nullable();
			$table->string('reporting_symbol')->nullable();
			$table->string('reporting_unit')->nullable();
			$table->string('method');
			$table->string('common_name')->nullable()->default('-');
			$table->boolean('non_detectable');
			$table->boolean('non_accredited');
			$table->boolean('active');
			$table->integer('company_id');
			$table->boolean('show_on_report');
			$table->timestamps(6);
			$table->string('equipment_id')->nullable()->default('0');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('analytes');
	}

}
