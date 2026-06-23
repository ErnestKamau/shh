<?php

namespace App\Livewire\Standards;

use Livewire\Component;
use Livewire\WithPagination;
use App\Standards;
use App\StandardValue;
use App\StandardAnalytes;
use App\Analyte;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StandardAnalytesManager extends Component
{
    use WithPagination;

    // Standard ID
    public $standardId;
    public $standard;

    // Standard Analytes Management
    public $editingStandardAnalyte = null;
    public $showStandardAnalyteModal = false;
    
    // Standard Analyte Form
    public $standardAnalyteForm = [
        'analyte_id' => null,
        'standard_value_id' => null,
        'standard_value_type' => 'is_range',
        'low' => '',
        'high' => '',
        'standard_is_value' => '',
        'comments' => '',
        'recommendations' => '',
        'expected_value' => '',
        'absolute_tolerance' => false,
        'is_active' => true,
        'mean_value' => '',
        'rel_std_dev' => '',
        'tolerance_1' => '',
        'tolerance_2' => '',
        'value_type' => ''
    ];

    // Supporting Data
    public $analytes = [];
    public $standardValues = [];

    // Searchable select state (modal)
    public $analyteSearch = '';
    public $standardValueSearch = '';
    public $selectedAnalyteName = '';
    public $selectedStandardValueName = '';
    public $showAnalyteDropdown = false;
    public $showStandardValueDropdown = false;
    public $filteredAnalytes = [];
    public $filteredStandardValues = [];

    // Search and Filter
    public $search = '';
    public $statusFilter = '';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    protected $rules = [
        'standardAnalyteForm.analyte_id' => 'required|exists:analytes,id',
        'standardAnalyteForm.standard_value_id' => 'nullable|exists:standard_values,id',
    ];

    protected $messages = [
        'standardAnalyteForm.analyte_id.required' => 'Analyte selection is required.',
    ];

    public function mount($standardId)
    {
        $this->standardId = $standardId;
        $this->standard = Standards::findOrFail($standardId);
        $this->loadInitialData();
    }

    public function loadInitialData()
    {
        $this->analytes = Analyte::where('active', 1)->get();
        $this->standardValues = StandardValue::where('status', 1)->get();
    }

    public function getStandardAnalytesProperty()
    {
        $query = StandardAnalytes::with(['analyte', 'standardValue'])
            ->where('standard_id', $this->standardId);

        if ($this->search) {
            $query->whereHas('analyte', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter);
        }

        return $query->orderBy('id', 'asc')->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function showCreateStandardAnalyteModal()
    {
        $this->resetStandardAnalyteForm();
        $this->showStandardAnalyteModal = true;
        $this->editingStandardAnalyte = null;
        
        // Dispatch event to trigger JavaScript enhancement
        $this->dispatch('modal-opened', ['type' => 'create', 'id' => null]);
    }

    public function showEditStandardAnalyteModal($id)
    {
        $standardAnalyte = StandardAnalytes::findOrFail($id);
        
        // Reset form first to ensure clean state
        $this->resetStandardAnalyteForm();
        
        // Determine value type based on both old and new data structures
        $valueType = 'range'; // default
        if (!empty($standardAnalyte->value_type)) {
            // New structure
            $valueType = $standardAnalyte->value_type;
        } elseif ($standardAnalyte->standard_value_type === 'is_range') {
            // Old structure - range
            $valueType = 'range';
        } elseif ($standardAnalyte->standard_value_type === 'is_standard_value') {
            // Old structure - value
            $valueType = 'use_value';
        }

        // Populate form with existing data
        $this->standardAnalyteForm = [
            'analyte_id' => $standardAnalyte->analyte_id,
            'standard_value_id' => $standardAnalyte->standard_value_id,
            'standard_value_type' => $standardAnalyte->standard_value_type ?? 'is_range',
            'low' => $standardAnalyte->low ?? '',
            'high' => $standardAnalyte->high ?? '',
            'standard_is_value' => $standardAnalyte->standard_is_value ?? '',
            'comments' => $standardAnalyte->comments ?? '',
            'recommendations' => $standardAnalyte->recommendations ?? '',
            'expected_value' => $standardAnalyte->expected_value ?? '',
            'absolute_tolerance' => $standardAnalyte->absolute_tolerance ?? false,
            'is_active' => $standardAnalyte->is_active ?? true,
            'mean_value' => $standardAnalyte->mean_value ?? '',
            'rel_std_dev' => $standardAnalyte->rel_std_dev ?? '',
            'tolerance_1' => $standardAnalyte->tolerance_1 ?? '',
            'tolerance_2' => $standardAnalyte->tolerance_2 ?? '',
            'value_type' => $valueType,
            'matrix_operator' => $valueType === 'use_value' ? ($standardAnalyte->value_type ?? '') : ($standardAnalyte->matrix_operator ?? ''),
            'matrix_value' => $valueType === 'use_value' ? ($standardAnalyte->standard_is_value ?? '') : ($standardAnalyte->matrix_value ?? '')
        ];
        
        $this->editingStandardAnalyte = $id;
        $this->showStandardAnalyteModal = true;
        $this->syncSearchableSelectLabels();

        // Dispatch event to trigger JavaScript enhancement
        $this->dispatch('modal-opened', ['type' => 'edit', 'id' => $id]);
    }

    public function saveStandardAnalyte()
    {
        // Base validation rules
        $rules = [
            'standardAnalyteForm.analyte_id' => 'required|exists:analytes,id',
            'standardAnalyteForm.value_type' => 'required|in:range,use_value',
        ];

        // Conditional validation based on value type
        if ($this->standardAnalyteForm['value_type'] === 'range') {
            $rules['standardAnalyteForm.low'] = 'required|string';
            $rules['standardAnalyteForm.high'] = 'required|string';
        } elseif ($this->standardAnalyteForm['value_type'] === 'use_value') {
            $rules['standardAnalyteForm.standard_value_id'] = 'required|exists:standard_values,id';
            $rules['standardAnalyteForm.matrix_operator'] = 'required|in:max,min,greater_than,less_than';
            $rules['standardAnalyteForm.matrix_value'] = 'required|string';
        }

        $this->validate($rules);

        try {
            DB::beginTransaction();

            if ($this->editingStandardAnalyte) {
                $standardAnalyte = StandardAnalytes::findOrFail($this->editingStandardAnalyte);
                $standardAnalyte->update([
                    'standard_id' => $this->standardId,
                    'analyte_id' => $this->standardAnalyteForm['analyte_id'],
                    'standard_value_id' => $this->standardAnalyteForm['standard_value_id'],
                    'standard_value_type' => $this->standardAnalyteForm['value_type'] === 'range' ? 'is_range' : 'is_standard_value',
                    'low' => $this->standardAnalyteForm['low'],
                    'high' => $this->standardAnalyteForm['high'],
                    'standard_is_value' => $this->standardAnalyteForm['value_type'] === 'use_value' ? $this->standardAnalyteForm['matrix_value'] : $this->standardAnalyteForm['standard_is_value'],
                    'comments' => $this->standardAnalyteForm['comments'],
                    'recommendations' => $this->standardAnalyteForm['recommendations'],
                    'expected_value' => $this->standardAnalyteForm['expected_value'],
                    'absolute_tolerance' => $this->standardAnalyteForm['absolute_tolerance'],
                    'is_active' => $this->standardAnalyteForm['is_active'],
                    'mean_value' => $this->standardAnalyteForm['mean_value'],
                    'rel_std_dev' => $this->standardAnalyteForm['rel_std_dev'],
                    'tolerance_1' => $this->standardAnalyteForm['tolerance_1'],
                    'tolerance_2' => $this->standardAnalyteForm['tolerance_2'],
                    'value_type' => $this->standardAnalyteForm['value_type'] === 'use_value' ? $this->standardAnalyteForm['matrix_operator'] : $this->standardAnalyteForm['value_type'],
                    'matrix_operator' => $this->standardAnalyteForm['matrix_operator'],
                    'matrix_value' => $this->standardAnalyteForm['matrix_value'],
                ]);
                $this->message = 'Standard analyte updated successfully!';
            } else {
                StandardAnalytes::create([
                    'standard_id' => $this->standardId,
                    'analyte_id' => $this->standardAnalyteForm['analyte_id'],
                    'standard_value_id' => $this->standardAnalyteForm['standard_value_id'],
                    'standard_value_type' => $this->standardAnalyteForm['value_type'] === 'range' ? 'is_range' : 'is_standard_value',
                    'low' => $this->standardAnalyteForm['low'],
                    'high' => $this->standardAnalyteForm['high'],
                    'standard_is_value' => $this->standardAnalyteForm['value_type'] === 'use_value' ? $this->standardAnalyteForm['matrix_value'] : $this->standardAnalyteForm['standard_is_value'],
                    'comments' => $this->standardAnalyteForm['comments'],
                    'recommendations' => $this->standardAnalyteForm['recommendations'],
                    'expected_value' => $this->standardAnalyteForm['expected_value'],
                    'absolute_tolerance' => $this->standardAnalyteForm['absolute_tolerance'],
                    'is_active' => $this->standardAnalyteForm['is_active'],
                    'mean_value' => $this->standardAnalyteForm['mean_value'],
                    'rel_std_dev' => $this->standardAnalyteForm['rel_std_dev'],
                    'tolerance_1' => $this->standardAnalyteForm['tolerance_1'],
                    'tolerance_2' => $this->standardAnalyteForm['tolerance_2'],
                    'value_type' => $this->standardAnalyteForm['value_type'] === 'use_value' ? $this->standardAnalyteForm['matrix_operator'] : $this->standardAnalyteForm['value_type'],
                    'matrix_operator' => $this->standardAnalyteForm['matrix_operator'],
                    'matrix_value' => $this->standardAnalyteForm['matrix_value'],
                ]);
                $this->message = 'Standard analyte created successfully!';
            }

            DB::commit();
            $this->closeStandardAnalyteModal();
            $this->messageType = 'success';
            
            // Dispatch success events
            $this->dispatch('standard-analyte-saved', ['message' => $this->message]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
            
            // Dispatch error events
            $this->dispatch('validation-error', ['errors' => [$e->getMessage()]]);
        }
    }

    public function deleteStandardAnalyte($id)
    {
        try {
            StandardAnalytes::findOrFail($id)->delete();
            $this->message = 'Standard analyte deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeStandardAnalyteModal()
    {
        $this->showStandardAnalyteModal = false;
        $this->resetStandardAnalyteForm();
    }

    public function resetStandardAnalyteForm()
    {
        $this->standardAnalyteForm = [
            'analyte_id' => null,
            'standard_value_id' => null,
            'standard_value_type' => 'is_range', // Default to range
            'low' => '',
            'high' => '',
            'standard_is_value' => '',
            'comments' => '',
            'recommendations' => '',
            'expected_value' => '',
            'absolute_tolerance' => false,
            'is_active' => true,
            'mean_value' => '',
            'rel_std_dev' => '',
            'tolerance_1' => '',
            'tolerance_2' => '',
            'value_type' => 'range', // Default to range
            'matrix_operator' => '',
            'matrix_value' => ''
        ];
        $this->editingStandardAnalyte = null;
        $this->resetSearchableSelectState();
    }

    public function searchAnalytes(): void
    {
        $this->showAnalyteDropdown = true;
        $search = $this->analyteSearch;

        $this->filteredAnalytes = Analyte::query()
            ->where('active', 1)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    public function selectAnalyte(int|string $id): void
    {
        $analyte = collect($this->analytes)->firstWhere('id', (int) $id);
        if (!$analyte) {
            return;
        }

        $this->standardAnalyteForm['analyte_id'] = $analyte->id;
        $this->selectedAnalyteName = $analyte->name;
        $this->analyteSearch = $analyte->name;
        $this->showAnalyteDropdown = false;
    }

    public function clearAnalyte(): void
    {
        $this->standardAnalyteForm['analyte_id'] = null;
        $this->selectedAnalyteName = '';
        $this->analyteSearch = '';
        $this->showAnalyteDropdown = false;
    }

    public function searchStandardValues(): void
    {
        $this->showStandardValueDropdown = true;
        $search = $this->standardValueSearch;

        $this->filteredStandardValues = StandardValue::query()
            ->where('status', 1)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    public function selectStandardValue(int|string $id): void
    {
        $standardValue = collect($this->standardValues)->firstWhere('id', (int) $id);
        if (!$standardValue) {
            return;
        }

        $this->standardAnalyteForm['standard_value_id'] = $standardValue->id;
        $this->selectedStandardValueName = $standardValue->name;
        $this->standardValueSearch = $standardValue->name;
        $this->showStandardValueDropdown = false;
    }

    public function clearStandardValue(): void
    {
        $this->standardAnalyteForm['standard_value_id'] = null;
        $this->selectedStandardValueName = '';
        $this->standardValueSearch = '';
        $this->showStandardValueDropdown = false;
    }

    protected function syncSearchableSelectLabels(): void
    {
        $analyteId = $this->standardAnalyteForm['analyte_id'] ?? null;
        if ($analyteId) {
            $analyte = collect($this->analytes)->firstWhere('id', (int) $analyteId);
            $this->selectedAnalyteName = $analyte->name ?? '';
            $this->analyteSearch = $this->selectedAnalyteName;
        }

        $standardValueId = $this->standardAnalyteForm['standard_value_id'] ?? null;
        if ($standardValueId) {
            $standardValue = collect($this->standardValues)->firstWhere('id', (int) $standardValueId);
            $this->selectedStandardValueName = $standardValue->name ?? '';
            $this->standardValueSearch = $this->selectedStandardValueName;
        }
    }

    protected function resetSearchableSelectState(): void
    {
        $this->analyteSearch = '';
        $this->standardValueSearch = '';
        $this->selectedAnalyteName = '';
        $this->selectedStandardValueName = '';
        $this->showAnalyteDropdown = false;
        $this->showStandardValueDropdown = false;
        $this->filteredAnalytes = [];
        $this->filteredStandardValues = [];
    }

    public function toggleValueType($type)
    {
        $this->standardAnalyteForm['standard_value_type'] = $type;
    }

    public function updatedStandardAnalyteFormValueType()
    {
        // Clear related fields when switching value types
        if ($this->standardAnalyteForm['value_type'] === 'range') {
            // Clear use value fields
            $this->standardAnalyteForm['standard_value_id'] = null;
            $this->standardAnalyteForm['matrix_operator'] = '';
            $this->standardAnalyteForm['matrix_value'] = '';
            $this->standardAnalyteForm['standard_is_value'] = '';
            $this->selectedStandardValueName = '';
            $this->standardValueSearch = '';
            $this->showStandardValueDropdown = false;
            
            // Set correct standard_value_type for range
            $this->standardAnalyteForm['standard_value_type'] = 'is_range';
            
        } elseif ($this->standardAnalyteForm['value_type'] === 'use_value') {
            // Clear range fields
            $this->standardAnalyteForm['low'] = '';
            $this->standardAnalyteForm['high'] = '';
            
            // Set correct standard_value_type for use value
            $this->standardAnalyteForm['standard_value_type'] = 'is_standard_value';
        }

        $this->syncSearchableSelectLabels();
        
        // Dispatch event for JavaScript enhancement
        $this->dispatch('value-type-changed', $this->standardAnalyteForm['value_type']);
    }

    /**
     * Helper method to migrate old data structure to new structure
     */
    public function migrateLegacyData()
    {
        $legacyRecords = StandardAnalytes::whereNull('value_type')
            ->orWhere('value_type', '')
            ->get();

        foreach ($legacyRecords as $record) {
            $valueType = 'range'; // default
            if ($record->standard_value_type === 'is_range') {
                $valueType = 'range';
            } elseif ($record->standard_value_type === 'is_standard_value') {
                $valueType = 'use_value';
            }

            $record->update([
                'value_type' => $valueType,
                'matrix_operator' => $record->matrix_operator ?? '',
                'matrix_value' => $record->matrix_value ?? ''
            ]);
        }

        $this->message = 'Legacy data migrated successfully!';
        $this->messageType = 'success';
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.standards.standard-analytes-manager');
    }
}
