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
        Schema::create('ai_analytics_logs', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('session_id')->nullable()->index('idx_ai_analytics_logs_session_id_031fff09');
            $table->uuid('user_id')->nullable()->index('idx_ai_analytics_logs_user_id_861f19c3');
            $table->string('intent')->nullable()->index('idx_ai_analytics_logs_intent_396c8b9d');
            $table->string('model_used')->index('idx_ai_analytics_logs_model_used_7456c029');
            $table->integer('latency_ms')->default(0);
            $table->integer('tokens_used')->default(0);
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->double('confidence')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->foreign(['user_id'], 'fk_ai_analytics_logs_user_id_2b7ea81e')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_analytics_logs');
    }
};
