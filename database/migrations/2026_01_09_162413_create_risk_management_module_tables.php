<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Risk Management Module - ISO/IEC 17025:2017 Clause 8.5 & ISO 9001:2015 Clause 6.1
     */
    public function up(): void
    {
        // ============================================
        // CONFIGURATION/MASTER DATA TABLES
        // ============================================
        
        // Risk Categories (Technical, Quality, Operational, Business, HSE, etc.)
        Schema::create('risk_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Risk Sources (Manual Entry, Audit Finding, NC, Complaint, Equipment, etc.)
        Schema::create('risk_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Treatment Types (Eliminate, Reduce, Share, Accept)
        Schema::create('treatment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Risk Statuses (with workflow_step for workflow management)
        Schema::create('risk_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('order_index')->default(0);
            $table->integer('workflow_step')->nullable(); // 1-7 corresponding to workflow steps
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // ============================================
        // MAIN DATA TABLES
        // ============================================

        // Main Risks Table
        Schema::create('risks', function (Blueprint $table) {
            $table->id();
            $table->string('risk_number')->unique();
            
            // Basic Information
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('category_name')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_name')->nullable();
            
            // Risk Owner
            $table->string('risk_owner_name')->nullable();
            $table->unsignedBigInteger('risk_owner_id')->nullable();
            $table->string('department')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            
            // Risk Identification
            $table->date('date_identified');
            $table->string('identified_by')->nullable();
            $table->unsignedBigInteger('identified_by_user_id')->nullable();
            
            // Links to other entities
            $table->unsignedBigInteger('audit_id')->nullable();
            $table->unsignedBigInteger('audit_finding_id')->nullable();
            $table->unsignedBigInteger('non_conformance_id')->nullable();
            $table->unsignedBigInteger('complaint_id')->nullable();
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->text('related_entities')->nullable(); // JSON for other links
            
            // Risk Assessment (Step 2)
            $table->unsignedBigInteger('likelihood_scale_id')->nullable();
            $table->integer('likelihood_score')->nullable(); // 1-5
            $table->unsignedBigInteger('severity_scale_id')->nullable();
            $table->integer('severity_score')->nullable(); // 1-5
            $table->integer('rpn')->nullable(); // Risk Priority Number = Likelihood × Severity
            $table->string('risk_level')->nullable(); // Critical, High, Medium, Low (calculated from RPN)
            $table->text('assessment_notes')->nullable();
            $table->date('assessment_date')->nullable();
            
            // Risk Evaluation (Step 3)
            $table->string('evaluation_result')->nullable(); // Unacceptable, Tolerable, Acceptable
            $table->text('evaluation_notes')->nullable();
            $table->date('evaluation_date')->nullable();
            
            // Acceptance Criteria Configuration
            $table->integer('acceptance_threshold_rpn')->nullable(); // Configurable threshold
            $table->text('acceptance_criteria')->nullable();
            
            // Workflow Management
            $table->unsignedBigInteger('status_id')->nullable();
            $table->string('status_name')->nullable();
            $table->integer('workflow_step')->default(1); // Current workflow step (1-7)
            
            // Treatment Planning (Step 4)
            $table->boolean('requires_treatment')->default(false);
            $table->text('treatment_justification')->nullable();
            
            // Monitoring (Step 6)
            $table->integer('review_frequency_days')->nullable(); // Days between reviews
            $table->date('next_review_date')->nullable();
            $table->date('last_review_date')->nullable();
            
            // Residual Risk (after controls)
            $table->integer('residual_likelihood_score')->nullable();
            $table->integer('residual_severity_score')->nullable();
            $table->integer('residual_rpn')->nullable();
            $table->string('residual_risk_level')->nullable();
            
            // Closure (Step 7)
            $table->string('closure_type')->nullable(); // Eliminated, Controlled, Accepted
            $table->text('closure_justification')->nullable();
            $table->date('closure_date')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            
            // Metadata
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('category_id')->references('id')->on('risk_categories')->onDelete('set null');
            $table->foreign('source_id')->references('id')->on('risk_sources')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('risk_statuses')->onDelete('set null');
            $table->foreign('likelihood_scale_id')->references('id')->on('likelihood_scales')->onDelete('set null');
            $table->foreign('severity_scale_id')->references('id')->on('severity_scales')->onDelete('set null');
            $table->foreign('audit_id')->references('id')->on('iso_audits')->onDelete('set null');
            $table->foreign('audit_finding_id')->references('id')->on('audit_module_findings')->onDelete('set null');
            $table->foreign('non_conformance_id')->references('id')->on('non_conformances')->onDelete('set null');
            
            // Indexes
            $table->index(['status_id', 'company_id']);
            $table->index(['workflow_step', 'company_id']);
            $table->index('risk_number');
            $table->index('date_identified');
            $table->index('next_review_date');
        });

        // Risk Treatment Plans (Step 4 & 5)
        Schema::create('risk_treatment_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('risk_id');
            $table->unsignedBigInteger('treatment_type_id')->nullable();
            $table->string('treatment_type_name')->nullable();
            
            $table->string('title');
            $table->text('description');
            $table->text('control_measures')->nullable();
            $table->text('expected_outcome')->nullable();
            
            // Assignment
            $table->string('responsible_person')->nullable();
            $table->unsignedBigInteger('responsible_user_id')->nullable();
            $table->string('department')->nullable();
            
            // Dates
            $table->date('target_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->text('extension_reason')->nullable();
            
            // Implementation Status
            $table->string('implementation_status')->default('Planned'); // Planned, In Progress, Completed, On Hold, Cancelled
            $table->text('implementation_notes')->nullable();
            $table->date('implementation_start_date')->nullable();
            $table->date('implementation_end_date')->nullable();
            
            // Links to CAPA
            $table->unsignedBigInteger('capa_id')->nullable(); // Link to corrective_actions table
            $table->text('related_actions')->nullable(); // JSON for training, equipment, documents, etc.
            
            // Metadata
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('risk_id')->references('id')->on('risks')->onDelete('cascade');
            $table->foreign('treatment_type_id')->references('id')->on('treatment_types')->onDelete('set null');
            $table->foreign('capa_id')->references('id')->on('corrective_actions')->onDelete('set null');
            
            $table->index(['risk_id', 'implementation_status']);
        });

        // Risk Reviews (Step 6 - Monitoring)
        Schema::create('risk_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('risk_id');
            $table->string('review_number')->unique();
            
            $table->date('review_date');
            $table->string('reviewed_by')->nullable();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            
            // Review Type
            $table->string('review_type')->nullable(); // Scheduled, Triggered, Periodic
            $table->text('review_reason')->nullable();
            
            // Reassessment
            $table->integer('review_likelihood_score')->nullable();
            $table->integer('review_severity_score')->nullable();
            $table->integer('review_rpn')->nullable();
            $table->string('review_risk_level')->nullable();
            
            // Control Effectiveness
            $table->text('control_effectiveness_assessment')->nullable();
            $table->boolean('controls_effective')->nullable();
            $table->text('effectiveness_evidence')->nullable();
            
            // Findings
            $table->text('review_findings')->nullable();
            $table->text('opportunities_for_improvement')->nullable();
            $table->text('new_risks_identified')->nullable();
            
            // Decisions
            $table->string('review_decision')->nullable(); // Continue Monitoring, Close Risk, Additional Controls Needed
            $table->text('decision_justification')->nullable();
            $table->date('next_review_date')->nullable();
            
            // Metadata
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('risk_id')->references('id')->on('risks')->onDelete('cascade');
            $table->index(['risk_id', 'review_date']);
            $table->index('review_number');
        });

        // Risk Attachments (Polymorphic - can attach to risks, treatment plans, reviews)
        Schema::create('risk_attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable'); // risk_id, risk_treatment_plan_id, risk_review_id
            
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('original_name')->nullable();
            $table->text('description')->nullable();
            
            $table->unsignedBigInteger('uploaded_by');
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Note: morphs() already creates index on attachable_type and attachable_id
        });

        // Risk Notifications (Polymorphic)
        Schema::create('risk_notifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('notifiable'); // risk_id
            
            $table->string('title');
            $table->text('message')->nullable();
            
            $table->unsignedBigInteger('recipient_user_id');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_email_sent')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            
            $table->integer('company_id')->default(0);
            $table->timestamps();

            $table->index(['recipient_user_id', 'is_read']);
            // Note: morphs() already creates index on notifiable_type and notifiable_id
        });

        // Seed default configuration data
        $this->seedDefaultData();
    }

    /**
     * Seed default configuration data
     */
    private function seedDefaultData(): void
    {
        // Risk Categories
        \DB::table('risk_categories')->insert([
            ['name' => 'Technical', 'code' => 'TECH', 'description' => 'Technical risks related to testing, methods, equipment', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Quality', 'code' => 'QUAL', 'description' => 'Quality management system risks', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Operational', 'code' => 'OPS', 'description' => 'Operational and process risks', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Business', 'code' => 'BIZ', 'description' => 'Business and commercial risks', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Health, Safety & Environmental', 'code' => 'HSE', 'description' => 'Health, safety and environmental risks', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Compliance', 'code' => 'COMP', 'description' => 'Regulatory and compliance risks', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Information Security', 'code' => 'INFOSEC', 'description' => 'Information security and data protection risks', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Risk Sources
        \DB::table('risk_sources')->insert([
            ['name' => 'Manual Entry', 'code' => 'MANUAL', 'description' => 'Manually identified risk', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Non-Conformance', 'code' => 'NC', 'description' => 'Risk identified from non-conformance', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Audit Finding', 'code' => 'AUDIT', 'description' => 'Risk identified from audit finding', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Complaint', 'code' => 'COMPLAINT', 'description' => 'Risk identified from customer complaint', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Equipment Failure', 'code' => 'EQUIP', 'description' => 'Risk from equipment failure or calibration issues', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Training/Competency Expiry', 'code' => 'TRAIN', 'description' => 'Risk from training or competency expiry', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Management Review', 'code' => 'MGTREV', 'description' => 'Risk identified during management review', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Routine Operation', 'code' => 'ROUTINE', 'description' => 'Risk identified during routine operations', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Environmental Limits', 'code' => 'ENV', 'description' => 'Risk from environmental limits exceeded', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Treatment Types
        \DB::table('treatment_types')->insert([
            ['name' => 'Eliminate', 'code' => 'ELIM', 'description' => 'Remove the risk entirely', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Reduce', 'code' => 'REDUCE', 'description' => 'Mitigate the risk to acceptable levels', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Share', 'code' => 'SHARE', 'description' => 'Transfer risk (insurance, outsourcing)', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Accept', 'code' => 'ACCEPT', 'description' => 'Accept risk with documented justification', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Risk Statuses (with workflow_step mapping)
        \DB::table('risk_statuses')->insert([
            // Step 1: Risk Identified
            ['name' => 'Identified', 'code' => 'IDENT', 'description' => 'Risk has been identified', 'color_code' => '#17a2b8', 'order_index' => 1, 'workflow_step' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'New Risk', 'code' => 'NEW', 'description' => 'New risk identified', 'color_code' => '#17a2b8', 'order_index' => 2, 'workflow_step' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // Step 2: Risk Assessment
            ['name' => 'Under Assessment', 'code' => 'ASSESS', 'description' => 'Risk is being assessed', 'color_code' => '#ffc107', 'order_index' => 3, 'workflow_step' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Assessment In Progress', 'code' => 'ASSESSING', 'description' => 'Assessment in progress', 'color_code' => '#ffc107', 'order_index' => 4, 'workflow_step' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // Step 3: Risk Evaluation
            ['name' => 'Under Evaluation', 'code' => 'EVAL', 'description' => 'Risk is being evaluated', 'color_code' => '#fd7e14', 'order_index' => 5, 'workflow_step' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Evaluation In Progress', 'code' => 'EVALUATING', 'description' => 'Evaluation in progress', 'color_code' => '#fd7e14', 'order_index' => 6, 'workflow_step' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // Step 4: Treatment Planning
            ['name' => 'Treatment Planning', 'code' => 'PLAN', 'description' => 'Treatment plan is being developed', 'color_code' => '#6f42c1', 'order_index' => 7, 'workflow_step' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Control Measures Planned', 'code' => 'PLANNED', 'description' => 'Control measures have been planned', 'color_code' => '#6f42c1', 'order_index' => 8, 'workflow_step' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // Step 5: Treatment Implementation
            ['name' => 'Treatment In Progress', 'code' => 'TREAT', 'description' => 'Treatment is being implemented', 'color_code' => '#20c997', 'order_index' => 9, 'workflow_step' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Controls Implemented', 'code' => 'IMPL', 'description' => 'Controls have been implemented', 'color_code' => '#20c997', 'order_index' => 10, 'workflow_step' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // Step 6: Risk Monitoring
            ['name' => 'Monitored', 'code' => 'MONITOR', 'description' => 'Risk is being monitored', 'color_code' => '#17a2b8', 'order_index' => 11, 'workflow_step' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Under Monitoring', 'code' => 'MONITORING', 'description' => 'Risk under ongoing monitoring', 'color_code' => '#17a2b8', 'order_index' => 12, 'workflow_step' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // Step 7: Risk Closed
            ['name' => 'Closed', 'code' => 'CLOSED', 'description' => 'Risk has been closed', 'color_code' => '#28a745', 'order_index' => 13, 'workflow_step' => 7, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Resolved', 'code' => 'RESOLVED', 'description' => 'Risk has been resolved', 'color_code' => '#28a745', 'order_index' => 14, 'workflow_step' => 7, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_notifications');
        Schema::dropIfExists('risk_attachments');
        Schema::dropIfExists('risk_reviews');
        Schema::dropIfExists('risk_treatment_plans');
        Schema::dropIfExists('risks');
        Schema::dropIfExists('risk_statuses');
        Schema::dropIfExists('treatment_types');
        Schema::dropIfExists('risk_sources');
        Schema::dropIfExists('risk_categories');
    }
};

