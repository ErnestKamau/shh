<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentApprovalWorkflowStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('document_approval_workflow_steps', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('workflow_id');
            $table->integer('step_order');
            $table->string('step_name');
            $table->enum('step_type', ['authorization', 'approval']);
            $table->string('assignee_type');
            $table->bigInteger('assignee_id');
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            // Foreign key
            $table->foreign('workflow_id')->references('id')->on('document_approval_workflows')->onDelete('cascade');

            // Indexes
            $table->index('workflow_id');
            $table->index('step_order');
            $table->index(['assignee_type', 'assignee_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('document_approval_workflow_steps');
    }
}

