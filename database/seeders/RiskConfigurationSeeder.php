<?php

namespace Database\Seeders;

use App\Models\AuditModule\LikelihoodScale;
use App\Models\AuditModule\SeverityScale;
use App\Models\RiskManagement\RiskAcceptanceCriteria;
use App\Models\RiskManagement\RiskConfigurationOption;
use App\Models\RiskManagement\RiskLevelThreshold;
use App\Models\RiskManagement\RiskReviewFrequency;
use App\Models\RiskManagement\RiskScoringConfig;
use Illuminate\Database\Seeder;

class RiskConfigurationSeeder extends Seeder
{
    /**
     * Seed risk module settings, scales, thresholds, and dropdown options.
     *
     * @param  string|null  $companyId  Company UUID; null seeds global defaults only.
     */
    public function run(?string $companyId = null): void
    {
        $this->seedScoringConfig($companyId);
        $this->seedAcceptanceCriteria($companyId);
        $this->seedRiskLevelThresholds($companyId);
        $this->seedReviewFrequencies($companyId);
        $this->seedLikelihoodAndSeverityScales($companyId);
        $this->seedConfigurationOptions($companyId);
    }

    private function seedScoringConfig(?string $companyId): void
    {
        RiskScoringConfig::updateOrCreate(
            ['code' => 'RISK_RPN_DEFAULT'],
            [
                'name' => 'Standard RPN (Likelihood × Severity)',
                'scoring_method' => 'multiplicative',
                'formula' => 'likelihood * severity',
                'max_likelihood_score' => 5,
                'max_severity_score' => 5,
                'description' => 'Default 5×5 risk matrix scoring.',
                'is_default' => true,
                'is_active' => true,
                'company_id' => $companyId,
            ]
        );
    }

    private function seedAcceptanceCriteria(?string $companyId): void
    {
        RiskAcceptanceCriteria::updateOrCreate(
            ['code' => 'DEFAULT_ACCEPT'],
            [
                'name' => 'Standard Acceptance',
                'threshold_rpn' => 15,
                'criteria_description' => 'Risks at or below RPN 15 are generally acceptable with monitoring.',
                'requires_treatment_plan' => true,
                'requires_monitoring' => true,
                'can_skip_treatment' => false,
                'applicable_categories' => null,
                'is_default' => true,
                'is_active' => true,
                'company_id' => $companyId,
            ]
        );
    }

    private function seedRiskLevelThresholds(?string $companyId): void
    {
        $thresholds = [
            ['risk_level' => 'Low', 'min_rpn' => 1, 'max_rpn' => 4, 'color_code' => '#28a745', 'order_index' => 1],
            ['risk_level' => 'Medium', 'min_rpn' => 5, 'max_rpn' => 9, 'color_code' => '#ffc107', 'order_index' => 2],
            ['risk_level' => 'High', 'min_rpn' => 10, 'max_rpn' => 15, 'color_code' => '#fd7e14', 'order_index' => 3],
            ['risk_level' => 'Critical', 'min_rpn' => 16, 'max_rpn' => 25, 'color_code' => '#dc3545', 'order_index' => 4],
        ];

        foreach ($thresholds as $row) {
            RiskLevelThreshold::updateOrCreate(
                ['risk_level' => $row['risk_level'], 'company_id' => $companyId],
                array_merge($row, [
                    'description' => "RPN {$row['min_rpn']}–{$row['max_rpn']}",
                    'is_active' => true,
                ])
            );
        }
    }

