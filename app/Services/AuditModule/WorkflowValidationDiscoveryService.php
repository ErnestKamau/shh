<?php

namespace App\Services\AuditModule;

use App\Models\AuditModule\Audit;
use ReflectionClass;
use ReflectionMethod;

class WorkflowValidationDiscoveryService
{
    /**
     * Discover all audit-related models and their validation options
     */
    public function discoverValidationOptions(): array
    {
        $auditModel = new Audit();
        $reflection = new ReflectionClass($auditModel);
        
        $validationOptions = [];
        
        // Discover direct relationships from Audit model
        $relationships = $this->discoverRelationships($auditModel);
        
        // Build validation options based on discovered relationships
        foreach ($relationships as $relationshipName => $relationshipInfo) {
            $validationOptions = array_merge($validationOptions, $this->buildValidationOptionsForRelationship($relationshipName, $relationshipInfo));
            
            // Discover nested relationships (e.g., corrective actions from non-conformances)
            if ($relationshipName === 'nonConformances') {
                try {
                    $ncModel = new \App\Models\AuditModule\NonConformance();
                    $nestedRelationships = $this->discoverRelationships($ncModel);
                    
                    // Add nested relationship validations
                    foreach ($nestedRelationships as $nestedName => $nestedInfo) {
                        if ($nestedName === 'correctiveActions') {
                            $validationOptions = array_merge($validationOptions, $this->buildValidationOptionsForNestedRelationship($nestedName, $nestedInfo, 'nonConformances'));
                        } elseif ($nestedName === 'rootCauseAnalysis') {
                            $validationOptions = array_merge($validationOptions, $this->buildValidationOptionsForNestedRelationship($nestedName, $nestedInfo, 'nonConformances'));
                        }
                    }
                } catch (\Exception $e) {
                    // Skip if model can't be instantiated
                }
            }
        }
        
        return $validationOptions;
    }
    
    /**
     * Discover all relationships from the Audit model
     */
    protected function discoverRelationships($model): array
    {
        $relationships = [];
        $reflection = new ReflectionClass($model);
        
        // Get all methods that return HasMany, HasOne, BelongsToMany, etc.
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        foreach ($methods as $method) {
            $methodName = $method->getName();
            
            // Skip non-relationship methods
            if (in_array($methodName, ['__construct', 'save', 'delete', 'update', 'create', 'find', 'query'])) {
                continue;
            }
            
            // Check if method returns a relationship
            $returnType = $method->getReturnType();
            if ($returnType && (
                str_contains($returnType->getName(), 'HasMany') ||
                str_contains($returnType->getName(), 'HasOne') ||
                str_contains($returnType->getName(), 'BelongsToMany') ||
                str_contains($returnType->getName(), 'MorphMany')
            )) {
                try {
                    $relationship = $model->$methodName();
                    $relatedModel = $relationship->getRelated();
                    
                    $relationships[$methodName] = [
                        'type' => class_basename(get_class($relationship)),
                        'model' => get_class($relatedModel),
                        'model_name' => class_basename($relatedModel),
                        'table' => $relatedModel->getTable(),
                    ];
                } catch (\Exception $e) {
                    // Skip if relationship can't be resolved
                    continue;
                }
            }
        }
        
        return $relationships;
    }
    
