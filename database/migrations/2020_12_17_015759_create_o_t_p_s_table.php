<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOTPSTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('o_t_p_s', function (Blueprint $table) {
			$table->id();
			$table->string('code');
			$table->integer('user_id');
			$table->string('model');
			$table->integer('model_id');
			$table->integer('approved_by')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('o_t_p_s');
	}
}
