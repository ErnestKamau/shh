<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_products', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name');
			$table->integer('crm_company_unit_id');
			$table->timestamps(10);
			$table->smallInteger('active')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('company_products');
	}

}
