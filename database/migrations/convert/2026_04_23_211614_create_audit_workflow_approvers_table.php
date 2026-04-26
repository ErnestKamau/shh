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
            $table->string('module')->default('audit')->index('idx_audit_workflow_approvers_audit_dd0dbbbe');
            $table->integer('workflow_step')->comment('Workflow step number (1-8 for Audit, 3-8 for NC, 5-8 for CAPA)');
            $table->string('role_type')->default('approver')->comment('approver or verifier');
            $table->uuid('user_id')->index('audit_workflow_approvers_user_id_foreign')->comment('Specific user assigned');
            $table->string('iso_role')->nullable()->comment('ISO role: Lead Auditor, Quality Manager, Top Management, Auditee');
            $table->boolean('is_required')->default(true)->comment('Whether approval is required before proceeding');
            $table->string('approval_type')->default('single')->comment('single or multiple (for multiple approvers)');
            $table->uuid('company_id')->default(0)->index('idx_audit_workflow_approvers_company_id_b35e4ffb');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['role_type', 'company_id'], 'idx_audit_workflow_approvers_role_type_company_id_804bab41');
            $table->index(['workflow_step', 'company_id'], 'idx_audit_workflow_approvers_workflow_step_company_id_07d234e7');
            $table->foreign(['user_id'], 'fk_audit_workflow_approvers_user_id_e43ca3f6')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_audit_workflow_approvers_company_id_44b60215')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

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
