<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonelCertificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('personel_certifications', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->timestamps(10);
			$table->integer('personnel_id');
			$table->integer('role_certification_id');
			$table->boolean('status')->default(0);
			$table->string('certificate');
			$table->string('certificate_body');
			$table->dateTime('certificate_date');
			$table->dateTime('expire_date');
			$table->string('edited_by')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('personel_certifications');
	}

}
