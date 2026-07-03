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
        if (Schema::hasTable('risk_assessments')) {
            return;
        }
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('risk_id');
            $table->string('assessment_number')->index('idx_risk_assessments_assessment_number_16a85dae');
            $table->uuid('likelihood_scale_id')->nullable()->index('idx_risk_assessments_likelihood_scale_id_116490ab');
            $table->integer('likelihood_score')->nullable();
            $table->uuid('severity_scale_id')->nullable()->index('idx_risk_assessments_severity_scale_id_1e113e9c');
            $table->integer('severity_score')->nullable();
            $table->integer('rpn')->nullable();
            $table->string('risk_level')->nullable();
            $table->text('assessment_notes')->nullable();
            $table->unsignedBigInteger('assessed_by_user_id')->nullable();
            $table->string('assessed_by')->nullable();
            $table->date('assessment_date')->nullable()->index('idx_risk_assessments_assessment_date_0e353210');
            $table->boolean('is_current')->default(false);
            $table->integer('version')->default(1);
            $table->text('reassessment_reason')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_risk_assessments_created_by_e6d2af74');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_risk_assessments_company_id_18515187');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['assessment_number']);
            $table->index(['risk_id', 'is_current'], 'idx_risk_assessments_risk_id_is_current_0c55d490');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_assessments');
    }
};
