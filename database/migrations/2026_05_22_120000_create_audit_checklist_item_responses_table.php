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
        if (Schema::hasTable('audit_checklist_item_responses')) {
            return;
        }

        Schema::create('audit_checklist_item_responses', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('audit_id')->index('idx_audit_checklist_item_responses_audit_id');
            $table->uuid('audit_checklist_item_id')->index('idx_audit_checklist_item_responses_item_id');
            $table->string('compliance_status')->nullable();
            $table->text('audit_question')->nullable();
            $table->text('evidence_collected')->nullable();
            $table->text('observation')->nullable();
            $table->text('findings')->nullable();
            $table->text('auditor_notes')->nullable();
            $table->uuid('audited_by')->nullable()->index('idx_audit_checklist_item_responses_audited_by');
            $table->timestamp('audited_at')->nullable();
            $table->boolean('requires_follow_up')->default(false);
            $table->text('follow_up_notes')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_audit_checklist_item_responses_company_id');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['audit_id', 'audit_checklist_item_id'],
                'uniq_audit_checklist_item_responses_audit_item'
            );
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_checklist_item_responses');
    }
};
