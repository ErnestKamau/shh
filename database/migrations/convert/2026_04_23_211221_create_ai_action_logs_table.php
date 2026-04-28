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
        Schema::create('ai_action_logs', function (Blueprint $table) {
            $table->uuid('id');
            $table->char('action_id', 36)->index('idx_ai_action_logs_action_id_2405034f');
            $table->uuid('user_id')->nullable()->index('idx_ai_action_logs_user_id_9ed47e94');
            $table->string('intent');
            $table->json('entities')->nullable();
            $table->enum('status', ['proposed', 'confirmed', 'executed', 'failed', 'cancelled'])->default('proposed')->index('idx_ai_action_logs_proposed_0fa998bb');
            $table->timestamp('expires_at')->nullable()->index('idx_ai_action_logs_expires_at_c6cc0933');
            $table->json('result_meta')->nullable();
            $table->string('risk_level')->default('low');
            $table->timestamps();

            $table->unique(['action_id']);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_action_logs');
    }
};
