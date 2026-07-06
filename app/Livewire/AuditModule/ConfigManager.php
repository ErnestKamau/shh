<?php

namespace App\Livewire\AuditModule;

use Livewire\Component;
use Livewire\WithPagination;

class ConfigManager extends Component
{
    use WithPagination;

    public $type; // audit_types, finding_categories, risk_levels, rca_methods, capa_categories
    public $search = '';
    public $perPage = 15;
    
    public $showModal = false;
    public $isEdit = false;
    public $editId = null;
    
    // Common fields
    public $name = '';
    public $description = '';
    public $is_active = true;
    
    // Type-specific fields
    public $code = '';
    public $severity = '';
    public $requires_capa = false;
    public $color = '#6c757d';
    public $color_code = '#6c757d';
    public $badge_class = 'secondary';
    public $order_index = 0;
    public $workflow_step = null;
    public $score = 0;
    public $criteria = '';
    public $template = '';
    // Workflow action fields
    public $icon = '';
    public $requires_remarks = true;
    public $min_remarks_length = 10;
    public $requires_target_status = true;
    // Verification result fields
    public $requires_reopen = false;
    public $next_workflow_step = null;

    public function mount($type)
    {
        $this->type = $type;
    }

    public function getModelClass()
    {
        return match($this->type) {
            'audit_types' => \App\Models\AuditModule\AuditType::class,
            'audit_statuses' => \App\Models\AuditModule\AuditStatus::class,
            'workflow_actions' => \App\Models\AuditModule\WorkflowAction::class,
            'finding_categories' => \App\Models\AuditModule\FindingCategory::class,
            'risk_levels' => \App\Models\AuditModule\RiskLevel::class,
            'rca_methods' => \App\Models\AuditModule\RootCauseMethod::class,
            'capa_categories' => \App\Models\AuditModule\CapaCategory::class,
            'compliance_statuses' => \App\Models\AuditModule\ComplianceStatus::class,
            'severity_scales' => \App\Models\AuditModule\SeverityScale::class,
            'likelihood_scales' => \App\Models\AuditModule\LikelihoodScale::class,
            'verification_results' => \App\Models\AuditModule\VerificationResult::class,
            default => null,
        };
    }

    public function openModal($id = null)
    {
        $this->resetForm();
        
        if ($id) {
            $this->isEdit = true;
            $this->editId = $id;
            $this->loadItem($id);
        }
        
        $this->showModal = true;
    }

