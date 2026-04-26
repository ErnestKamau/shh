<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('skills_capability_matrix_role', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('capability_id');
            $table->integer('skill_matrix_role_id');
            $table->uuid('role_id')->index('idx_skills_capability_matrix_role_role_id_f6fe2a1c');
            $table->uuid('user_id')->index('idx_skills_capability_matrix_role_user_id_ed8d5612');
            $table->string('code');
            $table->dateTime('deleted_at')->nullable();
            $table->foreign(['role_id'], 'fk_skills_capability_matrix_role_role_id_eac5517b')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_skills_capability_matrix_role_user_id_6df6741f')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skills_capability_matrix_role');
    }
};
