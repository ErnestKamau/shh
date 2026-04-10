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
            $table->id();
            $table->unsignedBigInteger('risk_id');
            $table->unsignedBigInteger('assessment_id'); // CRITICAL LINK to risk_assessments
            $table->string('evaluation_number')->unique();
            
            // Evaluation Details
            $table->integer('risk_score')->nullable(); // Copied from assessment RPN
            $table->integer('acceptance_threshold_rpn')->nullable(); // 1-25
            $table->string('evaluation_result')->nullable(); // Acceptable, Tolerable, Unacceptable, Escalate
            $table->text('evaluation_notes')->nullable();
            
            // Evaluator Information
            $table->unsignedBigInteger('evaluated_by_user_id')->nullable();
            $table->string('evaluated_by')->nullable();
            $table->date('evaluation_date')->nullable();
            
            // Escalation (if Escalate selected)
            $table->unsignedBigInteger('escalated_to_user_id')->nullable();
            $table->text('escalation_reason')->nullable();
            
            // Version Control
            $table->boolean('is_current')->default(false);
            
            // Metadata
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('risk_id')->references('id')->on('risks')->onDelete('cascade');
            $table->foreign('assessment_id')->references('id')->on('risk_assessments')->onDelete('cascade');
            // Note: evaluated_by_user_id and escalated_to_user_id foreign keys removed due to users table ID type compatibility
            
            // Indexes
            $table->index(['risk_id', 'is_current']);
            $table->index(['assessment_id']);
            $table->index('evaluation_number');
            $table->index('evaluation_date');
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
