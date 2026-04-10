<?php

namespace App\Livewire\RiskModule;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RiskManagement\RiskConfigurationOption;
use Illuminate\Support\Facades\Auth;

class ConfigurationOptionsManager extends Component
{
    use WithPagination;

    public $optionType; // evaluation_result, acceptance_threshold_rpn, etc.
    public $search = '';
    public $perPage = 15;
    
    public $showModal = false;
    public $isEdit = false;
    public $editId = null;
    
    // Common fields
    public $name = '';
    public $description = '';
    public $is_active = true;
    public $code = '';
    public $color_code = '#6c757d';
    public $order_index = 0;
    
    // Special fields
    public $required_actions = ''; // For evaluation_result
    public $rpn_min = ''; // For evaluation_result and acceptance_threshold_rpn (range min)
    public $rpn_max = ''; // For evaluation_result and acceptance_threshold_rpn (range max)
    public $workflow_step = null; // For evaluation_result

    public function mount($optionType)
    {
        $this->optionType = $optionType;
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
        $item = RiskConfigurationOption::findOrFail($id);
        
        $this->name = $item->name;
        $this->description = $item->description ?? '';
        $this->is_active = $item->is_active;
        $this->code = $item->code ?? '';
        $this->color_code = $item->color_code ?? '#6c757d';
        $this->order_index = $item->order_index ?? 0;
        
        // Load metadata
        if ($this->optionType === 'evaluation_result') {
            if (isset($item->metadata['required_actions'])) {
                $this->required_actions = $item->metadata['required_actions'];
            }
            if (isset($item->metadata['rpn_min'])) {
                $this->rpn_min = $item->metadata['rpn_min'];
            }
            if (isset($item->metadata['rpn_max'])) {
                $this->rpn_max = $item->metadata['rpn_max'];
            }
            if (isset($item->metadata['workflow_step'])) {
                $this->workflow_step = $item->metadata['workflow_step'];
            }
        }
        
        if ($this->optionType === 'acceptance_threshold_rpn') {
            if (isset($item->metadata['min']) && isset($item->metadata['max'])) {
                $this->rpn_min = $item->metadata['min'];
                $this->rpn_max = $item->metadata['max'];
            } else {
                // Fallback for old single values
                $this->rpn_min = $item->code;
                $this->rpn_max = $item->code;
            }
        }
    }

    public function updatedRpnMin()
    {
        if ($this->optionType === 'acceptance_threshold_rpn' && $this->rpn_min && $this->rpn_max) {
            $this->name = $this->rpn_min . ' to ' . $this->rpn_max . ' RPN';
            $this->code = $this->rpn_min . '-' . $this->rpn_max;
        } elseif ($this->optionType === 'acceptance_threshold_rpn' && $this->rpn_min) {
            $this->name = $this->rpn_min . ' RPN';
            $this->code = (string)$this->rpn_min;
        }
    }

    public function updatedRpnMax()
    {
        if ($this->optionType === 'acceptance_threshold_rpn' && $this->rpn_min && $this->rpn_max) {
            $this->name = $this->rpn_min . ' to ' . $this->rpn_max . ' RPN';
            $this->code = $this->rpn_min . '-' . $this->rpn_max;
        } elseif ($this->optionType === 'acceptance_threshold_rpn' && $this->rpn_max) {
            $this->name = $this->rpn_max . ' RPN';
            $this->code = (string)$this->rpn_max;
        }
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
        
        // Handle evaluation_result
        if ($this->optionType === 'evaluation_result') {
            $rules['rpn_min'] = 'required|integer|min:1|max:100';
            $rules['rpn_max'] = 'required|integer|min:1|max:100|gte:rpn_min';
            $rules['workflow_step'] = 'required|integer|min:1|max:8';
        }
        
        // Handle acceptance_threshold_rpn
        if ($this->optionType === 'acceptance_threshold_rpn') {
            $rules['rpn_min'] = 'required|integer|min:1|max:100';
            $rules['rpn_max'] = 'required|integer|min:1|max:100|gte:rpn_min';
            
            if ($this->rpn_min && $this->rpn_max) {
                if ($this->rpn_min == $this->rpn_max) {
                    $this->code = (string)$this->rpn_min;
                    $this->name = $this->rpn_min . ' RPN';
                } else {
                    $this->code = $this->rpn_min . '-' . $this->rpn_max;
                    $this->name = $this->rpn_min . ' to ' . $this->rpn_max . ' RPN';
                }
            }
        } else {
            // Generate code from name if not provided
            if (empty(trim($this->code))) {
                $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $this->name);
                if (!empty($cleanName)) {
                    $this->code = strtolower(substr($cleanName, 0, 50));
                } else {
                    $words = explode(' ', $this->name);
                    $initials = '';
                    foreach ($words as $word) {
                        if (!empty($word)) {
                            $initials .= strtolower(substr($word, 0, 1));
                        }
                    }
                    $this->code = !empty($initials) ? substr($initials, 0, 50) : 'code' . time() % 10000;
                }
            }
            $this->code = strtolower(trim($this->code));
            
            $uniqueRule = $this->isEdit 
                ? 'unique:risk_configuration_options,code,' . $this->editId . ',id,option_type,' . $this->optionType . ',company_id,' . (getUserCompany() ?? 0)
                : 'unique:risk_configuration_options,code,NULL,id,option_type,' . $this->optionType . ',company_id,' . (getUserCompany() ?? 0);
            
            $rules['code'] = 'required|string|max:255|' . $uniqueRule;
        }
        
