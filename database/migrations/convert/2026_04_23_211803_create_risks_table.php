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
        Schema::create('risks', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('risk_number')->index('idx_risks_risk_number_da7d7c89');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('initial_comments')->nullable();
            $table->uuid('category_id')->nullable()->index('risks_category_id_foreign');
            $table->string('category_name')->nullable();
            $table->uuid('other_source_id')->nullable()->index('risks_other_source_id_foreign');
            $table->string('other_source_name')->nullable();
            $table->string('risk_owner_name')->nullable();
            $table->unsignedBigInteger('risk_owner_id')->nullable();
            $table->string('department')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->date('date_identified')->index('idx_risks_date_identified_a5afc293');
            $table->string('identified_by')->nullable();
            $table->unsignedBigInteger('identified_by_user_id')->nullable();
            $table->uuid('audit_id')->nullable()->index('risks_audit_id_foreign');
            $table->uuid('audit_finding_id')->nullable()->index('risks_audit_finding_id_foreign');
            $table->uuid('non_conformance_id')->nullable()->index('risks_non_conformance_id_foreign');
            $table->uuid('complaint_id')->nullable()->index('idx_risks_complaint_id_d55a6625');
            $table->uuid('equipment_id')->nullable()->index('idx_risks_equipment_id_a44bc40a');
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->uuid('sample_id')->nullable()->index('idx_risks_sample_id_764c726a');
            $table->string('sample_reference')->nullable();
            $table->uuid('method_id')->nullable()->index('idx_risks_method_id_497c4526');
            $table->string('method_reference')->nullable();
            $table->text('related_entities')->nullable();
            $table->uuid('likelihood_scale_id')->nullable()->index('risks_likelihood_scale_id_foreign');
            $table->integer('likelihood_score')->nullable();
            $table->uuid('severity_scale_id')->nullable()->index('risks_severity_scale_id_foreign');
            $table->integer('severity_score')->nullable();
            $table->integer('rpn')->nullable();
            $table->string('risk_level')->nullable();
            $table->text('assessment_notes')->nullable();
            $table->date('assessment_date')->nullable();
            $table->string('evaluation_result')->nullable();
            $table->text('evaluation_notes')->nullable();
            $table->date('evaluation_date')->nullable();
            $table->integer('acceptance_threshold_rpn')->nullable();
            $table->text('acceptance_criteria')->nullable();
            $table->uuid('status_id')->nullable();
            $table->string('status_name')->nullable();
            $table->integer('workflow_step')->default(1);
            $table->uuid('current_assessment_id')->nullable()->index('idx_risks_current_assessment_id_bb81a53f');
            $table->uuid('current_evaluation_id')->nullable()->index('idx_risks_current_evaluation_id_a596df5b');
            $table->boolean('requires_treatment')->default(false);
            $table->text('treatment_justification')->nullable();
            $table->integer('review_frequency_days')->nullable();
            $table->date('next_review_date')->nullable()->index('idx_risks_next_review_date_041addd8');
            $table->date('last_review_date')->nullable();
            $table->integer('residual_likelihood_score')->nullable();
            $table->integer('residual_severity_score')->nullable();
            $table->integer('residual_rpn')->nullable();
            $table->string('residual_risk_level')->nullable();
            $table->string('closure_type')->nullable();
            $table->text('closure_justification')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->date('closure_date')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->uuid('company_id')->default(0)->index('idx_risks_company_id_ed72944e');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['risk_number']);
            $table->index(['status_id', 'company_id'], 'idx_risks_status_id_company_id_b7184fc4');
            $table->index(['workflow_step', 'company_id'], 'idx_risks_workflow_step_company_id_6b3250ba');
            $table->foreign(['audit_finding_id'], 'fk_risks_audit_finding_id_df00ca43')->references(['id'])->on('audit_module_findings')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['audit_id'], 'fk_risks_audit_id_23431ec5')->references(['id'])->on('iso_audits')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['category_id'], 'fk_risks_category_id_c2349a14')->references(['id'])->on('risk_categories')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['likelihood_scale_id'], 'fk_risks_likelihood_scale_id_4db81be6')->references(['id'])->on('likelihood_scales')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['method_id'], 'fk_risks_method_id_649bd396')->references(['id'])->on('analysis_methods')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['non_conformance_id'], 'fk_risks_non_conformance_id_274db830')->references(['id'])->on('non_conformances')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['other_source_id'], 'fk_risks_other_source_id_b254132d')->references(['id'])->on('risk_sources')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_id'], 'fk_risks_sample_id_3ecfa1f8')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['severity_scale_id'], 'fk_risks_severity_scale_id_4e5d3a1f')->references(['id'])->on('severity_scales')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['status_id'], 'fk_risks_status_id_9032896b')->references(['id'])->on('risk_statuses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_risks_company_id_91e8fdd9')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['complaint_id'], 'fk_risks_complaint_id_3229aa4f')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['equipment_id'], 'fk_risks_equipment_id_b8697cec')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');



            $table->primary(['id']);



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risks');
    }
};
