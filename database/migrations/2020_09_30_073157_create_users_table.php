<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name');
			$table->string('email')->unique();
			$table->dateTime('email_verified_at')->nullable();
			$table->string('password')->nullable();
			$table->integer('company_id')->default(0);
			$table->string('remember_token', 100)->nullable();
			$table->timestamps(10);
			$table->integer('active')->nullable()->default(1);
			$table->integer('location_id')->default(0);
			$table->integer('department_id')->nullable();
			$table->string('photo')->nullable();
			$table->integer('electronic signature')->nullable();
			$table->integer('position')->nullable();
			$table->string('education_level', 100)->nullable();
			$table->date('date_of_birth')->nullable();
			$table->date('employment_date')->nullable();
			$table->string('id_number', 100)->nullable();
			$table->string('nssf', 100)->nullable();
			$table->string('nhif', 100)->nullable();
			$table->string('kra_pin', 100)->nullable();
			$table->string('first_name', 100)->nullable();
			$table->string('middle_name', 100)->nullable();
			$table->string('last_name', 100)->nullable();
			$table->string('electronic_sig')->nullable();
			$table->integer('designation')->nullable();
			$table->string('verify_code')->nullable();
			$table->dateTime('verify_code_expires')->nullable();
			$table->boolean('is_client')->default(0);
			$table->string('client_id')->nullable();
			$table->string('license_type', 100)->default('shared_user');
			$table->integer('supplier_id')->default(0);
			$table->boolean('is_online')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
