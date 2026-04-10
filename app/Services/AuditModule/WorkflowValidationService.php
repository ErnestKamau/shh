<?php

namespace App\Services\AuditModule;

use App\Models\AuditModule\Audit;
use App\Models\AuditModule\WorkflowActionRule;

class WorkflowValidationService
{
    /**
     * Validate workflow conditions for an audit based on validation rules
     */
    public function validateWorkflowConditions(Audit $audit, WorkflowActionRule $rule): array
    {
        $errors = [];
        $warnings = [];

        if (!$rule->conditions || empty($rule->conditions)) {
            return ['errors' => [], 'warnings' => []];
        }

        $conditions = $rule->conditions;

        // Validate findings requirements
        if (isset($conditions['require_findings']) && $conditions['require_findings']) {
            $findingsCount = $audit->findings()->count();
            if ($findingsCount === 0) {
                $errors[] = 'At least one finding must be recorded before proceeding.';
            }
        }

        // Validate minimum findings count
        if (isset($conditions['min_findings_count']) && $conditions['min_findings_count'] > 0) {
            $findingsCount = $audit->findings()->count();
            if ($findingsCount < $conditions['min_findings_count']) {
                $errors[] = "At least {$conditions['min_findings_count']} finding(s) must be recorded.";
            }
        }

        // Validate that findings requiring NCs have NCs raised
        if (isset($conditions['require_nc_for_findings']) && $conditions['require_nc_for_findings']) {
            $findingsRequiringNC = $audit->findings()
                ->whereHas('category', function($q) {
                    $q->where('requires_capa', true);
                })
                ->get();

            foreach ($findingsRequiringNC as $finding) {
                if (!$finding->hasNonConformance()) {
                    $errors[] = "Finding '{$finding->title}' requires a Non-Conformance to be raised.";
                }
            }
        }

        // Validate all NCs have root cause analysis
        if (isset($conditions['require_rca_for_all_ncs']) && $conditions['require_rca_for_all_ncs']) {
            $ncs = $audit->nonConformances()->get();
            foreach ($ncs as $nc) {
                if (!$nc->rootCauseAnalysis) {
                    $errors[] = "Non-Conformance '{$nc->nc_number}' requires Root Cause Analysis.";
                } elseif (isset($conditions['require_rca_approved']) && $conditions['require_rca_approved']) {
                    // Check if RCA is approved
                    $rca = $nc->rootCauseAnalysis;
                    if (!$rca->status || $rca->status->code !== 'APPROVE') {
                        $errors[] = "Root Cause Analysis for NC '{$nc->nc_number}' must be approved.";
                    }
                }
            }
        }

        // Validate all NCs have corrective actions assigned
        if (isset($conditions['require_capa_for_all_ncs']) && $conditions['require_capa_for_all_ncs']) {
            $ncs = $audit->nonConformances()->get();
            foreach ($ncs as $nc) {
                $capasCount = $nc->correctiveActions()->count();
                if ($capasCount === 0) {
                    $errors[] = "Non-Conformance '{$nc->nc_number}' requires at least one Corrective Action (CAPA) to be assigned.";
                } elseif (isset($conditions['min_capa_per_nc']) && $conditions['min_capa_per_nc'] > 0) {
                    if ($capasCount < $conditions['min_capa_per_nc']) {
                        $errors[] = "Non-Conformance '{$nc->nc_number}' requires at least {$conditions['min_capa_per_nc']} Corrective Action(s).";
                    }
                }
            }
        }

        // Validate all corrective actions are implemented
        if (isset($conditions['require_capa_implemented']) && $conditions['require_capa_implemented']) {
            $capas = $audit->nonConformances()
                ->with('correctiveActions')
                ->get()
                ->pluck('correctiveActions')
                ->flatten();

            foreach ($capas as $capa) {
                if (!$capa->implementation_date || $capa->status_name !== 'Implemented') {
                    $errors[] = "Corrective Action '{$capa->capa_number}' must be implemented before proceeding.";
                }
            }
        }
        
        // Validate all corrective actions are implemented (alternative key)
        if (isset($conditions['require_corrective_actions_implemented']) && $conditions['require_corrective_actions_implemented']) {
            $capas = $audit->nonConformances()
                ->with('correctiveActions')
                ->get()
                ->pluck('correctiveActions')
                ->flatten();

            foreach ($capas as $capa) {
                if (!$capa->implementation_date || $capa->status_name !== 'Implemented') {
                    $errors[] = "Corrective Action '{$capa->capa_number}' must be implemented before proceeding.";
                }
            }
        }

        // Validate all corrective actions are verified
        if (isset($conditions['require_capa_verified']) && $conditions['require_capa_verified']) {
            $capas = $audit->nonConformances()
                ->with('correctiveActions')
                ->get()
                ->pluck('correctiveActions')
                ->flatten();

            foreach ($capas as $capa) {
                if ($capa->status_name !== 'Verified' && $capa->status_name !== 'Closed') {
                    $errors[] = "Corrective Action '{$capa->capa_number}' must be verified before proceeding.";
                }
            }
        }
        
        // Validate all corrective actions are verified (alternative key)
        if (isset($conditions['require_corrective_actions_verified']) && $conditions['require_corrective_actions_verified']) {
            $capas = $audit->nonConformances()
                ->with('correctiveActions')
                ->get()
                ->pluck('correctiveActions')
                ->flatten();

            foreach ($capas as $capa) {
                if ($capa->status_name !== 'Verified' && $capa->status_name !== 'Closed') {
                    $errors[] = "Corrective Action '{$capa->capa_number}' must be verified before proceeding.";
                }
            }
        }
        
        // Validate minimum corrective actions count
        if (isset($conditions['min_corrective_actions_count']) && $conditions['min_corrective_actions_count'] > 0) {
            $totalCapas = $audit->nonConformances()
                ->with('correctiveActions')
                ->get()
                ->pluck('correctiveActions')
                ->flatten()
                ->count();
            
            if ($totalCapas < $conditions['min_corrective_actions_count']) {
                $errors[] = "At least {$conditions['min_corrective_actions_count']} corrective action(s) required. Currently have {$totalCapas}.";
            }
        }
        
        // Validate require corrective actions
        if (isset($conditions['require_corrective_actions']) && $conditions['require_corrective_actions']) {
            $totalCapas = $audit->nonConformances()
                ->with('correctiveActions')
                ->get()
                ->pluck('correctiveActions')
                ->flatten()
                ->count();
            
            if ($totalCapas === 0) {
                $errors[] = "At least one corrective action must be assigned.";
            }
        }
        
        // Validate require root cause analysis (alternative key)
        if (isset($conditions['require_root_cause_analysis']) && $conditions['require_root_cause_analysis']) {
            $ncs = $audit->nonConformances()->get();
            foreach ($ncs as $nc) {
                if (!$nc->rootCauseAnalysis) {
                    $errors[] = "Non-Conformance '{$nc->nc_number}' requires Root Cause Analysis.";
                }
            }
        }
        
        // Validate require root cause analysis approved (alternative key)
        if (isset($conditions['require_root_cause_analysis_approved']) && $conditions['require_root_cause_analysis_approved']) {
            $ncs = $audit->nonConformances()->get();
            foreach ($ncs as $nc) {
                if ($nc->rootCauseAnalysis) {
                    $rca = $nc->rootCauseAnalysis;
                    if (!$rca->status || $rca->status->code !== 'APPROVE') {
                        $errors[] = "Root Cause Analysis for NC '{$nc->nc_number}' must be approved.";
                    }
                } else {
                    $errors[] = "Non-Conformance '{$nc->nc_number}' requires Root Cause Analysis.";
                }
            }
        }
        
        // Validate attachments
        if (isset($conditions['require_attachments']) && $conditions['require_attachments']) {
            $attachmentsCount = $audit->attachments()->count();
            if ($attachmentsCount === 0) {
                $errors[] = 'At least one attachment must be uploaded before proceeding.';
            }
        }
        
        if (isset($conditions['min_attachments_count']) && $conditions['min_attachments_count'] > 0) {
            $attachmentsCount = $audit->attachments()->count();
            if ($attachmentsCount < $conditions['min_attachments_count']) {
                $errors[] = "At least {$conditions['min_attachments_count']} attachment(s) required. Currently have {$attachmentsCount}.";
            }
        }
        
        // Validate activity logs
        if (isset($conditions['require_activity_logs']) && $conditions['require_activity_logs']) {
            $activityCount = $audit->activityLogs()->count();
            if ($activityCount === 0) {
                $errors[] = 'At least one activity log entry must exist.';
            }
        }
        
        // Validate checklist item responses
        if (isset($conditions['require_checklist_item_responses']) && $conditions['require_checklist_item_responses']) {
            $responsesCount = $audit->checklistItemResponses()->count();
            if ($responsesCount === 0) {
                $errors[] = 'At least one checklist item response must exist.';
            }
        }
        
        if (isset($conditions['min_checklist_item_responses_count']) && $conditions['min_checklist_item_responses_count'] > 0) {
            $responsesCount = $audit->checklistItemResponses()->count();
            if ($responsesCount < $conditions['min_checklist_item_responses_count']) {
                $errors[] = "At least {$conditions['min_checklist_item_responses_count']} checklist item response(s) required. Currently have {$responsesCount}.";
            }
        }
        
        // Validate minimum checklists count
        if (isset($conditions['min_checklists_count']) && $conditions['min_checklists_count'] > 0) {
            $checklistsCount = $audit->checklists()->count();
            if ($checklistsCount < $conditions['min_checklists_count']) {
                $errors[] = "At least {$conditions['min_checklists_count']} checklist(s) required. Currently have {$checklistsCount}.";
            }
        }

        // Validate all corrective actions have owners
        if (isset($conditions['require_capa_owners']) && $conditions['require_capa_owners']) {
            $capas = $audit->nonConformances()
                ->with('correctiveActions')
                ->get()
                ->pluck('correctiveActions')
                ->flatten();

            foreach ($capas as $capa) {
                if (!$capa->action_owner_id) {
                    $errors[] = "Corrective Action '{$capa->capa_number}' must have an assigned owner.";
                }
            }
        }

        // Validate all corrective actions have due dates
        if (isset($conditions['require_capa_due_dates']) && $conditions['require_capa_due_dates']) {
            $capas = $audit->nonConformances()
                ->with('correctiveActions')
                ->get()
                ->pluck('correctiveActions')
                ->flatten();

            foreach ($capas as $capa) {
                if (!$capa->due_date) {
                    $errors[] = "Corrective Action '{$capa->capa_number}' must have a due date.";
                }
            }
        }

        // Dynamic validations - check for any other conditions
        foreach ($conditions as $key => $value) {
            if (in_array($key, [
                'require_findings', 'min_findings_count', 'require_nc_for_findings',
                'require_rca_for_all_ncs', 'require_rca_approved',
                'require_capa_for_all_ncs', 'min_capa_per_nc',
                'require_capa_implemented', 'require_capa_verified',
                'require_capa_owners', 'require_capa_due_dates'
            ])) {
                continue; // Already handled above
            }
            
            // Handle dynamic validations
            if ($value === true || (is_numeric($value) && $value > 0)) {
                $result = $this->validateDynamicCondition($audit, $key, $value);
                if (!$result['valid']) {
                    $errors[] = $result['message'];
                }
            }
        }
        
        // Custom validation rules (JSON-based)
        if (isset($conditions['custom_rules']) && is_array($conditions['custom_rules'])) {
            foreach ($conditions['custom_rules'] as $customRule) {
                $result = $this->validateCustomRule($audit, $customRule);
                if (!$result['valid']) {
                    $errors[] = $result['message'];
                }
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'valid' => empty($errors)
        ];
    }

    /**
     * Validate a dynamic condition
     */
    protected function validateDynamicCondition(Audit $audit, string $key, $value): array
    {
        // Handle require_* validations
        if (str_starts_with($key, 'require_')) {
            $relationshipName = str_replace('require_', '', $key);
            
            // Map relationship names to actual methods
            $relationshipMap = [
                'ncs' => 'nonConformances',
                'team_members' => 'teamMembers',
                'checklists' => 'checklists',
                'findings' => 'findings',
                'checklist_item_responses' => 'checklistItemResponses',
            ];
            
            // Convert snake_case to camelCase if not in map
            if (!isset($relationshipMap[$relationshipName])) {
                $parts = explode('_', $relationshipName);
                $methodName = $parts[0];
                for ($i = 1; $i < count($parts); $i++) {
                    $methodName .= ucfirst($parts[$i]);
                }
                // Handle plural to singular conversion
                if (str_ends_with($methodName, 's') && !in_array($methodName, ['findings', 'checklists'])) {
                    $methodName = rtrim($methodName, 's');
                }
            } else {
                $methodName = $relationshipMap[$relationshipName];
            }
            
            if (method_exists($audit, $methodName)) {
                $count = $audit->$methodName()->count();
                if ($count === 0) {
                    return [
                        'valid' => false,
                        'message' => ucwords(str_replace('_', ' ', $relationshipName)) . ' must be recorded before proceeding.'
                    ];
                }
            }
        }
        
        // Handle min_*_count validations
        if (str_starts_with($key, 'min_') && str_ends_with($key, '_count')) {
            $relationshipName = str_replace(['min_', '_count'], '', $key);
            
            $relationshipMap = [
                'ncs' => 'nonConformances',
                'team_members' => 'teamMembers',
                'checklists' => 'checklists',
                'findings' => 'findings',
                'checklist_item_responses' => 'checklistItemResponses',
            ];
            
            // Convert snake_case to camelCase if not in map
            if (!isset($relationshipMap[$relationshipName])) {
                $parts = explode('_', $relationshipName);
                $methodName = $parts[0];
                for ($i = 1; $i < count($parts); $i++) {
                    $methodName .= ucfirst($parts[$i]);
                }
                // Handle plural to singular conversion
                if (str_ends_with($methodName, 's') && !in_array($methodName, ['findings', 'checklists'])) {
                    $methodName = rtrim($methodName, 's');
                }
            } else {
                $methodName = $relationshipMap[$relationshipName];
            }
            
            if (method_exists($audit, $methodName) && is_numeric($value)) {
                $count = $audit->$methodName()->count();
                if ($count < $value) {
                    return [
                        'valid' => false,
                        'message' => "At least {$value} " . ucwords(str_replace('_', ' ', $relationshipName)) . " required. Currently have {$count}."
                    ];
                }
            }
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate a custom rule
     */
    protected function validateCustomRule(Audit $audit, array $rule): array
    {
        $type = $rule['type'] ?? null;
        $message = $rule['message'] ?? 'Validation failed.';

        switch ($type) {
            case 'count_findings':
                $min = $rule['min'] ?? 0;
                $count = $audit->findings()->count();
                return [
                    'valid' => $count >= $min,
                    'message' => $count < $min ? $message : ''
                ];

            case 'count_ncs':
                $min = $rule['min'] ?? 0;
                $count = $audit->nonConformances()->count();
                return [
                    'valid' => $count >= $min,
                    'message' => $count < $min ? $message : ''
                ];

            case 'count_capas':
                $min = $rule['min'] ?? 0;
                $count = $audit->nonConformances()
                    ->with('correctiveActions')
                    ->get()
                    ->pluck('correctiveActions')
                    ->flatten()
                    ->count();
                return [
                    'valid' => $count >= $min,
                    'message' => $count < $min ? $message : ''
                ];

            default:
                return ['valid' => true, 'message' => ''];
        }
    }

    /**
     * Get validation summary for an audit at a specific status
     */
    public function getValidationSummary(Audit $audit, string $statusName): array
    {
        $summary = [
            'findings_count' => $audit->findings()->count(),
            'ncs_count' => $audit->nonConformances()->count(),
            'ncs_with_rca' => $audit->nonConformances()->whereHas('rootCauseAnalysis')->count(),
            'ncs_with_capa' => $audit->nonConformances()->whereHas('correctiveActions')->count(),
            'capas_count' => $audit->nonConformances()
                ->with('correctiveActions')
                ->get()
                ->pluck('correctiveActions')
                ->flatten()
                ->count(),
            'capas_implemented' => $audit->nonConformances()
                ->with('correctiveActions')
                ->get()
                ->pluck('correctiveActions')
                ->flatten()
                ->where('status_name', 'Implemented')
                ->count(),
            'capas_verified' => $audit->nonConformances()
                ->with('correctiveActions')
                ->get()
                ->pluck('correctiveActions')
                ->flatten()
                ->whereIn('status_name', ['Verified', 'Closed'])
                ->count(),
        ];

        return $summary;
    }
}

