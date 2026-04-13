<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmCustomersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('crm_customers', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('code');
			$table->string('name');
			$table->string('postal_address')->nullable();
			$table->string('physical_address')->nullable();
			$table->string('fax')->nullable();
			$table->string('email');
			$table->string('telephone1');
			$table->string('telephone2');
			$table->string('website');
			$table->integer('country_id');
			$table->integer('company_id');
			$table->boolean('active');
			$table->timestamps();
			$table->string('unit_configurable_name', 100)->nullable();
			$table->string('sample_point_configurable_name', 100)->nullable();
			$table->string('product_configurable_name', 100)->nullable();
			$table->integer('credit_days')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('crm_customers');
	}

}
