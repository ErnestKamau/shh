<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierRFQSTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('supplier_r_f_q_s', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('supplier_id');
			$table->integer('request_id');
			$table->boolean('rfq_sent')->default(0);
			$table->boolean('quote_received')->default(0);
			$table->timestamps(6);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('supplier_r_f_q_s');
	}

}
