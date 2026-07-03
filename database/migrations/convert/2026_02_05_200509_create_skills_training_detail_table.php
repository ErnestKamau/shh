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
        if (Schema::hasTable('skills_training_detail')) {
            return;
        }
        Schema::create('skills_training_detail', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('training_header_id');
            $table->integer('capability_detail_id');
            $table->integer('skill_matrix_role_proficiency_id');
            $table->boolean('require_training')->default(true);
            $table->dateTime('deleted_at')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skills_training_detail');
    }
};
