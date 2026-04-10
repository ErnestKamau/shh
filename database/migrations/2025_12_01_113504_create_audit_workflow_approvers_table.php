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
            $table->id();
            $table->integer('workflow_step')->comment('Workflow step number (1-8 for Audit, 3-8 for NC, 5-8 for CAPA)');
            $table->string('role_type')->default('approver')->comment('approver or verifier');
            $table->bigInteger('user_id')->comment('Specific user assigned');
            $table->string('iso_role')->nullable()->comment('ISO role: Lead Auditor, Quality Manager, Top Management, Auditee');
            $table->boolean('is_required')->default(true)->comment('Whether approval is required before proceeding');
            $table->string('approval_type')->default('single')->comment('single or multiple (for multiple approvers)');
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['workflow_step', 'company_id']);
            $table->index(['role_type', 'company_id']);
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
