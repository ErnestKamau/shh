<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('equipment', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('equipment_number');
			$table->text('description');
			$table->string('picture');
			$table->string('make');
			$table->string('model');
			$table->date('date_purchased');
			$table->integer('maintainance_days');
			$table->integer('maintainance_notification_in_days')->default(0);
			$table->integer('calibration_days');
			$table->integer('calibration_notification_in_days')->default(0);
			$table->integer('company_id');
			$table->integer('status_id')->nullable();
			$table->string('asset_code')->nullable();
			$table->string('barcode_number')->nullable();
			$table->string('manufacturer')->nullable();
			$table->string('condition')->nullable();
			$table->integer('assigned_employee_id')->nullable();
			$table->string('assigned_department')->nullable();
			$table->string('market_value')->nullable();
			$table->date('warranty_date')->nullable();
			$table->integer('inventory_item_id')->nullable();
			$table->timestamps(10);
			$table->string('asset_description')->nullable();
			$table->string('serial_number')->nullable();
			$table->string('status')->nullable();
			$table->integer('employee_dispose_id')->nullable();
			$table->date('dispose_date')->nullable();
			$table->text('comment')->nullable();
			$table->boolean('is_disposal')->default(0);
			$table->boolean('active')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('equipment');
	}

}
