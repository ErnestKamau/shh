<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalysisElementsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('analysis_elements', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('analyte_id');
			$table->integer('decimal_places')->nullable();
			$table->string('reporting_symbol')->nullable();
			$table->string('reporting_unit')->nullable();
			$table->boolean('non_detectable')->nullable();
			$table->boolean('non_accredited')->default(0);
			$table->boolean('active')->default(1);
			$table->integer('company_id');
			$table->integer('analysis_type_id');
			$table->boolean('show_on_report')->default(1);
			$table->timestamps(10);
			$table->integer('equipment_id')->nullable()->default(0);
			$table->integer('method')->nullable();
			$table->smallInteger('is_manual')->nullable()->default(0);
			$table->string('operator_id', 100)->nullable();
			$table->float('significant_figures', 10, 0)->nullable();
			$table->float('lod', 10, 0)->nullable();
			$table->float('hod', 10, 0)->nullable();
			$table->integer('level')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('analysis_elements');
	}

}
