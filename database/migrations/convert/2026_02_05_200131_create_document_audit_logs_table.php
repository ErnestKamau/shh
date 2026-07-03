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
        if (Schema::hasTable('document_audit_logs')) {
            return;
        }
        Schema::create('document_audit_logs', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('auditable_type');
            $table->bigInteger('auditable_id');
            $table->enum('action', ['created', 'updated', 'deleted', 'viewed', 'uploaded', 'amended', 'approved', 'rejected', 'archived', 'restored', 'permission_changed', 'downloaded', 'expiry_notification_sent'])->index('idx_document_audit_logs_action_5e636c3d');
            $table->uuid('user_id')->nullable()->index('idx_document_audit_logs_user_id_87e6f880');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->longText('old_values')->nullable();
            $table->longText('new_values')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable()->index('idx_document_audit_logs_created_at_608e8e14');
            $table->timestamp('updated_at')->nullable();

            $table->index(['auditable_type', 'auditable_id'], 'idx_document_audit_logs_auditable_type_auditable_id_09209e0e');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_audit_logs');
    }
};
