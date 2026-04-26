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
        Schema::create('equipment_disposal_audit_logs', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('disposal_id')->index('idx_equipment_disposal_audit_logs_disposal_id_304f5d75');
            $table->enum('action', ['created', 'updated', 'submitted', 'approved', 'rejected', 'executed', 'closed', 'file_uploaded', 'file_deleted', 'signature_added'])->index('idx_equipment_disposal_audit_logs_action_faba876a');
            $table->uuid('user_id')->nullable()->index('idx_equipment_disposal_audit_logs_user_id_a0e3d20f');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->longText('old_values')->nullable();
            $table->longText('new_values')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable()->index('idx_equipment_disposal_audit_logs_created_at_57bc5657');
            $table->timestamp('updated_at')->nullable();
            $table->foreign(['disposal_id'], 'fk_equipment_disposal_audit_logs_disposal_id_f88da1a2')->references(['id'])->on('equipment_disposals')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_equipment_disposal_audit_logs_user_id_fd800541')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposal_audit_logs');
    }
};
