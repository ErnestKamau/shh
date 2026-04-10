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
        Schema::table('risks', function (Blueprint $table) {
            // Initial Comments
            $table->text('initial_comments')->nullable()->after('description'); // Separate from description
            
            // Lessons Learned
            $table->text('lessons_learned')->nullable()->after('closure_justification'); // For closure
            
            // Current Assessment and Evaluation References
            $table->unsignedBigInteger('current_assessment_id')->nullable()->after('workflow_step');
            $table->unsignedBigInteger('current_evaluation_id')->nullable()->after('current_assessment_id');
            
            // Foreign Keys
            $table->foreign('current_assessment_id')->references('id')->on('risk_assessments')->onDelete('set null');
            $table->foreign('current_evaluation_id')->references('id')->on('risk_evaluations')->onDelete('set null');
            
            // Indexes
            $table->index('current_assessment_id');
            $table->index('current_evaluation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risks', function (Blueprint $table) {
            $table->dropForeign(['current_assessment_id']);
            $table->dropForeign(['current_evaluation_id']);
            $table->dropIndex(['current_assessment_id']);
            $table->dropIndex(['current_evaluation_id']);
            $table->dropColumn([
                'initial_comments',
                'lessons_learned',
                'current_assessment_id',
                'current_evaluation_id'
            ]);
        });
    }
};
