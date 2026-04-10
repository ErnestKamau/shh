<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ISO 17025 Compliant Audit & CAPA Module
     */
    public function up(): void
    {
        // Drop existing tables first (in reverse order of dependencies)
        $this->down();

        // ============================================
        // CONFIGURATION/MASTER DATA TABLES
        // ============================================
        
        // Audit Types (Internal, External, Supplier, Accreditation)
        Schema::create('audit_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Audit Statuses (Scheduled, In Progress, Findings Review, etc.)
        Schema::create('audit_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Finding Categories (Conformity, Nonconformity, Observation, OFI)
        Schema::create('finding_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('severity')->nullable();
            $table->boolean('requires_capa')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Finding Statuses
        Schema::create('finding_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Risk Levels (Low, Medium, High)
        Schema::create('risk_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->integer('severity_score')->default(1);
            $table->string('color_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // NC Origins (Audit, Test Process, Customer Complaint, etc.)
        Schema::create('nc_origins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // NC Statuses
        Schema::create('nc_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Root Cause Methods (5 Whys, Fishbone, Human Error, etc.)
        Schema::create('root_cause_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->text('template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // RCA Statuses
        Schema::create('rca_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // CAPA Categories
        Schema::create('capa_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('action_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // CAPA Action Types
        Schema::create('capa_action_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // CAPA Statuses
        Schema::create('capa_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // CAPA Priorities
        Schema::create('capa_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('priority_level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Verification Effectiveness Results
        Schema::create('verification_results', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->boolean('requires_reopen')->default(false);
            $table->integer('next_workflow_step')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Severity Scales (for risk assessment)
        Schema::create('severity_scales', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('score')->default(1);
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Likelihood Scales (for risk assessment)
        Schema::create('likelihood_scales', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('score')->default(1);
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Verification Closure Statuses
        Schema::create('verification_closure_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Attachment Types
        Schema::create('attachment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Team Member Roles
        Schema::create('audit_team_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Notification Types
        Schema::create('audit_notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // ============================================
        // AUDIT CHECKLISTS
        // ============================================

        Schema::create('audit_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('audit_type_id')->nullable();
            $table->string('iso_standard')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('audit_type_id')->references('id')->on('audit_types')->onDelete('set null');
        });

        Schema::create('audit_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audit_checklist_id');
            $table->string('item_number');
            $table->string('iso_clause')->nullable();
            $table->text('requirement');
            $table->text('guidance')->nullable();
            $table->text('evidence_required')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('audit_checklist_id')->references('id')->on('audit_checklists')->onDelete('cascade');
        });

        // ============================================
        // MAIN DATA TABLES
        // ============================================

        // Main Audits Table (ISO 17025 Clause 8.8)
        // Named 'iso_audits' to avoid conflict with OwenIt\Auditing package's 'audits' table
        Schema::create('iso_audits', function (Blueprint $table) {
            $table->id();
            $table->string('audit_number')->unique();
            $table->string('revision_number')->default('REV. 00');
            $table->unsignedBigInteger('audit_type_id')->nullable();
            $table->string('audit_type_name')->nullable();
            
            $table->string('title');
            $table->text('objective')->nullable();
            $table->text('scope')->nullable();
            $table->text('criteria')->nullable();
            $table->unsignedBigInteger('checklist_id')->nullable();
            
            $table->string('lead_auditor_name')->nullable();
            $table->unsignedBigInteger('lead_auditor_id')->nullable();
            $table->text('audit_team')->nullable();
            $table->string('auditee_name')->nullable();
            $table->unsignedBigInteger('auditee_department_id')->nullable();
            $table->string('auditee_department_name')->nullable();
            
            $table->date('scheduled_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('report_date')->nullable();
            $table->date('closure_date')->nullable();
            
            $table->unsignedBigInteger('status_id')->nullable();
            $table->string('status_name')->nullable();
            
            $table->text('executive_summary')->nullable();
            $table->text('conclusions')->nullable();
            $table->text('recommendations')->nullable();
            
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('audit_type_id')->references('id')->on('audit_types')->onDelete('set null');
            $table->foreign('checklist_id')->references('id')->on('audit_checklists')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('audit_statuses')->onDelete('set null');
            
            $table->index(['status_id', 'company_id']);
            $table->index(['scheduled_date', 'start_date']);
            $table->index('audit_number');
        });

        // Audit Team Members
        Schema::create('audit_team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audit_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('role_name')->nullable();
            $table->text('responsibilities')->nullable();
            $table->timestamps();

            $table->foreign('audit_id')->references('id')->on('iso_audits')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('audit_team_roles')->onDelete('set null');
            $table->unique(['audit_id', 'user_id']);
        });

        // Audit Checklists Pivot Table (Many-to-Many)
        Schema::create('audit_checklist_audit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audit_id');
            $table->unsignedBigInteger('audit_checklist_id');
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->foreign('audit_id')->references('id')->on('iso_audits')->onDelete('cascade');
            $table->foreign('audit_checklist_id')->references('id')->on('audit_checklists')->onDelete('cascade');
            $table->unique(['audit_id', 'audit_checklist_id']);
        });

        // Audit Findings (ISO 17025 Clause 8.8)
        Schema::create('audit_module_findings', function (Blueprint $table) {
            $table->id();
            $table->string('finding_number')->unique();
            $table->unsignedBigInteger('audit_id');
            $table->unsignedBigInteger('finding_category_id')->nullable();
            $table->string('finding_category_name')->nullable();
            
            $table->string('iso_clause')->nullable();
            $table->string('sop_reference')->nullable();
            $table->text('requirement')->nullable();
            $table->text('observation')->nullable();
            $table->text('objective_evidence')->nullable();
            
            $table->unsignedBigInteger('risk_level_id')->nullable();
            $table->string('risk_level_name')->nullable();
            
            $table->string('responsible_person')->nullable();
            $table->unsignedBigInteger('responsible_user_id')->nullable();
            $table->date('response_due_date')->nullable();
            
            $table->unsignedBigInteger('status_id')->nullable();
            $table->string('status_name')->nullable();
            
            $table->integer('order_index')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('audit_id')->references('id')->on('iso_audits')->onDelete('cascade');
            $table->foreign('finding_category_id')->references('id')->on('finding_categories')->onDelete('set null');
            $table->foreign('risk_level_id')->references('id')->on('risk_levels')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('finding_statuses')->onDelete('set null');
            
            $table->index(['audit_id', 'status_id']);
            $table->index('finding_number');
        });

        // Non-Conformances (ISO 17025 Clause 7.10)
        Schema::create('non_conformances', function (Blueprint $table) {
            $table->id();
            $table->string('nc_number')->unique();
            
            $table->unsignedBigInteger('origin_id')->nullable();
            $table->string('origin_name')->nullable();
            $table->unsignedBigInteger('audit_id')->nullable();
            $table->unsignedBigInteger('audit_finding_id')->nullable();
            
            $table->unsignedBigInteger('sample_id')->nullable();
            $table->string('sample_reference')->nullable();
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->string('equipment_reference')->nullable();
            $table->unsignedBigInteger('method_id')->nullable();
            $table->string('method_reference')->nullable();
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->string('personnel_reference')->nullable();
            $table->string('sop_reference')->nullable();
            
            $table->string('title');
            $table->text('description');
            $table->string('iso_clause_violated')->nullable();
            $table->date('date_identified');
            $table->string('identified_by')->nullable();
            $table->unsignedBigInteger('identified_by_user_id')->nullable();
            $table->string('department')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            
            $table->unsignedBigInteger('risk_level_id')->nullable();
            $table->string('risk_level_name')->nullable();
            $table->integer('severity_score')->nullable();
            $table->integer('likelihood_score')->nullable();
            $table->text('risk_assessment_notes')->nullable();
            
            $table->text('immediate_correction')->nullable();
            $table->date('immediate_correction_date')->nullable();
            $table->string('immediate_correction_by')->nullable();
            
            $table->unsignedBigInteger('status_id')->nullable();
            $table->string('status_name')->nullable();
            $table->date('target_closure_date')->nullable();
            $table->date('actual_closure_date')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->text('closure_notes')->nullable();
            
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('audit_id')->references('id')->on('iso_audits')->onDelete('set null');
            $table->foreign('audit_finding_id')->references('id')->on('audit_module_findings')->onDelete('set null');
            $table->foreign('origin_id')->references('id')->on('nc_origins')->onDelete('set null');
            $table->foreign('risk_level_id')->references('id')->on('risk_levels')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('nc_statuses')->onDelete('set null');
            
            $table->index(['status_id', 'company_id']);
            $table->index('nc_number');
            $table->index('date_identified');
        });

        // Root Cause Analysis (ISO 17025 Clause 8.7)
        Schema::create('root_cause_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('non_conformance_id');
            $table->unsignedBigInteger('root_cause_method_id')->nullable();
            $table->string('method_name')->nullable();
            
            $table->text('analysis_data')->nullable();
            $table->text('root_cause_description');
            $table->text('contributing_factors')->nullable();
            $table->text('evidence_supporting_rca')->nullable();
            
            $table->unsignedBigInteger('status_id')->nullable();
            $table->string('status_name')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->date('approved_date')->nullable();
            $table->text('approval_comments')->nullable();
            
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('non_conformance_id')->references('id')->on('non_conformances')->onDelete('cascade');
            $table->foreign('root_cause_method_id')->references('id')->on('root_cause_methods')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('rca_statuses')->onDelete('set null');
        });

        // Corrective Actions (ISO 17025 Clause 8.7)
        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->string('capa_number')->unique();
            $table->unsignedBigInteger('non_conformance_id');
            $table->unsignedBigInteger('capa_category_id')->nullable();
            $table->string('capa_category_name')->nullable();
            
            $table->unsignedBigInteger('action_type_id')->nullable();
            $table->string('action_type_name')->nullable();
            
            $table->string('title');
            $table->text('description');
            $table->text('expected_outcome')->nullable();
            
            $table->string('action_owner')->nullable();
            $table->unsignedBigInteger('action_owner_id')->nullable();
            $table->string('department')->nullable();
            
            $table->date('due_date');
            $table->date('extended_due_date')->nullable();
            $table->text('extension_reason')->nullable();
            $table->date('implementation_date')->nullable();
            
            $table->unsignedBigInteger('status_id')->nullable();
            $table->string('status_name')->nullable();
            $table->unsignedBigInteger('priority_id')->nullable();
            $table->string('priority_name')->nullable();
            
            $table->text('implementation_notes')->nullable();
            $table->text('implementation_evidence')->nullable();
            
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('non_conformance_id')->references('id')->on('non_conformances')->onDelete('cascade');
            $table->foreign('capa_category_id')->references('id')->on('capa_categories')->onDelete('set null');
            $table->foreign('action_type_id')->references('id')->on('capa_action_types')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('capa_statuses')->onDelete('set null');
            $table->foreign('priority_id')->references('id')->on('capa_priorities')->onDelete('set null');
            
            $table->index(['status_id', 'company_id']);
            $table->index('capa_number');
            $table->index('due_date');
        });

        // Verification Records (ISO 17025 Clause 8.7)
        Schema::create('verification_records', function (Blueprint $table) {
            $table->id();
            $table->string('verification_number')->unique();
            $table->unsignedBigInteger('corrective_action_id');
            
            $table->date('verification_date');
            $table->string('verified_by')->nullable();
            $table->unsignedBigInteger('verified_by_user_id')->nullable();
            
            $table->unsignedBigInteger('effectiveness_result_id')->nullable();
            $table->string('effectiveness_result_name')->nullable();
            $table->text('verification_method')->nullable();
            $table->text('evidence_reviewed')->nullable();
            $table->text('comments')->nullable();
            
            $table->boolean('requires_reopen')->default(false);
            $table->text('reopen_reason')->nullable();
            
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_notes')->nullable();
            
            $table->unsignedBigInteger('closure_status_id')->nullable();
            $table->string('closure_status_name')->nullable();
            $table->date('closure_date')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('corrective_action_id')->references('id')->on('corrective_actions')->onDelete('cascade');
            $table->foreign('effectiveness_result_id')->references('id')->on('verification_results')->onDelete('set null');
            $table->foreign('closure_status_id')->references('id')->on('verification_closure_statuses')->onDelete('set null');
            
            $table->index('verification_number');
        });

        // Audit Attachments (Polymorphic)
        Schema::create('audit_attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('original_name')->nullable();
            
            $table->unsignedBigInteger('attachment_type_id')->nullable();
            $table->string('attachment_type_name')->nullable();
            $table->text('description')->nullable();
            
            $table->unsignedBigInteger('uploaded_by');
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('attachment_type_id')->references('id')->on('attachment_types')->onDelete('set null');
        });

        // Audit Notifications
        Schema::create('audit_notifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('notifiable');
            
            $table->unsignedBigInteger('notification_type_id')->nullable();
            $table->string('notification_type_name')->nullable();
            $table->string('title');
            $table->text('message')->nullable();
            
            $table->unsignedBigInteger('recipient_user_id');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_email_sent')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            
            $table->integer('company_id')->default(0);
            $table->timestamps();

            $table->foreign('notification_type_id')->references('id')->on('audit_notification_types')->onDelete('set null');
            $table->index(['recipient_user_id', 'is_read']);
        });

        // Seed default configuration data
        $this->seedDefaultData();
    }

    /**
     * Seed default configuration data
     */
    private function seedDefaultData(): void
    {
        // Audit Types
        \DB::table('audit_types')->insert([
            ['name' => 'Internal Audit', 'code' => 'INT', 'description' => 'Internal quality system audit', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'External Audit', 'code' => 'EXT', 'description' => 'External audit by certification body', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Supplier Audit', 'code' => 'SUP', 'description' => 'Supplier/vendor audit', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Accreditation Audit', 'code' => 'ACC', 'description' => 'Accreditation body audit (ISO 17025)', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Customer Audit', 'code' => 'CUS', 'description' => 'Customer/client audit', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Regulatory Audit', 'code' => 'REG', 'description' => 'Regulatory compliance audit', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Audit Statuses
        \DB::table('audit_statuses')->insert([
            ['name' => 'Scheduled', 'code' => 'SCHED', 'description' => 'Audit is planned/scheduled', 'color_code' => '#17a2b8', 'order_index' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'In Progress', 'code' => 'INPROG', 'description' => 'Audit is being conducted', 'color_code' => '#ffc107', 'order_index' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Findings Review', 'code' => 'REVIEW', 'description' => 'Audit findings under review', 'color_code' => '#fd7e14', 'order_index' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pending Closure', 'code' => 'PENDING', 'description' => 'Awaiting closure approval', 'color_code' => '#6f42c1', 'order_index' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Closed', 'code' => 'CLOSED', 'description' => 'Audit completed and closed', 'color_code' => '#28a745', 'order_index' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cancelled', 'code' => 'CANCEL', 'description' => 'Audit cancelled', 'color_code' => '#6c757d', 'order_index' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Finding Categories
        \DB::table('finding_categories')->insert([
            ['name' => 'Conformity', 'code' => 'CONF', 'description' => 'Requirement is fully met', 'severity' => null, 'requires_capa' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Minor Nonconformity', 'code' => 'NC-MIN', 'description' => 'Single lapse in meeting a requirement', 'severity' => 'Minor', 'requires_capa' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Major Nonconformity', 'code' => 'NC-MAJ', 'description' => 'Absence or total breakdown of a system', 'severity' => 'Major', 'requires_capa' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Critical Nonconformity', 'code' => 'NC-CRT', 'description' => 'Immediate risk to product/service quality', 'severity' => 'Critical', 'requires_capa' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Observation', 'code' => 'OBS', 'description' => 'Area of concern that could become NC', 'severity' => null, 'requires_capa' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Opportunity for Improvement', 'code' => 'OFI', 'description' => 'Suggestion for improvement', 'severity' => null, 'requires_capa' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Finding Statuses
        \DB::table('finding_statuses')->insert([
            ['name' => 'Open', 'code' => 'OPEN', 'description' => 'Finding is open', 'color_code' => '#dc3545', 'order_index' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Response Submitted', 'code' => 'RESP', 'description' => 'Response has been submitted', 'color_code' => '#ffc107', 'order_index' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Under Review', 'code' => 'REVIEW', 'description' => 'Response under review', 'color_code' => '#17a2b8', 'order_index' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Accepted', 'code' => 'ACCEPT', 'description' => 'Response accepted', 'color_code' => '#28a745', 'order_index' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Closed', 'code' => 'CLOSED', 'description' => 'Finding closed', 'color_code' => '#6c757d', 'order_index' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Risk Levels
        \DB::table('risk_levels')->insert([
            ['name' => 'Low', 'code' => 'LOW', 'description' => 'Minimal impact on quality or operations', 'severity_score' => 1, 'color_code' => '#28a745', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Medium', 'code' => 'MED', 'description' => 'Moderate impact requiring attention', 'severity_score' => 2, 'color_code' => '#ffc107', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'High', 'code' => 'HIGH', 'description' => 'Significant impact requiring immediate action', 'severity_score' => 3, 'color_code' => '#dc3545', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Critical', 'code' => 'CRIT', 'description' => 'Severe impact with potential for major consequences', 'severity_score' => 4, 'color_code' => '#6f42c1', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // NC Origins
        \DB::table('nc_origins')->insert([
            ['name' => 'Audit', 'code' => 'AUDIT', 'description' => 'From internal/external audit', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Test Process', 'code' => 'TEST', 'description' => 'From testing activities', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Customer Complaint', 'code' => 'CUST', 'description' => 'From customer complaint', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Equipment Issue', 'code' => 'EQUIP', 'description' => 'From equipment/calibration issue', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Method Deviation', 'code' => 'METHOD', 'description' => 'From method deviation', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Internal Review', 'code' => 'INTREV', 'description' => 'From internal review/inspection', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'External Feedback', 'code' => 'EXTFB', 'description' => 'From external feedback', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'code' => 'OTHER', 'description' => 'Other source', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // NC Statuses
        \DB::table('nc_statuses')->insert([
            ['name' => 'Identified', 'code' => 'IDENT', 'description' => 'NC has been identified', 'color_code' => '#dc3545', 'order_index' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'RCA In Progress', 'code' => 'RCA', 'description' => 'Root cause analysis in progress', 'color_code' => '#ffc107', 'order_index' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CAPA Assigned', 'code' => 'CAPA', 'description' => 'Corrective actions assigned', 'color_code' => '#17a2b8', 'order_index' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Verification Pending', 'code' => 'VERIFY', 'description' => 'Awaiting verification', 'color_code' => '#6f42c1', 'order_index' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Closed', 'code' => 'CLOSED', 'description' => 'NC is closed', 'color_code' => '#28a745', 'order_index' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cancelled', 'code' => 'CANCEL', 'description' => 'NC was cancelled', 'color_code' => '#6c757d', 'order_index' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Root Cause Methods
        \DB::table('root_cause_methods')->insert([
            ['name' => '5 Whys', 'code' => '5WHY', 'description' => 'Iterative interrogative technique', 'template' => json_encode(['why1' => '', 'why2' => '', 'why3' => '', 'why4' => '', 'why5' => '']), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fishbone Diagram', 'code' => 'FISH', 'description' => 'Ishikawa diagram analyzing causes', 'template' => json_encode(['manpower' => '', 'method' => '', 'machine' => '', 'material' => '', 'measurement' => '', 'environment' => '']), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fault Tree Analysis', 'code' => 'FTA', 'description' => 'Top-down deductive failure analysis', 'template' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Human Error Analysis', 'code' => 'HEA', 'description' => 'Analysis focused on human factors', 'template' => json_encode(['error_type' => '', 'contributing_factors' => '', 'systemic_issues' => '']), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pareto Analysis', 'code' => 'PAR', 'description' => '80/20 rule analysis', 'template' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'code' => 'OTHER', 'description' => 'Other method', 'template' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // RCA Statuses
        \DB::table('rca_statuses')->insert([
            ['name' => 'Draft', 'code' => 'DRAFT', 'description' => 'RCA in draft', 'color_code' => '#6c757d', 'order_index' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Submitted', 'code' => 'SUBMIT', 'description' => 'RCA submitted for review', 'color_code' => '#ffc107', 'order_index' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Approved', 'code' => 'APPROVE', 'description' => 'RCA approved', 'color_code' => '#28a745', 'order_index' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rejected', 'code' => 'REJECT', 'description' => 'RCA rejected', 'color_code' => '#dc3545', 'order_index' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // CAPA Categories
        \DB::table('capa_categories')->insert([
            ['name' => 'Process Improvement', 'code' => 'PROC', 'description' => 'Process changes', 'action_type' => 'both', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Training', 'code' => 'TRAIN', 'description' => 'Staff training', 'action_type' => 'both', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Documentation', 'code' => 'DOC', 'description' => 'Documentation updates', 'action_type' => 'both', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Equipment', 'code' => 'EQUIP', 'description' => 'Equipment maintenance/calibration', 'action_type' => 'both', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'System Change', 'code' => 'SYS', 'description' => 'System or software changes', 'action_type' => 'both', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Supplier Action', 'code' => 'SUPP', 'description' => 'Actions from suppliers', 'action_type' => 'corrective', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Infrastructure', 'code' => 'INFRA', 'description' => 'Facility/infrastructure changes', 'action_type' => 'both', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // CAPA Action Types
        \DB::table('capa_action_types')->insert([
            ['name' => 'Corrective', 'code' => 'CORR', 'description' => 'Corrective action to eliminate cause', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Preventive', 'code' => 'PREV', 'description' => 'Preventive action to prevent occurrence', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // CAPA Statuses
        \DB::table('capa_statuses')->insert([
            ['name' => 'Open', 'code' => 'OPEN', 'description' => 'CAPA is open', 'color_code' => '#dc3545', 'order_index' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'In Progress', 'code' => 'INPROG', 'description' => 'CAPA in progress', 'color_code' => '#ffc107', 'order_index' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Implemented', 'code' => 'IMPL', 'description' => 'CAPA implemented', 'color_code' => '#17a2b8', 'order_index' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Verification Pending', 'code' => 'VERIFY', 'description' => 'Awaiting verification', 'color_code' => '#6f42c1', 'order_index' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Verified', 'code' => 'VERF', 'description' => 'CAPA verified effective', 'color_code' => '#20c997', 'order_index' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Closed', 'code' => 'CLOSED', 'description' => 'CAPA closed', 'color_code' => '#28a745', 'order_index' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Overdue', 'code' => 'OVERDUE', 'description' => 'CAPA is overdue', 'color_code' => '#dc3545', 'order_index' => 7, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cancelled', 'code' => 'CANCEL', 'description' => 'CAPA cancelled', 'color_code' => '#6c757d', 'order_index' => 8, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // CAPA Priorities
        \DB::table('capa_priorities')->insert([
            ['name' => 'Critical', 'code' => 'CRIT', 'description' => 'Requires immediate action', 'color_code' => '#dc3545', 'priority_level' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'High', 'code' => 'HIGH', 'description' => 'High priority', 'color_code' => '#fd7e14', 'priority_level' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Medium', 'code' => 'MED', 'description' => 'Medium priority', 'color_code' => '#ffc107', 'priority_level' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Low', 'code' => 'LOW', 'description' => 'Low priority', 'color_code' => '#28a745', 'priority_level' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Verification Results
        \DB::table('verification_results')->insert([
            ['name' => 'Effective', 'code' => 'EFF', 'description' => 'Action was effective', 'color_code' => '#28a745', 'requires_reopen' => false, 'next_workflow_step' => 8, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Partially Effective', 'code' => 'PART', 'description' => 'Action was partially effective', 'color_code' => '#ffc107', 'requires_reopen' => true, 'next_workflow_step' => 8, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Not Effective', 'code' => 'NOTEFF', 'description' => 'Action was not effective', 'color_code' => '#dc3545', 'requires_reopen' => true, 'next_workflow_step' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Severity Scales (default 1-5 scale)
        \DB::table('severity_scales')->insert([
            ['name' => 'Very Low', 'code' => 'SEV-1', 'score' => 1, 'description' => 'Very low severity', 'color_code' => '#28a745', 'order_index' => 1, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Low', 'code' => 'SEV-2', 'score' => 2, 'description' => 'Low severity', 'color_code' => '#6c757d', 'order_index' => 2, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Medium', 'code' => 'SEV-3', 'score' => 3, 'description' => 'Medium severity', 'color_code' => '#ffc107', 'order_index' => 3, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'High', 'code' => 'SEV-4', 'score' => 4, 'description' => 'High severity', 'color_code' => '#fd7e14', 'order_index' => 4, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Very High', 'code' => 'SEV-5', 'score' => 5, 'description' => 'Very high severity', 'color_code' => '#dc3545', 'order_index' => 5, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Likelihood Scales (default 1-5 scale)
        \DB::table('likelihood_scales')->insert([
            ['name' => 'Very Unlikely', 'code' => 'LIK-1', 'score' => 1, 'description' => 'Very unlikely to occur', 'color_code' => '#28a745', 'order_index' => 1, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Unlikely', 'code' => 'LIK-2', 'score' => 2, 'description' => 'Unlikely to occur', 'color_code' => '#6c757d', 'order_index' => 2, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Possible', 'code' => 'LIK-3', 'score' => 3, 'description' => 'Possible to occur', 'color_code' => '#ffc107', 'order_index' => 3, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Likely', 'code' => 'LIK-4', 'score' => 4, 'description' => 'Likely to occur', 'color_code' => '#fd7e14', 'order_index' => 4, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Very Likely', 'code' => 'LIK-5', 'score' => 5, 'description' => 'Very likely to occur', 'color_code' => '#dc3545', 'order_index' => 5, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Verification Closure Statuses
        \DB::table('verification_closure_statuses')->insert([
            ['name' => 'Pending', 'code' => 'PEND', 'description' => 'Pending closure', 'color_code' => '#ffc107', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Closed', 'code' => 'CLOSED', 'description' => 'Verification closed', 'color_code' => '#28a745', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Reopened', 'code' => 'REOPEN', 'description' => 'Verification reopened', 'color_code' => '#dc3545', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Attachment Types
        \DB::table('attachment_types')->insert([
            ['name' => 'Evidence', 'code' => 'EVID', 'description' => 'Evidence document', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Document', 'code' => 'DOC', 'description' => 'General document', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Photo', 'code' => 'PHOTO', 'description' => 'Photograph', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Report', 'code' => 'RPT', 'description' => 'Report document', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Form', 'code' => 'FORM', 'description' => 'Form document', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'code' => 'OTHER', 'description' => 'Other attachment', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Audit Team Roles
        \DB::table('audit_team_roles')->insert([
            ['name' => 'Lead Auditor', 'code' => 'LEAD', 'description' => 'Lead auditor', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Auditor', 'code' => 'AUD', 'description' => 'Auditor', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Observer', 'code' => 'OBS', 'description' => 'Observer', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Technical Expert', 'code' => 'TECH', 'description' => 'Technical expert', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Notification Types
        \DB::table('audit_notification_types')->insert([
            ['name' => 'Upcoming Audit', 'code' => 'UPCOMING', 'description' => 'Upcoming audit reminder', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Overdue CAPA', 'code' => 'OVERDUE', 'description' => 'Overdue CAPA alert', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NC Requires Action', 'code' => 'NCACTION', 'description' => 'NC requires action', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Verification Pending', 'code' => 'VERIFYPEND', 'description' => 'Verification pending', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Audit Reminder', 'code' => 'REMIND', 'description' => 'General audit reminder', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Escalation', 'code' => 'ESCAL', 'description' => 'Escalation notification', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order of dependencies
        Schema::dropIfExists('audit_notifications');
        Schema::dropIfExists('audit_attachments');
        Schema::dropIfExists('verification_records');
        Schema::dropIfExists('corrective_actions');
        Schema::dropIfExists('root_cause_analyses');
        Schema::dropIfExists('non_conformances');
        Schema::dropIfExists('audit_module_findings');
        Schema::dropIfExists('audit_checklist_audit');
        Schema::dropIfExists('audit_team_members');
        Schema::dropIfExists('iso_audits');
        Schema::dropIfExists('audit_checklist_items');
        Schema::dropIfExists('audit_checklists');
        Schema::dropIfExists('audit_notification_types');
        Schema::dropIfExists('audit_team_roles');
        Schema::dropIfExists('attachment_types');
        Schema::dropIfExists('verification_closure_statuses');
        Schema::dropIfExists('likelihood_scales');
        Schema::dropIfExists('severity_scales');
        Schema::dropIfExists('verification_results');
        Schema::dropIfExists('capa_priorities');
        Schema::dropIfExists('capa_statuses');
        Schema::dropIfExists('capa_action_types');
        Schema::dropIfExists('capa_categories');
        Schema::dropIfExists('rca_statuses');
        Schema::dropIfExists('root_cause_methods');
        Schema::dropIfExists('nc_statuses');
        Schema::dropIfExists('nc_origins');
        Schema::dropIfExists('risk_levels');
        Schema::dropIfExists('finding_statuses');
        Schema::dropIfExists('finding_categories');
        Schema::dropIfExists('audit_statuses');
        Schema::dropIfExists('audit_types');
        
        // Also drop old tables if they exist
        Schema::dropIfExists('audit_findings');
        Schema::dropIfExists('audit_summary_reports');
    }
};
