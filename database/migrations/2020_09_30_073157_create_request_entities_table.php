<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestEntitiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_entities', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('priority')->default('Normal');
			$table->string('currency');
			$table->string('request_code');
			$table->string('status');
			$table->date('due_date')->nullable();
			$table->string('parent_request')->nullable();
			$table->integer('parent_request_id')->nullable();
			$table->string('request_type');
			$table->integer('approval_count')->default(0);
			$table->integer('required_approvals')->default(0);
			$table->integer('created_by')->default(0);
			$table->string('description', 1024)->nullable();
			$table->timestamps(10);
			$table->decimal('net_value', 18, 0)->nullable();
			$table->dateTime('submission_deadline')->nullable();
			$table->integer('supplier_id')->nullable();
			$table->integer('request_initiator')->nullable();
			$table->integer('inventory_location_id')->nullable();
			$table->integer('parent_material_requisition')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('request_entities');
	}

}
