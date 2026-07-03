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
            $table->uuid('disposal_id')->index('idx_equipment_disposal_audit_logs_disposal_id_eae610c4');
            $table->enum('action', ['created', 'updated', 'submitted', 'approved', 'rejected', 'executed', 'closed', 'file_uploaded', 'file_deleted', 'signature_added'])->index('idx_equipment_disposal_audit_logs_action_20b7e90b');
            $table->uuid('user_id')->nullable()->index('idx_equipment_disposal_audit_logs_user_id_2e558972');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->longText('old_values')->nullable();
            $table->longText('new_values')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable()->index('idx_equipment_disposal_audit_logs_created_at_ecca01c5');
            $table->timestamp('updated_at')->nullable();
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