    private function seedReviewFrequencies(?string $companyId): void
    {
        $frequencies = [
            ['risk_level' => 'Low', 'frequency_days' => 365, 'frequency_label' => 'Annual'],
            ['risk_level' => 'Medium', 'frequency_days' => 180, 'frequency_label' => 'Semi-annual'],
            ['risk_level' => 'High', 'frequency_days' => 90, 'frequency_label' => 'Quarterly'],
            ['risk_level' => 'Critical', 'frequency_days' => 30, 'frequency_label' => 'Monthly'],
        ];

        foreach ($frequencies as $row) {
            RiskReviewFrequency::updateOrCreate(
                ['risk_level' => $row['risk_level'], 'company_id' => $companyId],
                [
                    'frequency_days' => $row['frequency_days'],
                    'frequency_label' => $row['frequency_label'],
                    'description' => "Review every {$row['frequency_label']} for {$row['risk_level']} risks.",
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedLikelihoodAndSeverityScales(?string $companyId): void
    {
        $labels = [
            1 => ['Rare', 'Negligible', '#28a745', '#d4edda'],
            2 => ['Unlikely', 'Minor', '#8bc34a', '#e8f5e9'],
            3 => ['Possible', 'Moderate', '#ffc107', '#fff3cd'],
            4 => ['Likely', 'Major', '#fd7e14', '#ffe5d0'],
            5 => ['Almost Certain', 'Catastrophic', '#dc3545', '#f8d7da'],
        ];

        foreach ($labels as $score => [$likelihoodName, $severityName, $likelihoodColor, $severityColor]) {
            LikelihoodScale::updateOrCreate(
                ['code' => 'RISK_LIK_'.$score],
                [
                    'name' => $likelihoodName,
                    'description' => "Likelihood score {$score} – {$likelihoodName}",
                    'score' => $score,
                    'color_code' => $likelihoodColor,
                    'order_index' => $score,
                    'is_active' => true,
                    'company_id' => $companyId,
                ]
            );

            SeverityScale::updateOrCreate(
                ['code' => 'RISK_SEV_'.$score],
                [
                    'name' => $severityName,
                    'description' => "Severity score {$score} – {$severityName}",
                    'score' => $score,
                    'color_code' => $severityColor,
                    'order_index' => $score,
                    'is_active' => true,
                    'company_id' => $companyId,
                ]
            );
        }
    }

    private function seedConfigurationOptions(?string $companyId): void
    {
        $options = [
            // Evaluation results (RPN bands on 5×5 matrix)
            [
                'option_type' => 'evaluation_result',
                'code' => 'acceptable',
                'name' => 'Acceptable',
                'color_code' => '#28a745',
                'order_index' => 1,
                'metadata' => [
                    'rpn_min' => 1,
                    'rpn_max' => 8,
                    'workflow_step' => 7,
                    'required_actions' => 'Proceed to risk monitoring.',
                ],
            ],
            [
                'option_type' => 'evaluation_result',
                'code' => 'tolerable',
                'name' => 'Tolerable',
                'color_code' => '#ffc107',
                'order_index' => 2,
                'metadata' => [
                    'rpn_min' => 9,
                    'rpn_max' => 15,
                    'workflow_step' => 7,
                    'required_actions' => 'Monitor with defined review frequency.',
                ],
            ],
            [
                'option_type' => 'evaluation_result',
                'code' => 'unacceptable',
                'name' => 'Unacceptable',
                'color_code' => '#dc3545',
                'order_index' => 3,
                'metadata' => [
                    'rpn_min' => 16,
                    'rpn_max' => 25,
                    'workflow_step' => 4,
                    'required_actions' => 'Develop and approve a treatment plan.',
                ],
            ],
            // Risk levels (display)
            ['option_type' => 'risk_level', 'code' => 'low', 'name' => 'Low', 'color_code' => '#28a745', 'order_index' => 1, 'metadata' => []],
            ['option_type' => 'risk_level', 'code' => 'medium', 'name' => 'Medium', 'color_code' => '#ffc107', 'order_index' => 2, 'metadata' => []],
            ['option_type' => 'risk_level', 'code' => 'high', 'name' => 'High', 'color_code' => '#fd7e14', 'order_index' => 3, 'metadata' => []],
            ['option_type' => 'risk_level', 'code' => 'critical', 'name' => 'Critical', 'color_code' => '#dc3545', 'order_index' => 4, 'metadata' => []],
            // Implementation statuses
            ['option_type' => 'implementation_status', 'code' => 'planned', 'name' => 'Planned', 'color_code' => '#6c757d', 'order_index' => 1, 'metadata' => []],
            ['option_type' => 'implementation_status', 'code' => 'in_progress', 'name' => 'In Progress', 'color_code' => '#17a2b8', 'order_index' => 2, 'metadata' => []],
            ['option_type' => 'implementation_status', 'code' => 'completed', 'name' => 'Completed', 'color_code' => '#28a745', 'order_index' => 3, 'metadata' => []],
            ['option_type' => 'implementation_status', 'code' => 'on_hold', 'name' => 'On Hold', 'color_code' => '#ffc107', 'order_index' => 4, 'metadata' => []],
            ['option_type' => 'implementation_status', 'code' => 'cancelled', 'name' => 'Cancelled', 'color_code' => '#dc3545', 'order_index' => 5, 'metadata' => []],
            // Treatment priorities
            ['option_type' => 'treatment_priority', 'code' => 'low', 'name' => 'Low', 'color_code' => '#28a745', 'order_index' => 1, 'metadata' => []],
            ['option_type' => 'treatment_priority', 'code' => 'medium', 'name' => 'Medium', 'color_code' => '#ffc107', 'order_index' => 2, 'metadata' => []],
            ['option_type' => 'treatment_priority', 'code' => 'high', 'name' => 'High', 'color_code' => '#fd7e14', 'order_index' => 3, 'metadata' => []],
            ['option_type' => 'treatment_priority', 'code' => 'critical', 'name' => 'Critical', 'color_code' => '#dc3545', 'order_index' => 4, 'metadata' => []],
            // Review types
            ['option_type' => 'review_type', 'code' => 'scheduled', 'name' => 'Scheduled', 'color_code' => '#17a2b8', 'order_index' => 1, 'metadata' => []],
            ['option_type' => 'review_type', 'code' => 'triggered', 'name' => 'Triggered', 'color_code' => '#fd7e14', 'order_index' => 2, 'metadata' => []],
            ['option_type' => 'review_type', 'code' => 'periodic', 'name' => 'Periodic', 'color_code' => '#6f42c1', 'order_index' => 3, 'metadata' => []],
            // Review decisions
            ['option_type' => 'review_decision', 'code' => 'continue_monitoring', 'name' => 'Continue Monitoring', 'color_code' => '#17a2b8', 'order_index' => 1, 'metadata' => []],
            ['option_type' => 'review_decision', 'code' => 'close_risk', 'name' => 'Close Risk', 'color_code' => '#28a745', 'order_index' => 2, 'metadata' => []],
            ['option_type' => 'review_decision', 'code' => 'additional_controls', 'name' => 'Additional Controls Needed', 'color_code' => '#fd7e14', 'order_index' => 3, 'metadata' => []],
            // Closure types
            ['option_type' => 'closure_type', 'code' => 'eliminated', 'name' => 'Eliminated', 'color_code' => '#28a745', 'order_index' => 1, 'metadata' => []],
            ['option_type' => 'closure_type', 'code' => 'controlled', 'name' => 'Controlled', 'color_code' => '#17a2b8', 'order_index' => 2, 'metadata' => []],
            ['option_type' => 'closure_type', 'code' => 'accepted', 'name' => 'Accepted', 'color_code' => '#ffc107', 'order_index' => 3, 'metadata' => []],
            // Score picklists (1–5)
            ...collect(range(1, 5))->flatMap(function (int $score) {
                return [
                    [
                        'option_type' => 'likelihood_score',
                        'code' => (string) $score,
                        'name' => (string) $score,
                        'color_code' => '#17a2b8',
                        'order_index' => $score,
                        'metadata' => [],
                    ],
                    [
                        'option_type' => 'severity_score',
                        'code' => (string) $score,
                        'name' => (string) $score,
                        'color_code' => '#fd7e14',
                        'order_index' => $score,
                        'metadata' => [],
                    ],
                ];
            })->all(),
        ];

        foreach ($options as $row) {
            $metadata = $row['metadata'];
            unset($row['metadata']);

            RiskConfigurationOption::updateOrCreate(
                [
                    'option_type' => $row['option_type'],
                    'code' => $row['code'],
                    'company_id' => $companyId,
                ],
                array_merge($row, [
                    'description' => $row['description'] ?? null,
                    'is_active' => true,
                    'metadata' => ! empty($metadata) ? $metadata : null,
                    'company_id' => $companyId,
                ])
            );
        }
    }
}
