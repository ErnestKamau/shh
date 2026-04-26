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
            $table->uuid('workflow_id')->index('idx_equipment_disposal_approval_workflow_steps_workflo_8a20678b');
            $table->integer('step_order')->index('idx_equipment_disposal_approval_workflow_steps_step_or_99be8dc7');
            $table->string('step_name');
            $table->string('assignee_type');
            $table->bigInteger('assignee_id');
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->index(['assignee_type', 'assignee_id'], 'eqp_disp_wf_steps_assignee_idx');
            $table->foreign(['workflow_id'], 'fk_equipment_disposal_approval_workflow_steps_workflow_1b1d9c8b')->references(['id'])->on('equipment_disposal_approval_workflows')->onUpdate('no action')->onDelete('cascade');
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
