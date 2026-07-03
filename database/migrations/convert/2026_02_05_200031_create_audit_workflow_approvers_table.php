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
        Schema::create('audit_workflow_approvers', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('module')->default('audit')->index('idx_audit_workflow_approvers_audit_43ad5272');
            $table->integer('workflow_step')->comment('Workflow step number (1-8 for Audit, 3-8 for NC, 5-8 for CAPA)');
            $table->string('role_type')->default('approver')->comment('approver or verifier');
            $table->uuid('user_id')->index('idx_audit_workflow_approvers_user_id_3db5bbf4')->comment('Specific user assigned');
            $table->string('iso_role')->nullable()->comment('ISO role: Lead Auditor, Quality Manager, Top Management, Auditee');
            $table->boolean('is_required')->default(true)->comment('Whether approval is required before proceeding');
            $table->string('approval_type')->default('single')->comment('single or multiple (for multiple approvers)');
            $table->uuid('company_id')->nullable()->index('idx_audit_workflow_approvers_company_id_f064313a');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['role_type', 'company_id'], 'idx_audit_workflow_approvers_role_type_company_id_7db2faa0');
            $table->index(['workflow_step', 'company_id'], 'idx_audit_workflow_approvers_workflow_step_company_id_fcc9a249');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_workflow_approvers');
    }
};
