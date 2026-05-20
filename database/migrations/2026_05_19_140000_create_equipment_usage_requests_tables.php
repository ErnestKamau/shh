<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_usage_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id');
            $table->uuid('requester_id');
            $table->string('status', 32)->default('pending');
            $table->text('request_comment')->nullable();
            $table->dateTime('proposed_start_at');
            $table->dateTime('proposed_end_at');
            $table->dateTime('approved_start_at')->nullable();
            $table->dateTime('approved_end_at')->nullable();
            $table->text('approval_comment')->nullable();
            $table->uuid('helping_analyst_id')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->uuid('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->uuid('zone_id')->nullable();
            $table->timestamps();

            $table->index('equipment_id');
            $table->index('requester_id');
            $table->index('status');
            $table->index('zone_id');
            $table->foreign('equipment_id')->references('id')->on('equipment')->cascadeOnDelete();
            $table->foreign('requester_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('helping_analyst_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('rejected_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('equipment_usage_request_samples', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_usage_request_id');
            $table->uuid('sample_detail_id');
            $table->timestamps();

            $table->unique(['equipment_usage_request_id', 'sample_detail_id'], 'eur_samples_request_sample_unique');
            $table->foreign('equipment_usage_request_id', 'eur_samples_request_fk')
                ->references('id')->on('equipment_usage_requests')->cascadeOnDelete();
        });

        Schema::create('lab_user_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('notifiable_type');
            $table->uuid('notifiable_id');
            $table->string('notification_type', 64);
            $table->string('title');
            $table->text('message');
            $table->json('metadata')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_user_notifications');
        Schema::dropIfExists('equipment_usage_request_samples');
        Schema::dropIfExists('equipment_usage_requests');
    }
};
