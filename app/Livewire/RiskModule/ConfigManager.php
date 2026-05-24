<?php

namespace App\Livewire\RiskModule;

use Livewire\Component;
use Livewire\WithPagination;

class ConfigManager extends Component
{
    use WithPagination;

    public $type; // risk_statuses, risk_categories, risk_sources, treatment_types
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
    public $color_code = '#6c757d';
    public $order_index = 0;
    public $workflow_step = null;

    public $score = 1;

    public function mount($type)
    {
        $this->type = $type;
    }

    public function getModelClass()
    {
        return match($this->type) {
            'risk_statuses' => \App\Models\RiskManagement\RiskStatus::class,
            'risk_categories' => \App\Models\RiskManagement\RiskCategory::class,
            'risk_sources' => \App\Models\RiskManagement\RiskSource::class,
            'treatment_types' => \App\Models\RiskManagement\TreatmentType::class,
            'likelihood_scales' => \App\Models\AuditModule\LikelihoodScale::class,
            'severity_scales' => \App\Models\AuditModule\SeverityScale::class,
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
        $this->description = $item->description ?? '';
        $this->is_active = $item->is_active;
        
        if ($this->type === 'risk_statuses') {
            $this->code = $item->code ?? '';
            $this->color_code = $item->color_code ?? '#6c757d';
            $this->order_index = $item->order_index ?? 0;
            $this->workflow_step = $item->workflow_step;
        } elseif (in_array($this->type, ['risk_categories', 'risk_sources', 'treatment_types'])) {
            $this->code = $item->code ?? '';
        } elseif (in_array($this->type, ['likelihood_scales', 'severity_scales'])) {
            $this->code = $item->code ?? '';
            $this->score = $item->score ?? 1;
            $this->color_code = $item->color_code ?? '#6c757d';
            $this->order_index = $item->order_index ?? 0;
        }
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];

        $isScale = in_array($this->type, ['likelihood_scales', 'severity_scales']);
        
        if ($this->type === 'risk_statuses' || $isScale) {
            // Generate code from name if not provided
            if (empty(trim($this->code))) {
                if ($isScale) {
                     $this->code = strtoupper(substr(str_replace(' ', '_', $this->name), 0, 20));
                } else {
                    $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $this->name);
                    if (!empty($cleanName)) {
                        $this->code = strtoupper(substr($cleanName, 0, 10));
                    } else {
                        // Fallback logic
                        $words = explode(' ', $this->name);
                        $initials = '';
                        foreach ($words as $word) {
                            if (!empty($word)) {
                                $initials .= strtoupper(substr($word, 0, 1));
                            }
                        }
                        $this->code = !empty($initials) ? substr($initials, 0, 10) : 'CODE' . time() % 10000;
                    }
                }
            }
            $this->code = strtoupper(trim($this->code));
            
            $modelClass = $this->getModelClass();
            $tableName = (new $modelClass)->getTable();
            
            $uniqueRule = $this->isEdit
                ? 'unique:'.$tableName.',code,'.$this->editId.',id,company_id,'.(riskCompanyId() ?? 'NULL')
                : 'unique:'.$tableName.',code,NULL,id,company_id,'.(riskCompanyId() ?? 'NULL');
            
            $rules['code'] = 'nullable|string|max:' . ($isScale ? 50 : 10) . '|' . $uniqueRule;
            $rules['color_code'] = 'nullable|string|max:7';
            $rules['order_index'] = 'nullable|integer|min:0';
            
            if ($this->type === 'risk_statuses') {
               $rules['workflow_step'] = 'nullable|integer|min:1|max:7';
            }
            if ($isScale) {
                $rules['score'] = 'required|integer|min:1|max:10';
            }

        } elseif (in_array($this->type, ['risk_categories', 'risk_sources', 'treatment_types'])) {
            if (empty(trim($this->code))) {
                $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $this->name);
                if (!empty($cleanName)) {
                    $this->code = strtoupper(substr($cleanName, 0, 10));
                } else {
                     $words = explode(' ', $this->name);
                     $initials = '';
                     foreach ($words as $word) { if (!empty($word)) { $initials .= strtoupper(substr($word, 0, 1)); } }
                     $this->code = !empty($initials) ? substr($initials, 0, 10) : 'CODE' . time() % 10000;
                }
            }
            $this->code = strtoupper(trim($this->code));
            
            $modelClass = $this->getModelClass();
            $tableName = (new $modelClass)->getTable();
            
            $uniqueRule = $this->isEdit
                ? 'unique:'.$tableName.',code,'.$this->editId.',id,company_id,'.(riskCompanyId() ?? 'NULL')
                : 'unique:'.$tableName.',code,NULL,id,company_id,'.(riskCompanyId() ?? 'NULL');
            
            $rules['code'] = 'nullable|string|max:10|' . $uniqueRule;
        }
        
        $this->validate($rules);
        
        $modelClass = $this->getModelClass();
        
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'company_id' => riskCompanyId(),
        ];
        
        if ($this->type === 'risk_statuses' || $isScale) {
            $data['code'] = strtoupper(trim($this->code));
            $data['color_code'] = $this->color_code;
            $data['order_index'] = $this->order_index ?? 0;
            
            if ($this->type === 'risk_statuses') {
                $data['workflow_step'] = $this->workflow_step;
            }
            if ($isScale) {
                $data['score'] = $this->score;
            }
        } elseif (in_array($this->type, ['risk_categories', 'risk_sources', 'treatment_types'])) {
            $data['code'] = $this->code ? strtoupper(trim($this->code)) : null;
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
        $this->color_code = '#6c757d';
        $this->order_index = 0;
        $this->workflow_step = null;
        $this->score = 1;
    }

    public function getTypeTitle()
    {
        return match($this->type) {
            'risk_statuses' => 'Risk Statuses',
            'risk_categories' => 'Risk Categories',
            'risk_sources' => 'Risk Sources',
            'treatment_types' => 'Treatment Types',
            'likelihood_scales' => 'Likelihood Scales',
            'severity_scales' => 'Severity Scales',
            default => 'Configuration',
        };
    }

    public function render()
    {
        $modelClass = $this->getModelClass();
        
        $query = riskApplyCompanyScope($modelClass::query());
        
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        
        // For risk_statuses and scales, order by order_index (and workflow_step for statuses)
        if ($this->type === 'risk_statuses') {
            $items = $query->orderBy('workflow_step', 'asc')
                ->orderBy('order_index', 'asc')
                ->orderBy('name', 'asc')
                ->paginate($this->perPage);
        } elseif (in_array($this->type, ['likelihood_scales', 'severity_scales'])) {
             $items = $query->orderBy('order_index', 'asc')
                ->orderBy('name', 'asc')
                ->paginate($this->perPage);
        } else {
            $items = $query->orderBy('name')->paginate($this->perPage);
        }

        return view('livewire.risk-module.config-manager', [
            'items' => $items,
            'typeTitle' => $this->getTypeTitle(),
        ]);
    }
}


