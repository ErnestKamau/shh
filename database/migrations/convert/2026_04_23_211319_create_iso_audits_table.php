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
        Schema::create('iso_audits', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('audit_number')->index('idx_iso_audits_audit_number_ba89460c');
            $table->string('revision_number')->default('REV. 00');
            $table->uuid('audit_type_id')->nullable()->index('iso_audits_audit_type_id_foreign');
            $table->string('audit_type_name')->nullable();
            $table->string('title');
            $table->text('objective')->nullable();
            $table->text('scope')->nullable();
            $table->text('criteria')->nullable();
            $table->uuid('checklist_id')->nullable()->index('iso_audits_checklist_id_foreign');
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
            $table->uuid('created_by')->nullable()->index('idx_iso_audits_created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->uuid('company_id')->default(0)->index('idx_iso_audits_company_id_f0fe7824');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['audit_number']);
            $table->index(['scheduled_date', 'start_date'], 'idx_iso_audits_scheduled_date_start_date_9ade4e7e');
            $table->index(['status_id', 'company_id'], 'idx_iso_audits_status_id_company_id_4d59f3ff');
            $table->foreign(['audit_type_id'], 'fk_iso_audits_audit_type_id_ca824c7f')->references(['id'])->on('audit_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['checklist_id'], 'fk_iso_audits_checklist_id_ce19dcf5')->references(['id'])->on('audit_checklists')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['status_id'], 'fk_iso_audits_status_id_208efbcf')->references(['id'])->on('audit_statuses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_iso_audits_company_id_0f65128f')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_iso_audits_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');

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
