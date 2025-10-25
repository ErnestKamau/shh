<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkillsMatrixDetailRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skills_matrix_detail_role', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('matrix_detail_id');
            $table->integer('role_id');
            $table->integer('matrix_role_id');
            $table->integer('proficiency_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('skills_matrix_detail_role');
    }
}
