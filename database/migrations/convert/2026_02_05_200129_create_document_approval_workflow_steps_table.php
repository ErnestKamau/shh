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
        if (Schema::hasTable('document_approval_workflow_steps')) {
            return;
        }
        Schema::create('document_approval_workflow_steps', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('workflow_id')->index('idx_document_approval_workflow_steps_workflow_id_7dfa1934');
            $table->integer('step_order')->index('idx_document_approval_workflow_steps_step_order_114a1d39');
            $table->string('step_name');
            $table->enum('step_type', ['authorization', 'approval']);
            $table->string('assignee_type');
            $table->bigInteger('assignee_id');
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->index(['assignee_type', 'assignee_id'], 'idx_document_approval_workflow_steps_assignee_type_ass_aec54c3b');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_approval_workflow_steps');
    }
};
