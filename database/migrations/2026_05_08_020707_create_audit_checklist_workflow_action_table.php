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
        if (Schema::hasTable('audit_checklist_workflow_action')) {
            return;
        }

        Schema::create('audit_checklist_workflow_action', function (Blueprint $table) {
            $table->id();
            $table->uuid('audit_checklist_id')->index();
            $table->uuid('workflow_action_id')->index();
            $table->timestamps();
            
            $table->foreign('audit_checklist_id')
                ->references('id')
                ->on('audit_checklists')
                ->onDelete('cascade');
            
            $table->foreign('workflow_action_id')
                ->references('id')
                ->on('workflow_actions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_checklist_workflow_action');
    }
};
