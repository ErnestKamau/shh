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
        Schema::create('equipment_disposal_approvals', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('disposal_id')->index('idx_equipment_disposal_approvals_disposal_id_cb1a0707');
            $table->integer('step')->index('idx_equipment_disposal_approvals_step_c3c82004');
            $table->uuid('approver_id')->index('idx_equipment_disposal_approvals_approver_id_8e9402db');
            $table->enum('decision', ['approve', 'reject'])->nullable()->index('idx_equipment_disposal_approvals_decision_153713c3');
            $table->text('remarks')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->foreign(['approver_id'], 'fk_equipment_disposal_approvals_approver_id_6bfb8073')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['disposal_id'], 'fk_equipment_disposal_approvals_disposal_id_601a65ca')->references(['id'])->on('equipment_disposals')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposal_approvals');
    }
};