    /**
     * Build validation options for a specific relationship
     */
    protected function buildValidationOptionsForRelationship(string $relationshipName, array $relationshipInfo): array
    {
        $options = [];
        $modelName = $relationshipInfo['model_name'];
        $table = $relationshipInfo['table'];
        
        // Skip user relationships but include attachments and activity logs for validation
        if (in_array($relationshipName, ['createdBy', 'updatedBy', 'closedBy', 'leadAuditor', 'notifications'])) {
            return $options;
        }
        
        // Include attachments and activity logs as optional validations
        if ($relationshipName === 'attachments') {
            $options[] = [
                'key' => 'require_attachments',
                'label' => 'Require Attachments',
                'description' => 'At least one attachment must be uploaded',
                'type' => 'boolean',
                'category' => 'Attachments',
                'model' => $modelName,
            ];
            $options[] = [
                'key' => 'min_attachments_count',
                'label' => 'Minimum Attachments Count',
                'description' => 'Minimum number of attachments required',
                'type' => 'integer',
                'category' => 'Attachments',
                'model' => $modelName,
            ];
            return $options;
        }
        
        if ($relationshipName === 'activityLogs') {
            $options[] = [
                'key' => 'require_activity_logs',
                'label' => 'Require Activity Logs',
                'description' => 'At least one activity log entry must exist',
                'type' => 'boolean',
                'category' => 'Activity',
                'model' => $modelName,
            ];
            return $options;
        }
        
        // Generate validation options based on relationship type and model
        switch ($relationshipName) {
            case 'findings':
                $options[] = [
                    'key' => 'require_findings',
                    'label' => 'Require Findings',
                    'description' => 'At least one finding must be recorded',
                    'type' => 'boolean',
                    'category' => 'Findings',
                    'model' => $modelName,
                ];
                $options[] = [
                    'key' => 'min_findings_count',
                    'label' => 'Minimum Findings Count',
                    'description' => 'Minimum number of findings required',
                    'type' => 'integer',
                    'category' => 'Findings',
                    'model' => $modelName,
                ];
                $options[] = [
                    'key' => 'require_nc_for_findings',
                    'label' => 'Require NC for Findings',
                    'description' => 'All findings requiring NC must have NCs raised',
                    'type' => 'boolean',
                    'category' => 'Findings',
                    'model' => $modelName,
                ];
                break;
                
            case 'nonConformances':
                $options[] = [
                    'key' => 'require_ncs',
                    'label' => 'Require Non-Conformances',
                    'description' => 'At least one non-conformance must be recorded',
                    'type' => 'boolean',
                    'category' => 'Non-Conformances',
                    'model' => $modelName,
                ];
                $options[] = [
                    'key' => 'min_ncs_count',
                    'label' => 'Minimum NCs Count',
                    'description' => 'Minimum number of non-conformances required',
                    'type' => 'integer',
                    'category' => 'Non-Conformances',
                    'model' => $modelName,
                ];
                $options[] = [
                    'key' => 'require_rca_for_all_ncs',
                    'label' => 'Require RCA for All NCs',
                    'description' => 'All non-conformances must have root cause analysis',
                    'type' => 'boolean',
                    'category' => 'Non-Conformances',
                    'model' => $modelName,
                ];
                $options[] = [
                    'key' => 'require_rca_approved',
                    'label' => 'Require RCA Approved',
                    'description' => 'All root cause analyses must be approved',
                    'type' => 'boolean',
                    'category' => 'Non-Conformances',
                    'model' => $modelName,
                ];
                $options[] = [
                    'key' => 'require_capa_for_all_ncs',
                    'label' => 'Require CAPA for All NCs',
                    'description' => 'All non-conformances must have corrective actions assigned',
                    'type' => 'boolean',
                    'category' => 'Non-Conformances',
                    'model' => $modelName,
                ];
                $options[] = [
                    'key' => 'min_capa_per_nc',
                    'label' => 'Minimum CAPA per NC',
                    'description' => 'Minimum number of corrective actions required per non-conformance',
                    'type' => 'integer',
                    'category' => 'Non-Conformances',
                    'model' => $modelName,
                ];
                break;
                
            case 'teamMembers':
                $options[] = [
                    'key' => 'require_team_members',
                    'label' => 'Require Team Members',
                    'description' => 'At least one team member must be assigned',
                    'type' => 'boolean',
                    'category' => 'Team',
                    'model' => $modelName,
                ];
                $options[] = [
                    'key' => 'min_team_members_count',
                    'label' => 'Minimum Team Members',
                    'description' => 'Minimum number of team members required',
                    'type' => 'integer',
                    'category' => 'Team',
                    'model' => $modelName,
                ];
                break;
                
            case 'checklists':
                $options[] = [
                    'key' => 'require_checklists',
                    'label' => 'Require Checklists',
                    'description' => 'At least one checklist must be assigned',
                    'type' => 'boolean',
                    'category' => 'Checklists',
                    'model' => $modelName,
                ];
                $options[] = [
                    'key' => 'min_checklists_count',
                    'label' => 'Minimum Checklists Count',
                    'description' => 'Minimum number of checklists required',
                    'type' => 'integer',
                    'category' => 'Checklists',
                    'model' => $modelName,
                ];
                break;
                
            case 'checklistItemResponses':
                $options[] = [
                    'key' => 'require_checklist_item_responses',
                    'label' => 'Require Checklist Item Responses',
                    'description' => 'At least one checklist item must be responded to',
                    'type' => 'boolean',
                    'category' => 'Checklist Item Responses',
                    'model' => $modelName,
                ];
                $options[] = [
                    'key' => 'min_checklist_item_responses_count',
                    'label' => 'Minimum Checklist Item Responses',
                    'description' => 'Minimum number of checklist item responses required',
                    'type' => 'integer',
                    'category' => 'Checklist Item Responses',
                    'model' => $modelName,
                ];
                break;
        }
        
        // Add generic count validations for unknown relationships (only if no specific validations were added)
        if (empty($options) && !in_array($relationshipName, ['status', 'auditType', 'checklist', 'leadAuditor', 'createdBy', 'updatedBy', 'closedBy'])) {
            // Convert camelCase to snake_case for key
            $snakeCase = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $relationshipName));
            
