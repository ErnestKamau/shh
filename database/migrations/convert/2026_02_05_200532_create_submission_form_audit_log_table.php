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
        if (Schema::hasTable('submission_form_audit_log')) {
            return;
        }
        Schema::create('submission_form_audit_log', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_instance_id');
            $table->uuid('user_id')->index('idx_submission_form_audit_log_user_id_ef1649b2');
            $table->enum('action', ['created', 'updated', 'submitted', 'reviewed', 'approved', 'rejected', 'cancelled']);
            $table->longText('field_changes')->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable()->index('idx_submission_form_audit_log_created_at_cd31af67');

            $table->index(['submission_form_instance_id', 'action'], 'idx_submission_form_audit_log_submission_form_instance_2791c529');
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
