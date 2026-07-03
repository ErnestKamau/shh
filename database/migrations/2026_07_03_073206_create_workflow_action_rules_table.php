<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workflow_action_rules')) {
            return;
        }

        Schema::create('workflow_action_rules', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('workflow_action_id')->index();
            $table->uuid('from_status_id')->nullable()->index();
            $table->string('from_status_name')->nullable();
            $table->uuid('target_status_id')->nullable()->index();
            $table->string('target_status_name')->nullable();
            $table->string('target_type')->default('next');
            $table->boolean('validate_progression')->default(true);
            $table->json('validation_rules')->nullable();
            $table->json('conditions')->nullable();
            $table->text('success_message')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order_index')->default(0);
            $table->uuid('company_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['id']);
            $table->index(['company_id', 'workflow_action_id', 'is_active'], 'idx_workflow_action_rules_company_action_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_action_rules');
    }
};
