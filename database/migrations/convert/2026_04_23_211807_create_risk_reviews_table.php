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
        Schema::create('risk_reviews', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('risk_id');
            $table->string('review_number')->index('idx_risk_reviews_review_number_bc9b5221');
            $table->date('review_date');
            $table->string('reviewed_by')->nullable();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->string('review_type')->nullable();
            $table->text('review_reason')->nullable();
            $table->integer('review_likelihood_score')->nullable();
            $table->integer('review_severity_score')->nullable();
            $table->integer('review_rpn')->nullable();
            $table->string('review_risk_level')->nullable();
            $table->text('control_effectiveness_assessment')->nullable();
            $table->boolean('controls_effective')->nullable();
            $table->text('effectiveness_evidence')->nullable();
            $table->text('review_findings')->nullable();
            $table->text('opportunities_for_improvement')->nullable();
            $table->text('new_risks_identified')->nullable();
            $table->text('kpi_metrics')->nullable();
            $table->boolean('action_required')->default(false);
            $table->text('action_required_reason')->nullable();
            $table->boolean('reassess_risk')->default(false);
            $table->string('review_decision')->nullable();
            $table->text('decision_justification')->nullable();
            $table->date('next_review_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['review_number']);
            $table->index(['risk_id', 'review_date'], 'idx_risk_reviews_risk_id_review_date_7c6329ec');
            $table->foreign(['risk_id'], 'fk_risk_reviews_risk_id_d1946dee')->references(['id'])->on('risks')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_reviews');
    }
};
