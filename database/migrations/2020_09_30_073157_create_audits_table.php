<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('audits', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('user_type')->nullable();
			$table->bigInteger('user_id')->nullable();
			$table->string('event');
			$table->string('auditable_type');
			$table->bigInteger('auditable_id');
			$table->text('old_values')->nullable();
			$table->text('new_values')->nullable();
			$table->string('url')->nullable();
			$table->string('ip_address', 45)->nullable();
			$table->string('user_agent', 1023)->nullable();
			$table->string('tags')->nullable();
			$table->timestamps(6);
			$table->index(['user_id','user_type']);
			$table->index(['auditable_type','auditable_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('audits');
	}

}
