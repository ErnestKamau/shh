<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierQuoteAttachmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('supplier_quote_attachments', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('supplier_id');
			$table->integer('registered_by');
			$table->integer('request_id');
			$table->integer('quotation_id');
			$table->integer('request_item_id');
			$table->text('description');
			$table->string('attachment');
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
		Schema::drop('supplier_quote_attachments');
	}

}
