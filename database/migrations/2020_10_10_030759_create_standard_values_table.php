<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandardValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('standard_values', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('code');
            $table->string('name');
            $table->boolean('status')->default(0);
            $table->integer('edited_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('standard_values');
    }
}
