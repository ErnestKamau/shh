<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerInvoiceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('customer_invoice', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->timestamps(10);
			$table->float('total', 10, 0)->default(0);
			$table->integer('sent_by')->default(0);
			$table->string('invoice_number')->default('INV-0000')->unique();
			$table->integer('pricelist_id');
			$table->string('reference_number')->nullable();
			$table->string('upload_url')->nullable();
			$table->integer('currency_id')->default(0);
			$table->date('due_date')->nullable();
			$table->integer('customer_id')->default(0);
			$table->float('total_tax', 10, 0)->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('customer_invoice');
	}

}
