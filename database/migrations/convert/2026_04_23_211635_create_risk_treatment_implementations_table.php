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
        Schema::create('risk_treatment_implementations', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('risk_id');
            $table->uuid('treatment_plan_id')->index('idx_risk_treatment_implementations_treatment_plan_id_f67447e2');
            $table->uuid('assessment_id')->nullable()->index('idx_risk_treatment_implementations_assessment_id_b5903bf7');
            $table->string('implementation_number')->index('idx_risk_treatment_implementations_implementation_numb_616a6514');
            $table->text('actions_implemented')->nullable();
            $table->unsignedBigInteger('responsible_user_id')->nullable();
            $table->string('responsible_person')->nullable();
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->text('resources_used')->nullable();
            $table->string('status')->nullable()->index('idx_risk_treatment_implementations_status_65e3285a');
            $table->integer('observed_residual_risk')->nullable();
            $table->text('effectiveness_notes')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->string('approved_by')->nullable();
            $table->date('approval_date')->nullable();
            $table->date('next_review_date')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_risk_treatment_implementations_created_by_bce9e2ac');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_risk_treatment_implementations_company_id_ea84e51c');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['implementation_number']);
            $table->index(['risk_id', 'treatment_plan_id'], 'idx_risk_treatment_implementations_risk_id_treatment_p_b41e81b1');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_treatment_implementations');
    }
};
