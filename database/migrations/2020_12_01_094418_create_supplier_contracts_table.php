<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierContractsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('supplier_contracts', function (Blueprint $table) {
			$table->id();
			$table->string('description')->default("No Description");
			$table->integer('supplier_id');
			$table->string('items');
			$table->date('start');
			$table->date('end');
			$table->string('file');
			$table->boolean('status')->default(1);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('supplier_contracts');
	}
}
