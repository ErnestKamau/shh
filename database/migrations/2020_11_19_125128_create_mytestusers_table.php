<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMytestusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mytestusers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('gender');
            $table->string('country');
            $table->string('age');
            $table->date('date');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mytestusers');
    }
}
