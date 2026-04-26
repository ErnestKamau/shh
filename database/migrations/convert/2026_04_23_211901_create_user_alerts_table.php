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
        Schema::create('user_alerts', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('type');
            $table->string('title');
            $table->string('description');
            $table->string('url');
            $table->uuid('user_id')->index('idx_user_alerts_user_id_5a65c59e');
            $table->integer('created_id');
            $table->string('model');
            $table->integer('model_id');
            $table->string('status');
            $table->timestamps();
            $table->foreign(['user_id'], 'fk_user_alerts_user_id_edc83019')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_alerts');
    }
};