            $options[] = [
                'key' => "require_{$snakeCase}",
                'label' => "Require " . ucwords(str_replace(['_', 's'], [' ', ''], $snakeCase)),
                'description' => "At least one {$relationshipName} must exist",
                'type' => 'boolean',
                'category' => ucwords(str_replace('_', ' ', $snakeCase)),
                'model' => $modelName,
            ];
            $options[] = [
                'key' => "min_{$snakeCase}_count",
                'label' => "Minimum " . ucwords(str_replace(['_', 's'], [' ', ''], $snakeCase)) . " Count",
                'description' => "Minimum number of {$relationshipName} required",
                'type' => 'integer',
                'category' => ucwords(str_replace('_', ' ', $snakeCase)),
                'model' => $modelName,
            ];
        }
        
        return $options;
    }
    
    /**
     * Build validation options for nested relationships (e.g., corrective actions from NCs)
     */
    protected function buildValidationOptionsForNestedRelationship(string $relationshipName, array $relationshipInfo, string $parentRelationship): array
    {
        $options = [];
        $modelName = $relationshipInfo['model_name'];
        
        switch ($relationshipName) {
            case 'correctiveActions':
                $options[] = [
                    'key' => 'require_corrective_actions',
                    'label' => 'Require Corrective Actions (CAPA)',
                    'description' => 'All non-conformances must have corrective actions assigned',
                    'type' => 'boolean',
                    'category' => 'Corrective Actions',
                    'model' => $modelName,
                    'parent' => $parentRelationship,
                ];
                $options[] = [
                    'key' => 'min_corrective_actions_count',
                    'label' => 'Minimum Corrective Actions Count',
                    'description' => 'Minimum total number of corrective actions across all NCs',
                    'type' => 'integer',
                    'category' => 'Corrective Actions',
                    'model' => $modelName,
                    'parent' => $parentRelationship,
                ];
                $options[] = [
                    'key' => 'require_corrective_actions_implemented',
                    'label' => 'Require CAPA Implemented',
                    'description' => 'All corrective actions must be implemented',
                    'type' => 'boolean',
                    'category' => 'Corrective Actions',
                    'model' => $modelName,
                    'parent' => $parentRelationship,
                ];
                $options[] = [
                    'key' => 'require_corrective_actions_verified',
                    'label' => 'Require CAPA Verified',
                    'description' => 'All corrective actions must be verified',
                    'type' => 'boolean',
                    'category' => 'Corrective Actions',
                    'model' => $modelName,
                    'parent' => $parentRelationship,
                ];
                break;
                
            case 'rootCauseAnalysis':
                $options[] = [
                    'key' => 'require_root_cause_analysis',
                    'label' => 'Require Root Cause Analysis',
                    'description' => 'All non-conformances must have root cause analysis completed',
                    'type' => 'boolean',
                    'category' => 'Root Cause Analysis',
                    'model' => $modelName,
                    'parent' => $parentRelationship,
                ];
                $options[] = [
                    'key' => 'require_root_cause_analysis_approved',
                    'label' => 'Require RCA Approved',
                    'description' => 'All root cause analyses must be approved',
                    'type' => 'boolean',
                    'category' => 'Root Cause Analysis',
                    'model' => $modelName,
                    'parent' => $parentRelationship,
                ];
                break;
        }
        
        return $options;
    }
    
    /**
     * Get all available validation categories
     */
    public function getValidationCategories(): array
    {
        $options = $this->discoverValidationOptions();
        $categories = [];
        
        foreach ($options as $option) {
            $category = $option['category'] ?? 'General';
            if (!isset($categories[$category])) {
                $categories[$category] = [];
            }
            $categories[$category][] = $option;
        }
        
        return $categories;
    }
    
    /**
     * Get validation option by key
     */
    public function getValidationOption(string $key): ?array
    {
        $options = $this->discoverValidationOptions();
        foreach ($options as $option) {
            if ($option['key'] === $key) {
                return $option;
            }
        }
        return null;
    }
}

