<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentDisposalApprovalWorkflowStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('equipment_disposal_approval_workflow_steps', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('workflow_id');
            $table->integer('step_order');
            $table->string('step_name');
            $table->string('assignee_type'); // App\User, App\Models\Role, etc.
            $table->bigInteger('assignee_id');
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            // Foreign key
            $table->foreign('workflow_id')->references('id')->on('equipment_disposal_approval_workflows')->onDelete('cascade');

            // Indexes
            $table->index('workflow_id');
            $table->index('step_order');
            $table->index(['assignee_type', 'assignee_id'], 'eqp_disp_wf_steps_assignee_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposal_approval_workflow_steps');
    }
}

