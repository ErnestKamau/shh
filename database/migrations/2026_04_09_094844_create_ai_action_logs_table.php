<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiActionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_action_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->uuid('action_id')->unique();
            $blueprint->unsignedBigInteger('user_id')->nullable();
            $blueprint->string('intent');
            $blueprint->json('entities')->nullable();
            $blueprint->enum('status', ['proposed', 'confirmed', 'executed', 'failed', 'cancelled'])->default('proposed');
            $blueprint->json('result_meta')->nullable();
            $blueprint->string('risk_level')->default('low');
            $blueprint->timestamps();

            $blueprint->index('action_id');
            $blueprint->index('user_id');
            $blueprint->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_action_logs');
    }
}
