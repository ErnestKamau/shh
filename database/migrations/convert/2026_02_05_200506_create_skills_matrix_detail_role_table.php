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
        Schema::create('skills_matrix_detail_role', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('matrix_detail_id');
            $table->uuid('role_id')->index('idx_skills_matrix_detail_role_role_id_a2aa0496');
            $table->integer('matrix_role_id');
            $table->integer('proficiency_id');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skills_matrix_detail_role');
    }
};
