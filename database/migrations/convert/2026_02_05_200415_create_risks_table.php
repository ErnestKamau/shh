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
        if (Schema::hasTable('risks')) {
            return;
        }
        Schema::create('risks', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('risk_number')->index('idx_risks_risk_number_0d632872');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('initial_comments')->nullable();
            $table->uuid('category_id')->nullable()->index('idx_risks_category_id_94e9bb83');
            $table->string('category_name')->nullable();
            $table->uuid('other_source_id')->nullable()->index('idx_risks_other_source_id_abe947a2');
            $table->string('other_source_name')->nullable();
            $table->string('risk_owner_name')->nullable();
            $table->unsignedBigInteger('risk_owner_id')->nullable();
            $table->string('department')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->date('date_identified')->index('idx_risks_date_identified_74bce34a');
            $table->string('identified_by')->nullable();
            $table->unsignedBigInteger('identified_by_user_id')->nullable();
            $table->uuid('audit_id')->nullable()->index('idx_risks_audit_id_07737feb');
            $table->uuid('audit_finding_id')->nullable()->index('idx_risks_audit_finding_id_9f23b409');
            $table->uuid('non_conformance_id')->nullable()->index('idx_risks_non_conformance_id_07ed4848');
            $table->uuid('complaint_id')->nullable()->index('idx_risks_complaint_id_896c0197');
            $table->uuid('equipment_id')->nullable()->index('idx_risks_equipment_id_e93422b3');
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->uuid('sample_id')->nullable()->index('idx_risks_sample_id_528210d9');
            $table->string('sample_reference')->nullable();
            $table->uuid('method_id')->nullable()->index('idx_risks_method_id_ca5b59ea');
            $table->string('method_reference')->nullable();
            $table->text('related_entities')->nullable();
            $table->uuid('likelihood_scale_id')->nullable()->index('idx_risks_likelihood_scale_id_392bbeca');
            $table->integer('likelihood_score')->nullable();
            $table->uuid('severity_scale_id')->nullable()->index('idx_risks_severity_scale_id_8c1b9175');
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
            $table->uuid('current_assessment_id')->nullable()->index('idx_risks_current_assessment_id_b1207908');
            $table->uuid('current_evaluation_id')->nullable()->index('idx_risks_current_evaluation_id_3894b664');
            $table->boolean('requires_treatment')->default(false);
            $table->text('treatment_justification')->nullable();
            $table->integer('review_frequency_days')->nullable();
            $table->date('next_review_date')->nullable()->index('idx_risks_next_review_date_7f8169ba');
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
            $table->uuid('created_by')->nullable()->index('idx_risks_created_by_773e0128');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_risks_company_id_54f7339b');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['risk_number']);
            $table->index(['status_id', 'company_id'], 'idx_risks_status_id_company_id_e08233e4');
            $table->index(['workflow_step', 'company_id'], 'idx_risks_workflow_step_company_id_c51659f7');

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
