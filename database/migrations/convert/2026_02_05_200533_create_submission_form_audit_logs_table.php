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
        Schema::create('submission_form_audit_logs', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_instance_id')->index('idx_submission_form_audit_logs_submission_form_instanc_4f8e2892');
            $table->uuid('user_id')->index('idx_submission_form_audit_logs_user_id_16e023f6');
            $table->string('action')->index('idx_submission_form_audit_logs_action_674dd915');
            $table->longText('field_changes')->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable()->index('idx_submission_form_audit_logs_created_at_7df40a12');
            $table->timestamp('updated_at')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_form_audit_logs');
    }
};
