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
        Schema::table('risk_treatment_plans', function (Blueprint $table) {
            // Priority
            $table->string('priority')->nullable()->after('treatment_type_name'); // High, Medium, Low
            
            // Resources
            $table->text('resources_required')->nullable()->after('expected_outcome'); // Budget, staff, equipment
            
            // Residual Risk Expected
            $table->integer('residual_risk_expected')->nullable()->after('resources_required'); // Estimated RPN after treatment
            
            // Approval Fields
            $table->unsignedBigInteger('approved_by_user_id')->nullable()->after('actual_completion_date');
            $table->string('approved_by')->nullable()->after('approved_by_user_id');
            $table->date('approval_date')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approval_date');
            
            // Note: approved_by_user_id foreign key removed due to users table ID type compatibility
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_treatment_plans', function (Blueprint $table) {
            $table->dropColumn([
                'priority',
                'resources_required',
                'residual_risk_expected',
                'approved_by_user_id',
                'approved_by',
                'approval_date',
                'approval_notes'
            ]);
        });
    }
};
