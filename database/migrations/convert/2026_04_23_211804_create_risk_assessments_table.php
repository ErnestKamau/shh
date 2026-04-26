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
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('risk_id');
            $table->string('assessment_number')->index('idx_risk_assessments_assessment_number_a325cc75');
            $table->uuid('likelihood_scale_id')->nullable()->index('risk_assessments_likelihood_scale_id_foreign');
            $table->integer('likelihood_score')->nullable();
            $table->uuid('severity_scale_id')->nullable()->index('risk_assessments_severity_scale_id_foreign');
            $table->integer('severity_score')->nullable();
            $table->integer('rpn')->nullable();
            $table->string('risk_level')->nullable();
            $table->text('assessment_notes')->nullable();
            $table->unsignedBigInteger('assessed_by_user_id')->nullable();
            $table->string('assessed_by')->nullable();
            $table->date('assessment_date')->nullable()->index('idx_risk_assessments_assessment_date_f162c19d');
            $table->boolean('is_current')->default(false);
            $table->integer('version')->default(1);
            $table->text('reassessment_reason')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->uuid('company_id')->default(0)->index('idx_risk_assessments_company_id_41f40a0c');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['assessment_number']);
            $table->index(['risk_id', 'is_current'], 'idx_risk_assessments_risk_id_is_current_8542cd8d');
            $table->foreign(['likelihood_scale_id'], 'fk_risk_assessments_likelihood_scale_id_be10f1a5')->references(['id'])->on('likelihood_scales')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['risk_id'], 'fk_risk_assessments_risk_id_8f60d3c9')->references(['id'])->on('risks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['severity_scale_id'], 'fk_risk_assessments_severity_scale_id_3280c7b0')->references(['id'])->on('severity_scales')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_risk_assessments_company_id_6b228850')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

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
