<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('audit_activity_logs')) {
            Schema::create('audit_activity_logs', function (Blueprint $table) {
                $table->uuid('id');
                $table->string('loggable_type');
                $table->uuid('loggable_id');
                $table->string('action');
                $table->integer('workflow_step')->nullable();
                $table->string('workflow_step_name')->nullable();
                $table->string('previous_status')->nullable();
                $table->string('current_status')->nullable();
                $table->timestamp('step_started_at')->nullable();
                $table->integer('duration_seconds')->nullable();
                $table->text('remarks')->nullable();
                $table->text('description')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->uuid('performed_by')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->uuid('company_id')->nullable()->index('idx_audit_activity_logs_company_id');
                $table->timestamps();

                $table->index(
                    ['loggable_type', 'loggable_id'],
                    'idx_audit_activity_logs_loggable'
                );
                $table->index(['workflow_step', 'company_id'], 'idx_audit_activity_logs_workflow_company');
                $table->primary(['id']);
            });
        }

        if (! Schema::hasTable('audit_workflow_approvals')) {
            Schema::create('audit_workflow_approvals', function (Blueprint $table) {
                $table->uuid('id');
                $table->string('approvable_type');
                $table->uuid('approvable_id');
                $table->integer('workflow_step');
                $table->string('from_status')->nullable();
                $table->string('to_status')->nullable();
                $table->uuid('approver_id')->nullable()->index('idx_audit_workflow_approvals_approver_id');
                $table->string('approver_name')->nullable();
                $table->string('role_type')->default('approver');
                $table->string('iso_role')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->uuid('company_id')->nullable()->index('idx_audit_workflow_approvals_company_id');
                $table->timestamps();

                $table->index(
                    ['approvable_type', 'approvable_id'],
                    'idx_audit_workflow_approvals_approvable'
                );
                $table->index(
                    ['workflow_step', 'company_id'],
                    'idx_audit_workflow_approvals_step_company'
                );
                $table->primary(['id']);
            });
        }

        $this->fixAuditNotificationsMorphColumns();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_workflow_approvals');
        Schema::dropIfExists('audit_activity_logs');
    }

    private function fixAuditNotificationsMorphColumns(): void
    {
        if (! Schema::hasTable('audit_notifications')) {
            return;
        }

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (Schema::hasColumn('audit_notifications', 'notifiable_id')) {
            DB::statement('ALTER TABLE audit_notifications ALTER COLUMN notifiable_id TYPE VARCHAR(36) USING notifiable_id::text');
        }

        if (Schema::hasColumn('audit_notifications', 'recipient_user_id')) {
            DB::statement('ALTER TABLE audit_notifications ALTER COLUMN recipient_user_id DROP NOT NULL');
            DB::statement('ALTER TABLE audit_notifications ALTER COLUMN recipient_user_id TYPE VARCHAR(36) USING recipient_user_id::text');
        }
    }
};
