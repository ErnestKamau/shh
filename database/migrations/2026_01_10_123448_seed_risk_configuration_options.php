<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $companyId = 0; // Global defaults
        
        // 1. Risk Levels
        $riskLevels = [
            ['option_type' => 'risk_level', 'code' => 'critical', 'name' => 'Critical', 'color_code' => '#dc3545', 'order_index' => 1],
            ['option_type' => 'risk_level', 'code' => 'high', 'name' => 'High', 'color_code' => '#fd7e14', 'order_index' => 2],
            ['option_type' => 'risk_level', 'code' => 'medium', 'name' => 'Medium', 'color_code' => '#ffc107', 'order_index' => 3],
            ['option_type' => 'risk_level', 'code' => 'low', 'name' => 'Low', 'color_code' => '#28a745', 'order_index' => 4],
        ];
        
        // 2. Evaluation Results (with RPN ranges, workflow steps, and required actions in metadata)
        // Note: Current workflow step is 4 (Under Evaluation), so next steps should be after step 4
        $evaluationResults = [
            [
                'option_type' => 'evaluation_result', 
                'code' => 'acceptable', 
                'name' => 'Acceptable', 
                'color_code' => '#28a745', 
                'order_index' => 1,
                'metadata' => json_encode([
                    'required_actions' => 'Record formal risk acceptance, Document justification, Assign monitoring owner, Set review date',
                    'rpn_min' => 1,
                    'rpn_max' => 10,
                    'workflow_step' => 7  // Next step: Risk Monitoring (step 7)
                ])
            ],
            [
                'option_type' => 'evaluation_result', 
                'code' => 'tolerable', 
                'name' => 'Tolerable', 
                'color_code' => '#ffc107', 
                'order_index' => 2,
                'metadata' => json_encode([
                    'required_actions' => 'Document risk tolerance justification, Increase monitoring frequency, Set mandatory review date, Optional treatment plan',
                    'rpn_min' => 11,
                    'rpn_max' => 20,
                    'workflow_step' => 7  // Next step: Risk Monitoring (step 7)
                ])
            ],
            [
                'option_type' => 'evaluation_result', 
                'code' => 'unacceptable', 
                'name' => 'Unacceptable', 
                'color_code' => '#dc3545', 
                'order_index' => 3,
                'metadata' => json_encode([
                    'required_actions' => 'Create treatment plan, Assign responsible person, Define target completion dates, Implement controls, Escalate if high impact',
                    'rpn_min' => 21,
                    'rpn_max' => 50,
                    'workflow_step' => 5  // Next step: Treatment Planning (step 5)
                ])
            ],
            [
                'option_type' => 'evaluation_result', 
                'code' => 'escalate', 
                'name' => 'Escalate', 
                'color_code' => '#6f42c1', 
                'order_index' => 4,
                'metadata' => json_encode([
                    'required_actions' => 'Escalate to senior management, Provide detailed justification, Record management decision',
                    'rpn_min' => 51,
                    'rpn_max' => 100,
                    'workflow_step' => 5  // Next step: Treatment Planning (step 5) - escalated risks need treatment
                ])
            ],
        ];
        
        // 3. Implementation Statuses
        $implementationStatuses = [
            ['option_type' => 'implementation_status', 'code' => 'planned', 'name' => 'Planned', 'color_code' => '#6c757d', 'order_index' => 1],
            ['option_type' => 'implementation_status', 'code' => 'in_progress', 'name' => 'In Progress', 'color_code' => '#17a2b8', 'order_index' => 2],
            ['option_type' => 'implementation_status', 'code' => 'completed', 'name' => 'Completed', 'color_code' => '#28a745', 'order_index' => 3],
            ['option_type' => 'implementation_status', 'code' => 'on_hold', 'name' => 'On Hold', 'color_code' => '#ffc107', 'order_index' => 4],
            ['option_type' => 'implementation_status', 'code' => 'cancelled', 'name' => 'Cancelled', 'color_code' => '#dc3545', 'order_index' => 5],
            ['option_type' => 'implementation_status', 'code' => 'delayed', 'name' => 'Delayed', 'color_code' => '#fd7e14', 'order_index' => 6],
        ];
        
        // 4. Treatment Priorities
        $treatmentPriorities = [
            ['option_type' => 'treatment_priority', 'code' => 'high', 'name' => 'High', 'color_code' => '#dc3545', 'order_index' => 1],
            ['option_type' => 'treatment_priority', 'code' => 'medium', 'name' => 'Medium', 'color_code' => '#ffc107', 'order_index' => 2],
            ['option_type' => 'treatment_priority', 'code' => 'low', 'name' => 'Low', 'color_code' => '#28a745', 'order_index' => 3],
        ];
        
        // 5. Review Types
        $reviewTypes = [
            ['option_type' => 'review_type', 'code' => 'scheduled', 'name' => 'Scheduled', 'color_code' => null, 'order_index' => 1],
            ['option_type' => 'review_type', 'code' => 'triggered', 'name' => 'Triggered', 'color_code' => null, 'order_index' => 2],
            ['option_type' => 'review_type', 'code' => 'periodic', 'name' => 'Periodic', 'color_code' => null, 'order_index' => 3],
        ];
        
        // 6. Review Decisions
        $reviewDecisions = [
            ['option_type' => 'review_decision', 'code' => 'continue_monitoring', 'name' => 'Continue Monitoring', 'color_code' => null, 'order_index' => 1],
            ['option_type' => 'review_decision', 'code' => 'close_risk', 'name' => 'Close Risk', 'color_code' => null, 'order_index' => 2],
            ['option_type' => 'review_decision', 'code' => 'additional_controls_needed', 'name' => 'Additional Controls Needed', 'color_code' => null, 'order_index' => 3],
        ];
        
        // 7. Closure Types
        $closureTypes = [
            ['option_type' => 'closure_type', 'code' => 'eliminated', 'name' => 'Eliminated', 'color_code' => '#28a745', 'order_index' => 1],
            ['option_type' => 'closure_type', 'code' => 'controlled', 'name' => 'Controlled', 'color_code' => '#17a2b8', 'order_index' => 2],
            ['option_type' => 'closure_type', 'code' => 'accepted', 'name' => 'Accepted', 'color_code' => '#6c757d', 'order_index' => 3],
        ];
        
        // 8. Likelihood Scores
        $likelihoodScores = [
            ['option_type' => 'likelihood_score', 'code' => '1', 'name' => 'Score 1', 'color_code' => null, 'order_index' => 1, 'metadata' => json_encode(['value' => 1, 'label' => 'Very Low'])],
            ['option_type' => 'likelihood_score', 'code' => '2', 'name' => 'Score 2', 'color_code' => null, 'order_index' => 2, 'metadata' => json_encode(['value' => 2, 'label' => 'Low'])],
            ['option_type' => 'likelihood_score', 'code' => '3', 'name' => 'Score 3', 'color_code' => null, 'order_index' => 3, 'metadata' => json_encode(['value' => 3, 'label' => 'Medium'])],
            ['option_type' => 'likelihood_score', 'code' => '4', 'name' => 'Score 4', 'color_code' => null, 'order_index' => 4, 'metadata' => json_encode(['value' => 4, 'label' => 'High'])],
            ['option_type' => 'likelihood_score', 'code' => '5', 'name' => 'Score 5', 'color_code' => null, 'order_index' => 5, 'metadata' => json_encode(['value' => 5, 'label' => 'Very High'])],
        ];
        
        // 9. Severity Scores
        $severityScores = [
            ['option_type' => 'severity_score', 'code' => '1', 'name' => 'Score 1', 'color_code' => null, 'order_index' => 1, 'metadata' => json_encode(['value' => 1, 'label' => 'Very Low'])],
            ['option_type' => 'severity_score', 'code' => '2', 'name' => 'Score 2', 'color_code' => null, 'order_index' => 2, 'metadata' => json_encode(['value' => 2, 'label' => 'Low'])],
            ['option_type' => 'severity_score', 'code' => '3', 'name' => 'Score 3', 'color_code' => null, 'order_index' => 3, 'metadata' => json_encode(['value' => 3, 'label' => 'Medium'])],
            ['option_type' => 'severity_score', 'code' => '4', 'name' => 'Score 4', 'color_code' => null, 'order_index' => 4, 'metadata' => json_encode(['value' => 4, 'label' => 'High'])],
            ['option_type' => 'severity_score', 'code' => '5', 'name' => 'Score 5', 'color_code' => null, 'order_index' => 5, 'metadata' => json_encode(['value' => 5, 'label' => 'Very High'])],
        ];
        
        // 10. Acceptance Threshold RPN (with ranges)
        $acceptanceThresholds = [
            [
                'option_type' => 'acceptance_threshold_rpn', 
                'code' => '1-10', 
                'name' => '1 to 10 RPN', 
                'color_code' => '#28a745', 
                'order_index' => 1,
                'metadata' => json_encode(['min' => 1, 'max' => 10])
            ],
            [
                'option_type' => 'acceptance_threshold_rpn', 
                'code' => '11-20', 
                'name' => '11 to 20 RPN', 
                'color_code' => '#ffc107', 
                'order_index' => 2,
                'metadata' => json_encode(['min' => 11, 'max' => 20])
            ],
            [
                'option_type' => 'acceptance_threshold_rpn', 
                'code' => '21-30', 
                'name' => '21 to 30 RPN', 
                'color_code' => '#fd7e14', 
                'order_index' => 3,
                'metadata' => json_encode(['min' => 21, 'max' => 30])
            ],
            [
                'option_type' => 'acceptance_threshold_rpn', 
                'code' => '31-50', 
                'name' => '31 to 50 RPN', 
                'color_code' => '#dc3545', 
                'order_index' => 4,
                'metadata' => json_encode(['min' => 31, 'max' => 50])
            ],
            [
                'option_type' => 'acceptance_threshold_rpn', 
                'code' => '51-100', 
                'name' => '51 to 100 RPN', 
                'color_code' => '#8b0000', 
                'order_index' => 5,
                'metadata' => json_encode(['min' => 51, 'max' => 100])
            ],
        ];
        
        // Combine all options
        $allOptions = array_merge(
            $riskLevels,
            $evaluationResults,
            $implementationStatuses,
            $treatmentPriorities,
            $reviewTypes,
            $reviewDecisions,
            $closureTypes,
            $likelihoodScores,
            $severityScores,
            $acceptanceThresholds
        );
        
        // Insert options
        foreach ($allOptions as $option) {
            DB::table('risk_configuration_options')->insert([
                'option_type' => $option['option_type'],
                'code' => $option['code'],
                'name' => $option['name'],
                'color_code' => $option['color_code'] ?? null,
                'order_index' => $option['order_index'],
                'is_active' => true,
                'metadata' => $option['metadata'] ?? null,
                'company_id' => $companyId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('risk_configuration_options')->where('company_id', 0)->delete();
    }
};
