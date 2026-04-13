<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inventory_items', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('inventory_category_id');
			$table->integer('inventory_sub_category_id');
			$table->float('stock_in', 10, 0)->default(0);
			$table->float('stock_out', 10, 0)->default(0);
			$table->integer('created_by');
			$table->integer('supplier_id')->nullable();
			$table->integer('inventory_department_id')->default(0);
			$table->integer('edited_by')->default(0);
			$table->string('status')->default('in_inventory');
			$table->timestamps(6);
			$table->integer('test_score')->nullable();
			$table->integer('inventory_location_id')->nullable()->default(0);
			$table->string('batch_code')->nullable();
			$table->date('expiry')->nullable()->default('2099-12-31');
			$table->float('price', 10, 0)->nullable();
			$table->integer('received_by')->nullable();
			$table->string('previous_batch_code', 100)->nullable();
			$table->string('po_number', 100)->nullable();
			$table->string('barcode', 100)->nullable();
			$table->integer('inventory_store_id')->nullable();
			$table->integer('inventory_store_slot_id')->nullable();
			$table->string('storage_state_id', 100)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inventory_items');
	}

}
