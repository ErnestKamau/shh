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
        Schema::create('risk_evaluations', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('risk_id');
            $table->uuid('assessment_id')->index('idx_risk_evaluations_assessment_id_faee9fae');
            $table->string('evaluation_number')->index('idx_risk_evaluations_evaluation_number_b159fe21');
            $table->integer('risk_score')->nullable();
            $table->integer('acceptance_threshold_rpn')->nullable();
            $table->string('evaluation_result')->nullable();
            $table->text('evaluation_notes')->nullable();
            $table->unsignedBigInteger('evaluated_by_user_id')->nullable();
            $table->string('evaluated_by')->nullable();
            $table->date('evaluation_date')->nullable()->index('idx_risk_evaluations_evaluation_date_7084dd64');
            $table->unsignedBigInteger('escalated_to_user_id')->nullable();
            $table->text('escalation_reason')->nullable();
            $table->boolean('is_current')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->uuid('company_id')->default(0)->index('idx_risk_evaluations_company_id_922ec1c1');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['evaluation_number']);
            $table->index(['risk_id', 'is_current'], 'idx_risk_evaluations_risk_id_is_current_fee7cf55');
            $table->foreign(['assessment_id'], 'fk_risk_evaluations_assessment_id_b0ac8246')->references(['id'])->on('risk_assessments')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['risk_id'], 'fk_risk_evaluations_risk_id_6d1eade0')->references(['id'])->on('risks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_risk_evaluations_company_id_420be571')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_evaluations');
    }
};
