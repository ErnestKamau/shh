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
            $table->char('action_id', 36)->index('idx_ai_action_logs_action_id_36b666b2');
            $table->uuid('user_id')->nullable()->index('idx_ai_action_logs_user_id_c081aec0');
            $table->string('intent');
            $table->json('entities')->nullable();
            $table->enum('status', ['proposed', 'confirmed', 'executed', 'failed', 'cancelled'])->default('proposed')->index('idx_ai_action_logs_proposed_e621a7da');
            $table->timestamp('expires_at')->nullable()->index('idx_ai_action_logs_expires_at_34ddb419');
            $table->json('result_meta')->nullable();
            $table->string('risk_level')->default('low');
            $table->timestamps();

            $table->unique(['action_id']);
            $table->foreign(['user_id'], 'fk_ai_action_logs_user_id_e475dafb')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');

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
