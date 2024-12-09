<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkillsTrainingDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skills_training_detail', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('training_header_id');
            $table->integer('capability_detail_id');
            $table->integer('skill_matrix_role_proficiency_id');
            $table->boolean('require_training')->default(1);
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
        Schema::dropIfExists('skills_training_detail');
    }
}
