<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
        });

        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workflow_definition_id');
            $table->string('step_name');
            $table->string('step_code');
            $table->unsignedInteger('sequence');
            $table->string('role_name')->nullable();
            $table->boolean('is_final')->default(false);
            $table->timestamps();

            $table->foreign('workflow_definition_id')
                ->references('id')
                ->on('workflow_definitions')
                ->cascadeOnDelete();
            $table->unique(['workflow_definition_id', 'step_code']);
        });

        Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workflow_definition_id');
            $table->uuid('from_step_id');
            $table->uuid('to_step_id');
            $table->string('action_name');
            $table->timestamps();

            $table->foreign('workflow_definition_id')
                ->references('id')
                ->on('workflow_definitions')
                ->cascadeOnDelete();
            $table->foreign('from_step_id')
                ->references('id')
                ->on('workflow_steps')
                ->cascadeOnDelete();
            $table->foreign('to_step_id')
                ->references('id')
                ->on('workflow_steps')
                ->cascadeOnDelete();
        });

        Schema::create('registry_request_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->uuid('workflow_definition_id')->nullable();
            $table->string('default_priority')->default('normal');
            $table->json('metadata_schema')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workflow_definition_id')
                ->references('id')
                ->on('workflow_definitions')
                ->nullOnDelete();
        });

        Schema::create('registry_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference_no')->unique();
            $table->uuid('request_category_id');
            $table->uuid('workflow_definition_id')->nullable();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('entity_type')->nullable();
            $table->string('entity_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('priority')->default('normal');
            $table->string('direction')->default('incoming');
            $table->string('current_stage')->nullable();
            $table->string('status')->default('draft');
            $table->string('submitting_party')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->uuid('company_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('request_category_id')
                ->references('id')
                ->on('registry_request_categories')
                ->restrictOnDelete();
            $table->foreign('workflow_definition_id')
                ->references('id')
                ->on('workflow_definitions')
                ->nullOnDelete();
            $table->index(['company_id', 'status']);
            $table->index(['request_category_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('registry_request_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('registry_request_id');
            $table->string('action_type');
            $table->string('from_stage')->nullable();
            $table->string('to_stage')->nullable();
            $table->text('comment')->nullable();
            $table->json('payload')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->foreign('registry_request_id')
                ->references('id')
                ->on('registry_requests')
                ->cascadeOnDelete();
            $table->index(['registry_request_id', 'action_type']);
        });

        Schema::create('registry_request_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('registry_request_id');
            $table->unsignedBigInteger('assigned_to');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->string('role_context')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('assigned_at');
            $table->timestamp('unassigned_at')->nullable();
            $table->timestamps();

            $table->foreign('registry_request_id')
                ->references('id')
                ->on('registry_requests')
                ->cascadeOnDelete();
            $table->index(['registry_request_id', 'is_active']);
        });

        Schema::create('registry_request_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('registry_request_id');
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->foreign('registry_request_id')
                ->references('id')
                ->on('registry_requests')
                ->cascadeOnDelete();
        });

        Schema::create('registry_request_status_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('registry_request_id');
            $table->string('stage_code');
            $table->timestamp('entered_at');
            $table->timestamp('exited_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->timestamps();

            $table->foreign('registry_request_id')
                ->references('id')
                ->on('registry_requests')
                ->cascadeOnDelete();
            $table->index(['registry_request_id', 'stage_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registry_request_status_logs');
        Schema::dropIfExists('registry_request_documents');
        Schema::dropIfExists('registry_request_assignments');
        Schema::dropIfExists('registry_request_actions');
        Schema::dropIfExists('registry_requests');
        Schema::dropIfExists('registry_request_categories');
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_definitions');
    }
};
