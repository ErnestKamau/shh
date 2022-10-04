<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintainanceCalibrationLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('maintainance_calibration_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('equipment_id');
			$table->string('service_provider');
			$table->text('notes');
			$table->string('type');
			$table->date('date');
			$table->string('certificate')->default('no-document');
			$table->integer('overseen_by');
			$table->integer('edit_by');
			$table->integer('maintainance_notification_in_days')->nullable();
			$table->integer('calibration_notification_in_days')->nullable();
			$table->date('replacement_date')->nullable();
			$table->string('reference_number')->nullable();
			$table->string('maintenance_type')->nullable();
			$table->integer('operator_id')->nullable();
			$table->integer('supplier_id')->nullable();
			$table->timestamps(10);
			$table->integer('employee_id')->nullable();
			$table->string('maintainance_type')->default('Not assigned');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('maintainance_calibration_logs');
	}

}
