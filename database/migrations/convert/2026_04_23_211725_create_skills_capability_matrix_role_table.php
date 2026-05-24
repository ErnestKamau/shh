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
            $table->uuid('capability_id');
            $table->integer('skill_matrix_role_id');
            $table->uuid('role_id')->index('idx_skills_capability_matrix_role_role_id_2056a6f3');
            $table->uuid('user_id')->index('idx_skills_capability_matrix_role_user_id_2711b587');
            $table->string('code');
            $table->dateTime('deleted_at')->nullable();

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
