<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkillsMatrixDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skills_matrix_detail', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('skill_matrix_id');
            $table->integer('competency_area_id');
            $table->integer('competency_type_id');
            $table->integer('competency_description_id');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('skills_matrix_detail');
    }
}
