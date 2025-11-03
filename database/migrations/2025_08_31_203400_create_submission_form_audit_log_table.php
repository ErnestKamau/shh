<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionFormAuditLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submission_form_audit_log', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('submission_form_instance_id');
            $table->bigInteger('user_id');
            $table->enum('action', ['created', 'updated', 'submitted', 'reviewed', 'approved', 'rejected', 'cancelled']);
            $table->json('field_changes')->nullable(); // Track what fields were changed
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            // Foreign key constraints
            $table->foreign('submission_form_instance_id', 'sf_audit_instance_id_foreign')->references('id')->on('submission_form_instances')->onDelete('cascade');
            $table->foreign('user_id', 'sf_audit_user_id_foreign')->references('id')->on('users');

            // Indexes for performance
            $table->index(['submission_form_instance_id', 'action'], 'sf_audit_instance_action_idx');
            $table->index('created_at', 'sf_audit_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submission_form_audit_log');
    }
}