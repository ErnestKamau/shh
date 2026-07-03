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
            $table->uuid('user_id')->index('idx_user_alerts_user_id_aaca5f19');
            $table->integer('created_id');
            $table->string('model');
            $table->integer('model_id');
            $table->string('status');
            $table->timestamps();

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
