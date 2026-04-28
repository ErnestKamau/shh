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
            $table->uuid('disposal_id')->index('idx_equipment_disposal_approvals_disposal_id_37047831');
            $table->integer('step')->index('idx_equipment_disposal_approvals_step_4688bbc4');
            $table->uuid('approver_id')->index('idx_equipment_disposal_approvals_approver_id_4bbdf398');
            $table->enum('decision', ['approve', 'reject'])->nullable()->index('idx_equipment_disposal_approvals_decision_d8d5bbfe');
            $table->text('remarks')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
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