    public function loadItem($id)
    {
        $modelClass = $this->getModelClass();
        $item = $modelClass::findOrFail($id);
        
        $this->name = $item->name;
        $this->description = $item->description;
        $this->is_active = $item->is_active;
        
        if ($this->type === 'audit_types' || $this->type === 'finding_categories' || $this->type === 'compliance_statuses' || $this->type === 'audit_statuses' || $this->type === 'severity_scales' || $this->type === 'likelihood_scales' || $this->type === 'verification_results' || $this->type === 'risk_levels' || $this->type === 'rca_methods' || $this->type === 'capa_categories') {
            $this->code = $item->code;
        }
        
        if ($this->type === 'finding_categories') {
            $this->severity = $item->severity;
            $this->requires_capa = $item->requires_capa;
        } elseif ($this->type === 'risk_levels') {
            $this->color = $item->color_code ?? '#6c757d';
            $this->color_code = $item->color_code ?? '#6c757d';
            $this->score = $item->severity_score ?? 0;
        } elseif ($this->type === 'rca_methods') {
            $this->template = is_array($item->template) ? json_encode($item->template, JSON_PRETTY_PRINT) : $item->template;
        } elseif ($this->type === 'compliance_statuses') {
            $this->color_code = $item->color_code ?? '#6c757d';
            $this->badge_class = $item->badge_class ?? 'secondary';
            $this->order_index = $item->order_index ?? 0;
        } elseif ($this->type === 'audit_statuses') {
            $this->color_code = $item->color_code ?? '#6c757d';
            $this->order_index = $item->order_index ?? 0;
            $this->workflow_step = $item->workflow_step;
        } elseif ($this->type === 'workflow_actions') {
            $this->color_code = $item->color_code ?? '#6c757d';
            $this->badge_class = $item->badge_class ?? 'primary';
            $this->order_index = $item->order_index ?? 0;
            $this->icon = $item->icon ?? '';
            $this->requires_remarks = $item->requires_remarks ?? true;
            $this->min_remarks_length = $item->min_remarks_length ?? 10;
            $this->requires_target_status = $item->requires_target_status ?? true;
        } elseif ($this->type === 'severity_scales' || $this->type === 'likelihood_scales') {
            $this->score = $item->score;
            $this->color_code = $item->color_code ?? '#6c757d';
            $this->order_index = $item->order_index ?? 0;
        } elseif ($this->type === 'verification_results') {
            $this->color_code = $item->color_code ?? '#17a2b8';
            $this->requires_reopen = $item->requires_reopen ?? false;
            $this->next_workflow_step = $item->next_workflow_step;
        }
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
        
        if ($this->type === 'audit_types') {
            // Generate code from name if not provided
            if (empty(trim($this->code))) {
                // Extract alphanumeric characters from name
                $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $this->name);
                
                // If name has characters, use first 10, otherwise generate from initials
                if (!empty($cleanName)) {
                    $this->code = strtoupper(substr($cleanName, 0, 10));
                } else {
                    // Fallback: use first letters of words
                    $words = explode(' ', $this->name);
                    $initials = '';
                    foreach ($words as $word) {
                        if (!empty($word)) {
                            $initials .= strtoupper(substr($word, 0, 1));
                        }
                    }
                    $this->code = !empty($initials) ? substr($initials, 0, 10) : 'AUD' . time() % 10000;
                }
            }
            
            // Ensure code is not empty and is uppercase
            $this->code = strtoupper(trim($this->code));
            
            // If still empty after processing, generate a unique code
            if (empty($this->code)) {
                $this->code = 'AUD' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
            
            $uniqueRule = $this->uniqueConfigCodeRule('audit_types');
            
            $rules['code'] = 'required|string|max:10|' . $uniqueRule;
        } elseif ($this->type === 'finding_categories') {
            $rules['code'] = 'nullable|string|max:10';
            $rules['severity'] = 'nullable|string|max:50';
            $rules['requires_capa'] = 'boolean';
        } elseif ($this->type === 'risk_levels') {
            $this->ensureCodeFromName('RISK', 20);
            $uniqueRule = $this->isEdit
                ? 'unique:risk_levels,code,' . $this->editId . ',id'
                : 'unique:risk_levels,code';
            $rules['code'] = 'required|string|max:20|' . $uniqueRule;
            $rules['color'] = 'nullable|string|max:20';
            $rules['score'] = 'required|integer|min:0|max:100';
            $rules['criteria'] = 'nullable|string';
        } elseif ($this->type === 'rca_methods') {
            $this->ensureCodeFromName('RCA', 20);
            $uniqueRule = $this->isEdit
                ? 'unique:root_cause_methods,code,' . $this->editId . ',id'
                : 'unique:root_cause_methods,code';
            $rules['code'] = 'required|string|max:20|' . $uniqueRule;
            $rules['template'] = 'nullable|string';
        } elseif ($this->type === 'capa_categories') {
            $this->ensureCodeFromName('CAPA', 20);
            $uniqueRule = $this->isEdit
                ? 'unique:capa_categories,code,' . $this->editId . ',id'
                : 'unique:capa_categories,code';
            $rules['code'] = 'required|string|max:20|' . $uniqueRule;
        } elseif ($this->type === 'compliance_statuses') {
            // Generate code from name if not provided
            if (empty(trim($this->code))) {
                $this->code = strtolower(str_replace([' ', '-'], '_', $this->name));
            }
            $this->code = strtolower(trim($this->code));
            
            $uniqueRule = $this->uniqueConfigCodeRule('compliance_statuses');
            
            $rules['code'] = 'required|string|max:50|' . $uniqueRule;
            $rules['color_code'] = 'nullable|string|max:7';
            $rules['badge_class'] = 'required|string|in:success,danger,warning,info,secondary';
            $rules['order_index'] = 'nullable|integer|min:0';
        } elseif ($this->type === 'audit_statuses') {
            // Generate code from name if not provided
            if (empty(trim($this->code))) {
                $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $this->name);
                if (!empty($cleanName)) {
                    $this->code = strtoupper(substr($cleanName, 0, 10));
                } else {
                    $words = explode(' ', $this->name);
                    $initials = '';
                    foreach ($words as $word) {
                        if (!empty($word)) {
                            $initials .= strtoupper(substr($word, 0, 1));
                        }
                    }
                    $this->code = !empty($initials) ? substr($initials, 0, 10) : 'STAT' . time() % 10000;
                }
            }
            $this->code = strtoupper(trim($this->code));
            
            $uniqueRule = $this->uniqueConfigCodeRule('audit_statuses');
            
            $rules['code'] = 'required|string|max:10|' . $uniqueRule;
            $rules['color_code'] = 'nullable|string|max:7';
            $rules['order_index'] = 'nullable|integer|min:0';
            $rules['workflow_step'] = 'nullable|integer|min:1|max:' . max(array_keys(getAuditWorkflowSteps()));
        } elseif ($this->type === 'workflow_actions') {
            // Generate code from name if not provided
            if (empty(trim($this->code))) {
                $this->code = strtoupper(str_replace([' ', '-'], '_', $this->name));
            }
            $this->code = strtoupper(trim($this->code));
            
            $uniqueRule = $this->uniqueConfigCodeRule('workflow_actions');
            
            $rules['code'] = 'required|string|max:50|' . $uniqueRule;
            $rules['icon'] = 'nullable|string|max:100';
            $rules['color_code'] = 'nullable|string|max:7';
            $rules['badge_class'] = 'required|string|in:success,danger,warning,info,primary,secondary';
            $rules['requires_remarks'] = 'boolean';
            $rules['min_remarks_length'] = 'nullable|integer|min:0';
            $rules['requires_target_status'] = 'boolean';
            $rules['order_index'] = 'nullable|integer|min:0';
        } elseif ($this->type === 'severity_scales' || $this->type === 'likelihood_scales') {
            // Generate code from name if not provided
            if (empty(trim($this->code))) {
                $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $this->name);
                if (!empty($cleanName)) {
                    $prefix = $this->type === 'severity_scales' ? 'SEV' : 'LIK';
                    $this->code = strtoupper($prefix . '-' . substr($cleanName, 0, 10));
                } else {
                    $prefix = $this->type === 'severity_scales' ? 'SEV' : 'LIK';
                    $this->code = $prefix . '-' . time() % 10000;
                }
            }
            $this->code = strtoupper(trim($this->code));
            
