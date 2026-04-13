<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleHeadersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sample_headers', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('batch_code');
			$table->date('receipt_date')->nullable();
			$table->date('date_collected');
			$table->integer('crm_customer_id');
			$table->string('crm_unit_name');
			$table->integer('sample_type_id');
			$table->string('reference_number')->nullable();
			$table->string('status');
			$table->boolean('is_routine');
			$table->float('routine_frequency', 10, 0);
			$table->timestamps(6);
			$table->integer('is_amendment')->nullable();
			$table->smallInteger('schedule_sent')->nullable()->default(0);
			$table->integer('sample_tracking_stage')->nullable();
			$table->string('description', 1000)->nullable();
			$table->string('document_number', 100)->nullable();
			$table->string('importer_address')->nullable();
			$table->integer('receiving_officer')->nullable();
			$table->integer('sampling_officer')->nullable();
			$table->string('priority', 100)->default('Normal');
			$table->string('reason_for_submission', 512)->nullable();
			$table->string('how_sample_was_obtained', 512)->nullable();
			$table->integer('specialist_analyst_id')->nullable();
			$table->string('declared_commodity_code', 512)->nullable();
			$table->string('net_quantity_and_unit_of_quantity', 128)->nullable();
			$table->string('sample_appearance_description', 512)->nullable();
			$table->string('use_of_goods', 512)->nullable();
			$table->string('kra_office_ref')->nullable();
			$table->string('kra_office_station')->nullable();
			$table->string('where_sample_was_obtained')->nullable();
			$table->decimal('declared_amount', 18, 0)->nullable();
			$table->decimal('final_declared_amount', 18, 0)->nullable();
			$table->string('radio_active_levels')->nullable();
			$table->date('date_expected');
			$table->integer('invoice_id')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sample_headers');
	}

}
