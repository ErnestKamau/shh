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
            $table->uuid('treatment_plan_id')->index('risk_treatment_implementations_treatment_plan_id_foreign');
            $table->uuid('assessment_id')->nullable()->index('risk_treatment_implementations_assessment_id_foreign');
            $table->string('implementation_number')->index('idx_risk_treatment_implementations_implementation_numb_029646fe');
            $table->text('actions_implemented')->nullable();
            $table->unsignedBigInteger('responsible_user_id')->nullable();
            $table->string('responsible_person')->nullable();
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->text('resources_used')->nullable();
            $table->string('status')->nullable()->index('idx_risk_treatment_implementations_status_1d2b4a4d');
            $table->integer('observed_residual_risk')->nullable();
            $table->text('effectiveness_notes')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->string('approved_by')->nullable();
            $table->date('approval_date')->nullable();
            $table->date('next_review_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->uuid('company_id')->default(0)->index('idx_risk_treatment_implementations_company_id_4c142b94');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['implementation_number']);
            $table->index(['risk_id', 'treatment_plan_id'], 'idx_risk_treatment_implementations_risk_id_treatment_p_cfb02eb3');
            $table->foreign(['assessment_id'], 'fk_risk_treatment_implementations_assessment_id_659b3668')->references(['id'])->on('risk_assessments')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['risk_id'], 'fk_risk_treatment_implementations_risk_id_832cc869')->references(['id'])->on('risks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['treatment_plan_id'], 'fk_risk_treatment_implementations_treatment_plan_id_f85d25ea')->references(['id'])->on('risk_treatment_plans')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_risk_treatment_implementations_company_id_67b49a1c')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

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
