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
        if (Schema::hasTable('skills_matrix_detail')) {
            return;
        }
        Schema::create('skills_matrix_detail', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('skill_matrix_id');
            $table->integer('competency_area_id');
            $table->integer('competency_type_id');
            $table->integer('competency_description_id');
            $table->dateTime('deleted_at')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skills_matrix_detail');
    }
};
