<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricelistsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pricelists', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('code');
			$table->string('description', 1024);
			$table->integer('currency_id');
			$table->boolean('is_master')->default(0);
			$table->boolean('active')->default(0);
			$table->string('document_no');
			$table->string('revision_number');
			$table->timestamps(10);
			$table->string('status', 100)->default('no-changes');
			$table->date('valid_till')->nullable();
			$table->string('pricelist_file')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('pricelists');
	}

}
