<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkillsCapabilityMatrixRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skills_capability_matrix_role', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('capability_id');
            $table->integer('skill_matrix_role_id');
            $table->integer('role_id');
            $table->integer('user_id');
            $table->string('code');
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
        Schema::dropIfExists('skills_capability_matrix_role');
    }
}
