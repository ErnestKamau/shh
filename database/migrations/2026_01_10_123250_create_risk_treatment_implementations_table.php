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
            $table->id();
            $table->unsignedBigInteger('risk_id');
            $table->unsignedBigInteger('treatment_plan_id');
            $table->unsignedBigInteger('assessment_id')->nullable(); // Reference original assessment
            $table->string('implementation_number')->unique();
            
            // Implementation Details
            $table->text('actions_implemented')->nullable(); // What was actually done
            $table->unsignedBigInteger('responsible_user_id')->nullable();
            $table->string('responsible_person')->nullable();
            
            // Dates
            $table->date('start_date')->nullable(); // Actual start
            $table->date('completion_date')->nullable(); // Actual completion
            
            // Resources and Effectiveness
            $table->text('resources_used')->nullable(); // Actual vs planned
            $table->string('status')->nullable(); // Planned, In Progress, Completed, Delayed, Cancelled
            $table->integer('observed_residual_risk')->nullable(); // Actual RPN after implementation
            $table->text('effectiveness_notes')->nullable();
            
            // Approval
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->string('approved_by')->nullable();
            $table->date('approval_date')->nullable();
            
            // Next Review
            $table->date('next_review_date')->nullable();
            
            // Metadata
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('risk_id')->references('id')->on('risks')->onDelete('cascade');
            $table->foreign('treatment_plan_id')->references('id')->on('risk_treatment_plans')->onDelete('cascade');
            $table->foreign('assessment_id')->references('id')->on('risk_assessments')->onDelete('set null');
            // Note: responsible_user_id and approved_by_user_id foreign keys removed due to users table ID type compatibility
            
            // Indexes
            $table->index(['risk_id', 'treatment_plan_id']);
            $table->index('implementation_number');
            $table->index('status');
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
