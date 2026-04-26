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
        Schema::create('submission_form_audit_log', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_instance_id');
            $table->uuid('user_id')->index('sf_audit_user_id_foreign');
            $table->enum('action', ['created', 'updated', 'submitted', 'reviewed', 'approved', 'rejected', 'cancelled']);
            $table->longText('field_changes')->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable()->index('sf_audit_created_at_idx');

            $table->index(['submission_form_instance_id', 'action'], 'sf_audit_instance_action_idx');
            $table->foreign(['submission_form_instance_id'], 'sf_audit_instance_id_foreign')->references(['id'])->on('submission_form_instances')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'sf_audit_user_id_foreign')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_form_audit_log');
    }
};
