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
        if (Schema::hasTable('risk_evaluations')) {
            return;
        }
        Schema::create('risk_evaluations', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('risk_id');
            $table->uuid('assessment_id')->index('idx_risk_evaluations_assessment_id_5317914e');
            $table->string('evaluation_number')->index('idx_risk_evaluations_evaluation_number_72975f2f');
            $table->integer('risk_score')->nullable();
            $table->integer('acceptance_threshold_rpn')->nullable();
            $table->string('evaluation_result')->nullable();
            $table->text('evaluation_notes')->nullable();
            $table->unsignedBigInteger('evaluated_by_user_id')->nullable();
            $table->string('evaluated_by')->nullable();
            $table->date('evaluation_date')->nullable()->index('idx_risk_evaluations_evaluation_date_82ccf16b');
            $table->unsignedBigInteger('escalated_to_user_id')->nullable();
            $table->text('escalation_reason')->nullable();
            $table->boolean('is_current')->default(false);
            $table->uuid('created_by')->nullable()->index('idx_risk_evaluations_created_by_c342464b');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_risk_evaluations_company_id_f8184a51');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['evaluation_number']);
            $table->index(['risk_id', 'is_current'], 'idx_risk_evaluations_risk_id_is_current_4da4f3a9');

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
