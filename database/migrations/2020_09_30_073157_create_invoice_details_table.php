<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('invoice_details', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->timestamps(6);
			$table->integer('sample_header_id');
			$table->integer('sample_detail_id');
			$table->integer('invoice_id');
			$table->integer('cost_price');
			$table->integer('selling_price');
			$table->float('tax_amount', 10, 0)->default(0);
			$table->string('tax_rate')->default('0');
			$table->float('selling_amount', 10, 0);
			$table->float('total', 10, 0);
			$table->integer('quantity')->default(1);
			$table->string('analysis_type');
			$table->integer('crm_customer_id')->default(0);
			$table->string('analysis_type_name')->nullable();
			$table->float('selling_price_amount', 10, 0)->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('invoice_details');
	}

}
