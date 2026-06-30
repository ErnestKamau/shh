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
        Schema::create('crm_feedback_corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_feedback_id');
            $table->foreign('customer_feedback_id')->references('id')->on('customerfeedbacks')->cascadeOnDelete();
            $table->string('reference_no')->nullable()->unique();
            $table->string('status')->default('Draft');
            $table->boolean('auto_triggered')->default(false);
            $table->timestamp('triggered_at')->nullable();
            $table->uuid('opened_by')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('summary_notes')->nullable();
            $table->timestamps();

            $table->unique('customer_feedback_id');
            $table->index(['status', 'assigned_to']);
            $table->foreign('opened_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('crm_feedback_corrective_action_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_corrective_action_id');
            $table->string('source_type')->default('reported_issue');
            $table->uuid('evaluation_metric_id')->nullable();
            $table->text('issue_summary')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('corrective_action_plan')->nullable();
            $table->uuid('responsible_user_id')->nullable();
            $table->date('target_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->text('effectiveness_notes')->nullable();
            $table->timestamps();

            $table->index(['feedback_corrective_action_id', 'source_type'], 'crm_feedback_ca_items_parent_source_idx');
            $table->foreign('feedback_corrective_action_id', 'crm_fb_ca_items_action_fk')
                ->references('id')
                ->on('crm_feedback_corrective_actions')
                ->cascadeOnDelete();
            $table->foreign('evaluation_metric_id', 'crm_fb_ca_items_metric_fk')
                ->references('id')
                ->on('crm_evaluation_metrics')
                ->nullOnDelete();
            $table->foreign('responsible_user_id', 'crm_fb_ca_items_resp_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_feedback_corrective_action_items');
        Schema::dropIfExists('crm_feedback_corrective_actions');
    }
};
