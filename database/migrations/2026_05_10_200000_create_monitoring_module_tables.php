<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('document_control_number')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->date('effective_date')->nullable();
            $table->date('review_date')->nullable();
            $table->string('department')->nullable();
            $table->string('monitoring_category', 32)->default('environmental');
            $table->json('approval_workflow')->nullable();
            $table->string('status', 32)->default('draft');
            $table->boolean('is_active')->default(true);
            $table->uuid('lab_id')->nullable()->index();
            $table->uuid('parent_template_id')->nullable()->index();
            $table->uuid('company_id')->nullable()->index();
            $table->uuid('created_by')->nullable()->index();
            $table->uuid('updated_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('lab_id')->references('id')->on('labs')->nullOnDelete();
            $table->index(['monitoring_category', 'is_active'], 'monitoring_templates_category_active_idx');
        });

        Schema::create('monitoring_formula_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id')->nullable()->index();
            $table->string('name');
            $table->string('output_key')->nullable();
            $table->text('expression');
            $table->text('pass_condition_expression')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('monitoring_templates')->cascadeOnDelete();
        });

        Schema::create('monitoring_template_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id')->index();
            $table->uuid('formula_rule_id')->nullable()->index();
            $table->string('field_key');
            $table->string('label');
            $table->string('field_type', 32)->default('text');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_readonly')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('field_config')->nullable();
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('monitoring_templates')->cascadeOnDelete();
            $table->foreign('formula_rule_id')->references('id')->on('monitoring_formula_rules')->nullOnDelete();
            $table->unique(['template_id', 'field_key']);
        });

        Schema::create('monitoring_variables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('variable_type', 32)->default('constant');
            $table->json('value')->nullable();
            $table->json('query_builder')->nullable();
            $table->json('allowed_tables')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('monitoring_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id')->index();
            $table->unsignedInteger('template_version')->default(1);
            $table->uuid('lab_id')->nullable()->index();
            $table->uuid('equipment_id')->nullable()->index();
            $table->date('log_date')->index();
            $table->string('monitoring_scope', 32)->default('environmental');
            $table->string('status', 32)->default('pending');
            $table->string('overall_result', 32)->nullable();
            $table->boolean('deviation_triggered')->default(false);
            $table->json('payload')->nullable();
            $table->uuid('executed_by')->nullable()->index();
            $table->timestamp('executed_at')->nullable();
            $table->uuid('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->json('signature_payload')->nullable();
            $table->uuid('company_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('monitoring_templates')->restrictOnDelete();
            $table->foreign('lab_id')->references('id')->on('labs')->nullOnDelete();
            $table->foreign('equipment_id')->references('id')->on('equipment')->nullOnDelete();
            $table->index(['monitoring_scope', 'status', 'log_date'], 'monitoring_logs_scope_status_date_idx');
        });

        Schema::create('monitoring_log_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('log_id')->index();
            $table->uuid('template_field_id')->nullable()->index();
            $table->string('field_key');
            $table->string('field_label');
            $table->text('raw_value')->nullable();
            $table->text('computed_value')->nullable();
            $table->string('status', 32)->nullable();
            $table->boolean('pass')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('log_id')->references('id')->on('monitoring_logs')->cascadeOnDelete();
            $table->foreign('template_field_id')->references('id')->on('monitoring_template_fields')->nullOnDelete();
        });

        Schema::create('monitoring_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('log_id')->index();
            $table->unsignedInteger('workflow_stage')->default(1);
            $table->uuid('approver_id')->nullable()->index();
            $table->string('status', 32)->default('pending');
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_reason')->nullable();
            $table->boolean('password_confirmed')->default(false);
            $table->json('signature_payload')->nullable();
            $table->timestamps();

            $table->foreign('log_id')->references('id')->on('monitoring_logs')->cascadeOnDelete();
        });

        Schema::create('monitoring_calibration_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('log_id')->index();
            $table->uuid('equipment_id')->index();
            $table->uuid('calibration_log_id')->nullable()->index();
            $table->decimal('correction_factor', 12, 6)->nullable();
            $table->decimal('uncertainty_of_measure', 12, 6)->nullable();
            $table->date('calibration_date')->nullable();
            $table->string('calibration_certificate')->nullable();
            $table->string('standard_used')->nullable();
            $table->json('snapshot_payload')->nullable();
            $table->timestamps();

            $table->foreign('log_id')->references('id')->on('monitoring_logs')->cascadeOnDelete();
            $table->foreign('equipment_id')->references('id')->on('equipment')->cascadeOnDelete();
            $table->foreign('calibration_log_id')->references('id')->on('maintainance_calibration_logs')->nullOnDelete();
        });

        Schema::create('monitoring_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('log_id')->index();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->uuid('uploaded_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('log_id')->references('id')->on('monitoring_logs')->cascadeOnDelete();
        });

        Schema::create('monitoring_audit_trails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('auditable_type');
            $table->uuid('auditable_id')->index();
            $table->string('action', 32);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->uuid('user_id')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id'], 'monitoring_audit_type_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_audit_trails');
        Schema::dropIfExists('monitoring_attachments');
        Schema::dropIfExists('monitoring_calibration_snapshots');
        Schema::dropIfExists('monitoring_approvals');
        Schema::dropIfExists('monitoring_log_entries');
        Schema::dropIfExists('monitoring_logs');
        Schema::dropIfExists('monitoring_variables');
        Schema::dropIfExists('monitoring_template_fields');
        Schema::dropIfExists('monitoring_formula_rules');
        Schema::dropIfExists('monitoring_templates');
    }
};
