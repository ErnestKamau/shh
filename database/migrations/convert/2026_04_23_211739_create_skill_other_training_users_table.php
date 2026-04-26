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
        Schema::create('skill_other_training_users', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('train_plan_detail_id');
            $table->uuid('user_id')->index('idx_skill_other_training_users_user_id_1c6e004f');
            $table->foreign(['user_id'], 'fk_skill_other_training_users_user_id_465cabfe')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_other_training_users');
    }
};