            $scalesTable = $this->type === 'severity_scales' ? 'severity_scales' : 'likelihood_scales';
            $uniqueRule = $this->uniqueConfigCodeRule($scalesTable);
            
            $rules['code'] = 'required|string|max:20|' . $uniqueRule;
            $rules['score'] = 'required|integer|min:1|max:100';
            $rules['color_code'] = 'nullable|string|max:7';
            $rules['order_index'] = 'nullable|integer|min:0';
        } elseif ($this->type === 'verification_results') {
            // Generate code from name if not provided
            if (empty(trim($this->code))) {
                $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $this->name);
                if (!empty($cleanName)) {
                    $this->code = strtoupper(substr($cleanName, 0, 50));
                } else {
                    $words = explode(' ', $this->name);
                    $initials = '';
                    foreach ($words as $word) {
                        if (!empty($word)) {
                            $initials .= strtoupper(substr($word, 0, 1));
                        }
                    }
                    $this->code = !empty($initials) ? substr($initials, 0, 50) : 'VR' . time() % 10000;
                }
            }
            $this->code = strtoupper(trim($this->code));
            
            $uniqueRule = $this->uniqueConfigCodeRule('verification_results', 'code', ',deleted_at,NULL');
            
            $rules['code'] = 'required|string|max:50|' . $uniqueRule;
            $rules['color_code'] = 'nullable|string|max:7';
            $rules['requires_reopen'] = 'boolean';
            $rules['next_workflow_step'] = 'required|integer|min:1|max:8';
        }
        
        $this->validate($rules);
        
        $modelClass = $this->getModelClass();
        
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'company_id' => getUserCompany(),
        ];
        
        if ($this->type === 'audit_types') {
            $data['code'] = strtoupper(trim($this->code));
        } elseif ($this->type === 'audit_statuses') {
            $data['code'] = strtoupper(trim($this->code));
            $data['color_code'] = $this->color_code;
            $data['order_index'] = $this->order_index ?? 0;
            $data['workflow_step'] = $this->workflow_step;
        } elseif ($this->type === 'workflow_actions') {
            $data['code'] = strtoupper(trim($this->code));
            $data['icon'] = $this->icon;
            $data['color_code'] = $this->color_code;
            $data['badge_class'] = $this->badge_class;
            $data['requires_remarks'] = $this->requires_remarks;
            $data['min_remarks_length'] = $this->min_remarks_length ?? 10;
            $data['requires_target_status'] = $this->requires_target_status;
            $data['order_index'] = $this->order_index ?? 0;
        } elseif ($this->type === 'risk_levels') {
            $data['code'] = strtoupper(trim($this->code));
            $data['color_code'] = $this->color ?: $this->color_code;
            $data['severity_score'] = (int) $this->score;
            if (! empty(trim($this->criteria)) && empty(trim($this->description))) {
                $data['description'] = $this->criteria;
            }
        } elseif ($this->type === 'rca_methods') {
            $data['code'] = strtoupper(trim($this->code));
            $data['template'] = $this->template ? json_decode($this->template, true) : null;
        } elseif ($this->type === 'capa_categories') {
            $data['code'] = strtoupper(trim($this->code));
        } elseif ($this->type === 'finding_categories') {
            $data['code'] = $this->code;
            $data['severity'] = $this->severity;
            $data['requires_capa'] = $this->requires_capa;
        } elseif ($this->type === 'compliance_statuses') {
            $data['code'] = strtolower(trim($this->code));
            $data['color_code'] = $this->color_code;
            $data['badge_class'] = $this->badge_class;
            $data['order_index'] = $this->order_index ?? 0;
        } elseif ($this->type === 'severity_scales' || $this->type === 'likelihood_scales') {
            $data['code'] = strtoupper(trim($this->code));
            $data['score'] = $this->score;
            $data['color_code'] = $this->color_code ?? '#6c757d';
            $data['order_index'] = $this->order_index ?? 0;
        } elseif ($this->type === 'verification_results') {
            $data['code'] = strtoupper(trim($this->code));
            $data['color_code'] = $this->color_code ?? '#17a2b8';
            $data['requires_reopen'] = $this->requires_reopen ?? false;
            $data['next_workflow_step'] = $this->next_workflow_step;
        }
        
        if ($this->isEdit) {
            $item = $modelClass::findOrFail($this->editId);
            $item->update($data);
            $message = 'Item updated successfully.';
        } else {
            $modelClass::create($data);
            $message = 'Item created successfully.';
        }
        
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
    }

    public function delete($id)
    {
        $modelClass = $this->getModelClass();
        $item = $modelClass::findOrFail($id);
        $item->delete();
        
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Item deleted successfully.']);
    }

    public function toggleActive($id)
    {
        $modelClass = $this->getModelClass();
        $item = $modelClass::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
    }

    public function resetForm()
    {
        $this->isEdit = false;
        $this->editId = null;
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
        $this->code = '';
        $this->severity = '';
        $this->requires_capa = false;
        $this->color = '#6c757d';
        $this->color_code = '#6c757d';
        $this->badge_class = 'secondary';
        $this->order_index = 0;
        $this->workflow_step = null;
        $this->score = 0;
        $this->criteria = '';
        $this->template = '';
        $this->icon = '';
        $this->requires_remarks = true;
        $this->min_remarks_length = 10;
        $this->requires_target_status = true;
        $this->requires_reopen = false;
        $this->next_workflow_step = null;
    }

    public function getTypeTitle()
    {
        return match($this->type) {
            'audit_types' => 'Audit Types',
            'audit_statuses' => 'Audit Statuses',
            'workflow_actions' => 'Workflow Actions',
            'finding_categories' => 'Finding Categories',
            'risk_levels' => 'Risk Levels',
            'rca_methods' => 'Root Cause Methods',
            'capa_categories' => 'CAPA Categories',
            'compliance_statuses' => 'Compliance Statuses',
            'verification_results' => 'Verification Results',
            default => 'Configuration',
        };
    }

    /**
     * Build a `unique:...` rule scoped by company UUID. Omits `company_id` when no company context (global rows).
     */
    protected function uniqueConfigCodeRule(string $table, string $codeColumn = 'code', string $additionalUniqueSuffix = ''): string
    {
        $companyId = getUserCompany();
        $companyClause = ($companyId !== null && $companyId !== '')
            ? ',company_id,' . $companyId
            : '';

        return $this->isEdit
            ? 'unique:' . $table . ',' . $codeColumn . ',' . $this->editId . ',id' . $companyClause . $additionalUniqueSuffix
            : 'unique:' . $table . ',' . $codeColumn . ',NULL,id' . $companyClause . $additionalUniqueSuffix;
    }

    protected function ensureCodeFromName(string $fallbackPrefix = 'CFG', int $maxLength = 10): void
    {
        if (! empty(trim($this->code))) {
            $this->code = strtoupper(trim($this->code));

            return;
        }

        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $this->name);
        if (! empty($cleanName)) {
            $this->code = strtoupper(substr($cleanName, 0, $maxLength));

            return;
        }

        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            if (! empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }

        $this->code = ! empty($initials)
            ? substr($initials, 0, $maxLength)
            : $fallbackPrefix . str_pad((string) (time() % 10000), 4, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        $modelClass = $this->getModelClass();
        
        // Use forCompany scope if available, otherwise use default query
        if (method_exists($modelClass, 'scopeForCompany')) {
            $query = $modelClass::forCompany();
        } else {
            $companyId = getUserCompany();
            $query = $modelClass::query()->where(function ($q) use ($companyId) {
                $q->whereNull('company_id');
                if ($companyId !== null && $companyId !== '') {
                    $q->orWhere('company_id', $companyId);
                }
            });
        }
        
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        
        // For audit_statuses, order by workflow_step first, then order_index
        if ($this->type === 'audit_statuses') {
            $items = $query->orderBy('workflow_step', 'asc')
                ->orderBy('order_index', 'asc')
                ->orderBy('name', 'asc')
                ->paginate($this->perPage);
        } else {
            $items = $query->orderBy('name')->paginate($this->perPage);
        }

        return view('livewire.audit-module.config-manager', [
            'items' => $items,
            'typeTitle' => $this->getTypeTitle(),
        ]);
    }
}

