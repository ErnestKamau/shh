<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds fields to support the new verification workflow:
     * - approver_order: Tracks sequence of approvals (1 = Technical Reviewer, 2 = Lab Manager)
     * - is_technical_reviewer: Identifies if this is the Technical Reviewer
     * - can_send_back_to_lab: Allows Lab Manager to send batch back for amendment
     */
    public function up(): void
    {
        Schema::table('batch_labsection_approval', function (Blueprint $table) {
            if (!Schema::hasColumn('batch_labsection_approval', 'approver_order')) {
                $table->integer('approver_order')->nullable()->default(0)->comment('1=Technical Reviewer, 2=Lab Manager');
            }
            
            if (!Schema::hasColumn('batch_labsection_approval', 'is_technical_reviewer')) {
                $table->boolean('is_technical_reviewer')->nullable()->default(false)->comment('Identifies if this approver is the Technical Reviewer');
            }
            
            if (!Schema::hasColumn('batch_labsection_approval', 'can_send_back_to_lab')) {
                $table->boolean('can_send_back_to_lab')->nullable()->default(false)->comment('Only Lab Manager can send back for amendment');
            }
            
            if (!Schema::hasColumn('batch_labsection_approval', 'approver_type')) {
                $table->string('approver_type')->nullable()->comment('e.g., Technical Reviewer, Lab Manager');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_labsection_approval', function (Blueprint $table) {
            $table->dropColumn([
                'approver_order',
                'is_technical_reviewer',
                'can_send_back_to_lab',
                'approver_type'
            ]);
        });
    }
};
