<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandardsAnalytesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('standards_analytes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('analyte_id');
            $table->integer('standard_id');
            $table->integer('standard_value_id');
            $table->string('standard_value_type');
            $table->string('low')->nullable();
            $table->string('high')->nullable();
            $table->string('standard_is_value')->nullable();
            $table->text('comment')->nullable();
            $table->text('reccomendation')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('standards_analytes');
    }
}
