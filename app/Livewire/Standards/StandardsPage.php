<?php

namespace App\Livewire\Standards;

use Livewire\Component;
use Livewire\WithPagination;
use App\Standards;
use App\StandardValue;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StandardsPage extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination;

    // Tab Management
    public $activeTab = 'standards';

    // Standards Management
    public $editingStandard = null;
    public $showStandardModal = false;
    
    // Standard Form
    public $standardForm = [
        'name' => '',
        'code' => '',
        'main_standard' => false,
        'is_qc_standard' => false,
        'qc_type_id' => null,
        'qc_scheme_ids' => '',
        'status' => true
    ];

    // Standard Values Management
    public $editingStandardValue = null;
    public $showStandardValueModal = false;
    
    // Standard Value Form
    public $standardValueForm = [
        'name' => '',
        'code' => '',
        'status' => true
    ];

    // Supporting Data
    public $qcTypes = [];
    public $qcSchemes = [];

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
        'standardForm.name' => 'required|string|max:255',
        'standardForm.code' => 'required|string|max:255|unique:standards,code',
        'standardValueForm.name' => 'required|string|max:255',
        'standardValueForm.code' => 'required|string|max:255|unique:standard_values,code',
    ];

    protected $messages = [
        'standardForm.name.required' => 'Standard name is required.',
        'standardForm.code.required' => 'Standard code is required.',
        'standardForm.code.unique' => 'This standard code already exists.',
        'standardValueForm.name.required' => 'Standard value name is required.',
        'standardValueForm.code.required' => 'Standard value code is required.',
        'standardValueForm.code.unique' => 'This standard value code already exists.',
    ];

    public function mount()
    {
        $this->loadInitialData();
    }

    public function loadInitialData()
    {
        // Load QC types and schemes if they exist
        $this->qcTypes = collect(); // Placeholder - implement based on your QC module
        $this->qcSchemes = collect(); // Placeholder - implement based on your QC module
    }

    public function getStandardsProperty()
    {
        $query = Standards::query();

        if ($this->search) {
            $this->applyCaseInsensitiveSearch($query, ['name', 'code'], (string) $this->search);
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return $query->orderBy('name', 'asc')->paginate($this->perPage);
    }

    public function getStandardValuesProperty()
    {
        $query = StandardValue::query();

        if ($this->search) {
            $this->applyCaseInsensitiveSearch($query, ['name', 'code'], (string) $this->search);
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return $query->orderBy('name', 'asc')->paginate($this->perPage);
    }

    // Tab Management
    public function switchTab($tab)
    {
        $this->dispatch('tab-switching');
        
        $this->activeTab = $tab;
        $this->resetPage();
        $this->clearFilters();
        
        $this->dispatch('tab-switched');
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

    // Standard Methods
    public function showCreateStandardModal()
    {
        $this->resetStandardForm();
        $this->showStandardModal = true;
    }

    public function showEditStandardModal($id)
    {
        $standard = Standards::with('qcSchemes')->findOrFail($id);
        $this->standardForm = [
            'name' => $standard->name,
            'code' => $standard->code,
            'main_standard' => $standard->main_standard,
            'is_qc_standard' => $standard->is_qc_standard,
            'qc_type_id' => $standard->qc_type_id,
            'qc_scheme_ids' => $standard->qcSchemes->pluck('id')->implode(','),
            'status' => $standard->status
        ];
        $this->editingStandard = $id;
        $this->showStandardModal = true;
    }

    public function saveStandard()
    {
        $this->validate([
            'standardForm.name' => 'required|string|max:255',
            'standardForm.code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('standards', 'code')->ignore($this->editingStandard)
            ],
        ]);

        try {
            DB::beginTransaction();

            $schemeIds = array_values(array_filter(array_map(
                'trim',
                explode(',', (string) ($this->standardForm['qc_scheme_ids'] ?? ''))
            )));

            if ($this->editingStandard) {
                $standard = Standards::findOrFail($this->editingStandard);
                $standard->update([
                    'name' => $this->standardForm['name'],
                    'code' => $this->standardForm['code'],
                    'main_standard' => $this->standardForm['main_standard'],
                    'is_qc_standard' => $this->standardForm['is_qc_standard'],
                    'qc_type_id' => $this->standardForm['qc_type_id'],
                    'status' => $this->standardForm['status'],
                    'edited_by' => auth()->user()->id,
                ]);
                $standard->syncQcSchemes($schemeIds);
                $this->message = 'Standard updated successfully!';
            } else {
                $standard = Standards::create([
                    'name' => $this->standardForm['name'],
                    'code' => $this->standardForm['code'],
                    'main_standard' => $this->standardForm['main_standard'],
                    'is_qc_standard' => $this->standardForm['is_qc_standard'],
                    'qc_type_id' => $this->standardForm['qc_type_id'],
                    'status' => $this->standardForm['status'],
                    'edited_by' => auth()->user()->id,
                ]);
                $standard->syncQcSchemes($schemeIds);
                $this->message = 'Standard created successfully!';
            }

            DB::commit();
            $this->closeStandardModal();
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteStandard($id)
    {
        try {
            Standards::findOrFail($id)->delete();
            $this->message = 'Standard deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeStandardModal()
    {
        $this->showStandardModal = false;
        $this->resetStandardForm();
    }

    public function resetStandardForm()
    {
        $this->standardForm = [
            'name' => '',
            'code' => '',
            'main_standard' => false,
            'is_qc_standard' => false,
            'qc_type_id' => null,
            'qc_scheme_ids' => '',
            'status' => true
        ];
        $this->editingStandard = null;
    }

    // Standard Value Methods
    public function showCreateStandardValueModal()
    {
        $this->resetStandardValueForm();
        $this->showStandardValueModal = true;
    }

    public function showEditStandardValueModal($id)
    {
        $standardValue = StandardValue::findOrFail($id);
        $this->standardValueForm = [
            'name' => $standardValue->name,
            'code' => $standardValue->code,
            'status' => $standardValue->status
        ];
        $this->editingStandardValue = $id;
        $this->showStandardValueModal = true;
    }

    public function saveStandardValue()
    {
        $this->validate([
            'standardValueForm.name' => 'required|string|max:255',
            'standardValueForm.code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('standard_values', 'code')->ignore($this->editingStandardValue)
            ],
        ]);

        try {
            DB::beginTransaction();

            if ($this->editingStandardValue) {
                $standardValue = StandardValue::findOrFail($this->editingStandardValue);
                $standardValue->update([
                    'name' => $this->standardValueForm['name'],
                    'code' => $this->standardValueForm['code'],
                    'status' => $this->standardValueForm['status'],
                    'edited_by' => auth()->user()->id,
                ]);
                $this->message = 'Standard value updated successfully!';
            } else {
                StandardValue::create([
                    'name' => $this->standardValueForm['name'],
                    'code' => $this->standardValueForm['code'],
                    'status' => $this->standardValueForm['status'],
                    'edited_by' => auth()->user()->id,
                ]);
                $this->message = 'Standard value created successfully!';
            }

            DB::commit();
            $this->closeStandardValueModal();
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteStandardValue($id)
    {
        try {
            StandardValue::findOrFail($id)->delete();
            $this->message = 'Standard value deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeStandardValueModal()
    {
        $this->showStandardValueModal = false;
        $this->resetStandardValueForm();
    }

    public function resetStandardValueForm()
    {
        $this->standardValueForm = [
            'name' => '',
            'code' => '',
            'status' => true
        ];
        $this->editingStandardValue = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.standards.standards-page');
    }
}
