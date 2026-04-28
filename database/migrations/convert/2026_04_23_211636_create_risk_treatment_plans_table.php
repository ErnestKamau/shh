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
        Schema::create('risk_treatment_plans', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('risk_id');
            $table->uuid('treatment_type_id')->nullable()->index('idx_risk_treatment_plans_treatment_type_id_692ee681');
            $table->string('treatment_type_name')->nullable();
            $table->string('priority')->nullable();
            $table->string('title');
            $table->text('description');
            $table->text('control_measures')->nullable();
            $table->text('expected_outcome')->nullable();
            $table->text('resources_required')->nullable();
            $table->integer('residual_risk_expected')->nullable();
            $table->string('responsible_person')->nullable();
            $table->unsignedBigInteger('responsible_user_id')->nullable();
            $table->string('department')->nullable();
            $table->date('target_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->string('approved_by')->nullable();
            $table->date('approval_date')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('extension_reason')->nullable();
            $table->string('implementation_status')->default('Planned');
            $table->text('implementation_notes')->nullable();
            $table->date('implementation_start_date')->nullable();
            $table->date('implementation_end_date')->nullable();
            $table->uuid('capa_id')->nullable()->index('idx_risk_treatment_plans_capa_id_e759c2df');
            $table->text('related_actions')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_risk_treatment_plans_created_by_54c2cedb');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['risk_id', 'implementation_status'], 'idx_risk_treatment_plans_risk_id_implementation_status_d89b83e3');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_treatment_plans');
    }
};
