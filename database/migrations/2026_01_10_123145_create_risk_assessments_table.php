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
            $table->id();
            $table->unsignedBigInteger('risk_id');
            $table->string('assessment_number')->unique();
            
            // Assessment Details
            $table->unsignedBigInteger('likelihood_scale_id')->nullable();
            $table->integer('likelihood_score')->nullable(); // 1-5
            $table->unsignedBigInteger('severity_scale_id')->nullable();
            $table->integer('severity_score')->nullable(); // 1-5
            $table->integer('rpn')->nullable(); // Risk Priority Number = Likelihood × Severity
            $table->string('risk_level')->nullable(); // Critical, High, Medium, Low (calculated from RPN)
            $table->text('assessment_notes')->nullable();
            
            // Assessor Information
            $table->unsignedBigInteger('assessed_by_user_id')->nullable();
            $table->string('assessed_by')->nullable();
            $table->date('assessment_date')->nullable();
            
            // Version Control
            $table->boolean('is_current')->default(false);
            $table->integer('version')->default(1);
            $table->text('reassessment_reason')->nullable();
            
            // Metadata
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('risk_id')->references('id')->on('risks')->onDelete('cascade');
            $table->foreign('likelihood_scale_id')->references('id')->on('likelihood_scales')->onDelete('set null');
            $table->foreign('severity_scale_id')->references('id')->on('severity_scales')->onDelete('set null');
            // Note: assessed_by_user_id foreign key removed due to users table ID type compatibility
            
            // Indexes
            $table->index(['risk_id', 'is_current']);
            $table->index('assessment_number');
            $table->index('assessment_date');
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
