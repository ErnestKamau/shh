<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates only the iso_audits table
     */
    public function up(): void
    {
        if (!Schema::hasTable('iso_audits')) {
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
                
                // Add foreign keys only if the referenced tables exist
                if (Schema::hasTable('audit_types')) {
                    $table->foreign('audit_type_id')->references('id')->on('audit_types')->onDelete('set null');
                }
                if (Schema::hasTable('audit_checklists')) {
                    $table->foreign('checklist_id')->references('id')->on('audit_checklists')->onDelete('set null');
                }
                if (Schema::hasTable('audit_statuses')) {
                    $table->foreign('status_id')->references('id')->on('audit_statuses')->onDelete('set null');
                }
                
                $table->index(['status_id', 'company_id']);
                $table->index(['scheduled_date', 'start_date']);
                $table->index('audit_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iso_audits');
    }
};
