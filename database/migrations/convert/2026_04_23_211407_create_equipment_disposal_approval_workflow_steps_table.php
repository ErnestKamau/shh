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
        Schema::create('equipment_disposal_approval_workflow_steps', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('workflow_id')->index('idx_equipment_disposal_approval_workflow_steps_workflo_cc9b698a');
            $table->integer('step_order')->index('idx_equipment_disposal_approval_workflow_steps_step_or_4cedf71e');
            $table->string('step_name');
            $table->string('assignee_type');
            $table->bigInteger('assignee_id');
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->index(['assignee_type', 'assignee_id'], 'idx_equipment_disposal_approval_workflow_steps_assigne_333e0dd6');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposal_approval_workflow_steps');
    }
};
