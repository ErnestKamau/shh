<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_checklist_responses', function (Blueprint $table) {
            $table->dropUnique('workflow_checklist_responses_unique');
        });

        Schema::table('workflow_checklist_responses', function (Blueprint $table) {
            $table->uuid('sample_id')->nullable()->change();
            $table->uuid('submission_form_instance_id')->nullable();

            $table->foreign('submission_form_instance_id')
                ->references('id')
                ->on('submission_form_instances')
                ->cascadeOnDelete();

            $table->unique(
                ['sample_id', 'approval_id', 'checklist_item_id'],
                'workflow_checklist_responses_sample_unique'
            );
            $table->unique(
                ['submission_form_instance_id', 'approval_id', 'checklist_item_id'],
                'workflow_checklist_responses_form_unique'
            );
            $table->index('submission_form_instance_id');
        });

        Schema::table('workflow_approval_logs', function (Blueprint $table) {
            $table->dropUnique('workflow_approval_logs_unique');
        });

        Schema::table('workflow_approval_logs', function (Blueprint $table) {
            $table->uuid('sample_id')->nullable()->change();
            $table->uuid('submission_form_instance_id')->nullable();

            $table->foreign('submission_form_instance_id')
                ->references('id')
                ->on('submission_form_instances')
                ->cascadeOnDelete();

            $table->unique(
                ['sample_id', 'approval_id'],
                'workflow_approval_logs_sample_unique'
            );
            $table->unique(
                ['submission_form_instance_id', 'approval_id'],
                'workflow_approval_logs_form_unique'
            );
            $table->index('submission_form_instance_id');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_checklist_responses', function (Blueprint $table) {
            $table->dropForeign(['submission_form_instance_id']);
            $table->dropUnique('workflow_checklist_responses_sample_unique');
            $table->dropUnique('workflow_checklist_responses_form_unique');
            $table->dropIndex(['submission_form_instance_id']);
            $table->dropColumn('submission_form_instance_id');
        });

        Schema::table('workflow_checklist_responses', function (Blueprint $table) {
            $table->uuid('sample_id')->nullable(false)->change();
            $table->unique(
                ['sample_id', 'approval_id', 'checklist_item_id'],
                'workflow_checklist_responses_unique'
            );
        });

        Schema::table('workflow_approval_logs', function (Blueprint $table) {
            $table->dropForeign(['submission_form_instance_id']);
            $table->dropUnique('workflow_approval_logs_sample_unique');
            $table->dropUnique('workflow_approval_logs_form_unique');
            $table->dropIndex(['submission_form_instance_id']);
            $table->dropColumn('submission_form_instance_id');
        });

        Schema::table('workflow_approval_logs', function (Blueprint $table) {
            $table->uuid('sample_id')->nullable(false)->change();
            $table->unique(
                ['sample_id', 'approval_id'],
                'workflow_approval_logs_unique'
            );
        });
    }
};
