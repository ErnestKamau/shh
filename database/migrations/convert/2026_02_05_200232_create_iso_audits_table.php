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
        if (Schema::hasTable('iso_audits')) {
            return;
        }
        Schema::create('iso_audits', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('audit_number')->index('idx_iso_audits_audit_number_bdc4f96a');
            $table->string('revision_number')->default('REV. 00');
            $table->uuid('audit_type_id')->nullable()->index('idx_iso_audits_audit_type_id_307c72db');
            $table->string('audit_type_name')->nullable();
            $table->string('title');
            $table->text('objective')->nullable();
            $table->text('scope')->nullable();
            $table->text('criteria')->nullable();
            $table->uuid('checklist_id')->nullable()->index('idx_iso_audits_checklist_id_95ff082c');
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
            $table->uuid('status_id')->nullable();
            $table->string('status_name')->nullable();
            $table->text('executive_summary')->nullable();
            $table->text('conclusions')->nullable();
            $table->text('recommendations')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_iso_audits_created_by_8765a85a');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_iso_audits_company_id_a43d6ec5');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['audit_number']);
            $table->index(['scheduled_date', 'start_date'], 'idx_iso_audits_scheduled_date_start_date_a7f942c4');
            $table->index(['status_id', 'company_id'], 'idx_iso_audits_status_id_company_id_67d5b2b4');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iso_audits');
    }
};
