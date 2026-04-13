<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMethodReagentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('method_reagents', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('inventory_sub_category_id');
			$table->integer('method_id');
			$table->string('reporting_unit');
			$table->float('quantity', 10, 0);
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
		Schema::drop('method_reagents');
	}

}
