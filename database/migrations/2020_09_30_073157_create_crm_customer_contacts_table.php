<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmCustomerContactsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('crm_customer_contacts', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('first_name');
			$table->string('middle_name');
			$table->string('last_name');
			$table->string('job_occupation');
			$table->string('unit_name');
			$table->string('email');
			$table->string('telephone');
			$table->string('mobile');
			$table->boolean('receive_price_list');
			$table->boolean('receive_invoice');
			$table->boolean('receive_report');
			$table->integer('company_id');
			$table->integer('crm_customer_id');
			$table->boolean('active');
			$table->timestamps(10);
			$table->boolean('can_login')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('crm_customer_contacts');
	}

}
