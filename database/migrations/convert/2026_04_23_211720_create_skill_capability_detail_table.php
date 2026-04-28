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
        Schema::create('skill_capability_detail', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('capability_id');
            $table->integer('competency_id');
            $table->uuid('user_id')->index('idx_skill_capability_detail_user_id_c7bdcea1');
            $table->integer('proficiency_id');
            $table->dateTime('deleted_at')->nullable();
            $table->integer('skill_matrix_role_id')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_capability_detail');
    }
};
