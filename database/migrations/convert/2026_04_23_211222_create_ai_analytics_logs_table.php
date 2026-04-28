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
            $table->string('session_id')->nullable()->index('idx_ai_analytics_logs_session_id_831f447c');
            $table->uuid('user_id')->nullable()->index('idx_ai_analytics_logs_user_id_8534cddc');
            $table->string('intent')->nullable()->index('idx_ai_analytics_logs_intent_af164dfd');
            $table->string('model_used')->index('idx_ai_analytics_logs_model_used_33b0ad19');
            $table->integer('latency_ms')->default(0);
            $table->integer('tokens_used')->default(0);
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->double('confidence')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

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