        $rules['color_code'] = 'nullable|string|max:7';
        $rules['order_index'] = 'nullable|integer|min:0';
        
        $this->validate($rules);
        
        $data = [
            'option_type' => $this->optionType,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'color_code' => $this->color_code,
            'order_index' => $this->order_index ?? 0,
            'company_id' => getUserCompany() ?? 0,
        ];
        
        // Build metadata
        $metadata = [];
        if ($this->optionType === 'evaluation_result') {
            if (!empty($this->required_actions)) {
                $metadata['required_actions'] = $this->required_actions;
            }
            if ($this->rpn_min && $this->rpn_max) {
                $metadata['rpn_min'] = (int)$this->rpn_min;
                $metadata['rpn_max'] = (int)$this->rpn_max;
            }
            if ($this->workflow_step) {
                $metadata['workflow_step'] = (int)$this->workflow_step;
            }
        }
        
        if ($this->optionType === 'acceptance_threshold_rpn' && $this->rpn_min && $this->rpn_max) {
            $metadata['min'] = (int)$this->rpn_min;
            $metadata['max'] = (int)$this->rpn_max;
        }
        
        if (!empty($metadata)) {
            $data['metadata'] = json_encode($metadata);
        }
        
        if ($this->isEdit) {
            $item = RiskConfigurationOption::findOrFail($this->editId);
            $item->update($data);
            $message = 'Option updated successfully.';
        } else {
            RiskConfigurationOption::create($data);
            $message = 'Option created successfully.';
        }
        
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
    }

    public function delete($id)
    {
        $item = RiskConfigurationOption::findOrFail($id);
        $item->delete();
        
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Option deleted successfully.']);
    }

    public function toggleActive($id)
    {
        $item = RiskConfigurationOption::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Option status updated successfully.']);
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
        $this->required_actions = '';
        $this->rpn_min = '';
        $this->rpn_max = '';
        $this->workflow_step = null;
    }

    public function getTypeTitle()
    {
        return match($this->optionType) {
            'evaluation_result' => 'Evaluation Results',
            'acceptance_threshold_rpn' => 'Acceptance Threshold RPN',
            'risk_level' => 'Risk Levels',
            'implementation_status' => 'Implementation Statuses',
            'treatment_priority' => 'Treatment Priorities',
            'review_type' => 'Review Types',
            'review_decision' => 'Review Decisions',
            'closure_type' => 'Closure Types',
            default => ucfirst(str_replace('_', ' ', $this->optionType)),
        };
    }

    public function render()
    {
        $companyId = getUserCompany() ?? 0;
        
        $query = RiskConfigurationOption::where('option_type', $this->optionType)
            ->where(function($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhere('company_id', 0);
            });
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }
        
        $items = $query->orderBy('order_index', 'asc')
            ->orderBy('name', 'asc')
            ->paginate($this->perPage);

        return view('livewire.risk-module.configuration-options-manager', [
            'items' => $items,
            'typeTitle' => $this->getTypeTitle(),
        ]);
    }
}

