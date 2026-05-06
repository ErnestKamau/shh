<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('stage_name');
            $table->string('code')->unique();
            $table->string('name');
            $table->integer('order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['stage_name', 'code']);
            $table->index(['stage_name', 'is_active']);
        });

        Schema::create('workflow_checklist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('approval_id');
            $table->string('label');
            $table->enum('type', ['checkbox', 'text', 'select'])->default('text');
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();
            $table->integer('order')->default(1);
            $table->timestamps();

            $table->foreign('approval_id')
                ->references('id')
                ->on('workflow_approvals')
                ->cascadeOnDelete();

            $table->index(['approval_id', 'order']);
        });

        Schema::create('workflow_checklist_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sample_id');
            $table->uuid('approval_id');
            $table->uuid('checklist_item_id');
            $table->json('value')->nullable();
            $table->uuid('user_id')->nullable();
            $table->timestamps();

            $table->foreign('sample_id')
                ->references('id')
                ->on('sample_headers')
                ->cascadeOnDelete();
            $table->foreign('approval_id')
                ->references('id')
                ->on('workflow_approvals')
                ->cascadeOnDelete();
            $table->foreign('checklist_item_id')
                ->references('id')
                ->on('workflow_checklist_items')
                ->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique(['sample_id', 'approval_id', 'checklist_item_id'], 'workflow_checklist_responses_unique');
            $table->index('sample_id');
            $table->index('approval_id');
            $table->index('checklist_item_id');
            $table->index('user_id');
        });

        Schema::create('workflow_approval_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sample_id');
            $table->uuid('approval_id');
            $table->string('stage_name');
            $table->enum('status', ['approved', 'rejected']);
            $table->text('remarks')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at');
            $table->timestamps();

            $table->foreign('sample_id')
                ->references('id')
                ->on('sample_headers')
                ->cascadeOnDelete();
            $table->foreign('approval_id')
                ->references('id')
                ->on('workflow_approvals')
                ->cascadeOnDelete();
            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique(['sample_id', 'approval_id'], 'workflow_approval_logs_unique');
            $table->index(['sample_id', 'stage_name']);
            $table->index('approval_id');
            $table->index('approved_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_approval_logs');
        Schema::dropIfExists('workflow_checklist_responses');
        Schema::dropIfExists('workflow_checklist_items');
        Schema::dropIfExists('workflow_approvals');
    }
};