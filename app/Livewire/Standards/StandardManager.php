<?php

namespace App\Livewire\Standards;

use Livewire\Component;
use Livewire\WithPagination;
use App\Standards;
use App\StandardValue;
use App\StandardAnalytes;
use App\Analyte;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StandardManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination;

    // Standards Management
    public $standards = [];
    public $selectedStandard = null;
    public $showStandardAnalytes = false;
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
    public $standardValues = [];
    public $editingStandardValue = null;
    public $showStandardValueModal = false;
    
    // Standard Value Form
    public $standardValueForm = [
        'name' => '',
        'code' => '',
        'status' => true
    ];

    // Standard Analytes Management
    public $standardAnalytes = [];
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
    public $qcTypes = [];
    public $qcSchemes = [];

    // Search and Filter
    public $search = '';
    public $statusFilter = '';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';

    protected $rules = [
        'standardForm.name' => 'required|string|max:255',
        'standardForm.code' => 'required|string|max:255|unique:standards,code',
        'standardValueForm.name' => 'required|string|max:255',
        'standardValueForm.code' => 'required|string|max:255|unique:standard_values,code',
        'standardAnalyteForm.analyte_id' => 'required|exists:analytes,id',
        'standardAnalyteForm.standard_value_id' => 'nullable|exists:standard_values,id',
    ];

    protected $messages = [
        'standardForm.name.required' => 'Standard name is required.',
        'standardForm.code.required' => 'Standard code is required.',
        'standardForm.code.unique' => 'This standard code already exists.',
        'standardValueForm.name.required' => 'Standard value name is required.',
        'standardValueForm.code.required' => 'Standard value code is required.',
        'standardValueForm.code.unique' => 'This standard value code already exists.',
        'standardAnalyteForm.analyte_id.required' => 'Analyte selection is required.',
    ];

    public function mount()
    {
        $this->loadInitialData();
        $this->loadStandards();
        $this->loadStandardValues();
    }

    public function loadInitialData()
    {
        $this->analytes = Analyte::where('active', 1)->get();
        // Load QC types and schemes if they exist
        $this->qcTypes = collect(); // Placeholder - implement based on your QC module
        $this->qcSchemes = collect(); // Placeholder - implement based on your QC module
    }

    public function loadStandards()
    {
        $query = Standards::query();

        if ($this->search) {
            $this->applyCaseInsensitiveSearch($query, ['name', 'code'], (string) $this->search);
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $this->standards = $query->orderBy('name', 'asc')->get();
    }

    public function loadStandardValues()
    {
        $this->standardValues = StandardValue::where('status', 1)->orderBy('name', 'asc')->get();
    }

    public function updatedSearch()
    {
        $this->loadStandards();
    }

    public function updatedStatusFilter()
    {
        $this->loadStandards();
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
                
                // If setting as main standard, unset others
                if ($this->standardForm['main_standard']) {
                    Standards::where('id', '!=', $this->editingStandard)->update(['main_standard' => false]);
                }
                
                $standard->update([
                    'name' => $this->standardForm['name'],
                    'code' => $this->standardForm['code'],
                    'main_standard' => $this->standardForm['main_standard'],
                    'is_qc_standard' => $this->standardForm['is_qc_standard'],
                    'qc_type_id' => $this->standardForm['qc_type_id'],
                    'status' => $this->standardForm['status'] ?? true,
                    'edited_by' => auth()->user()->id,
                ]);
                $standard->syncQcSchemes($schemeIds);
                $this->message = 'Standard updated successfully!';
            } else {
                // If setting as main standard, unset others
                if ($this->standardForm['main_standard']) {
                    Standards::query()->update(['main_standard' => false]);
                }
                
                $standard = Standards::create([
                    'name' => $this->standardForm['name'],
                    'code' => $this->standardForm['code'],
                    'main_standard' => $this->standardForm['main_standard'],
                    'is_qc_standard' => $this->standardForm['is_qc_standard'],
                    'qc_type_id' => $this->standardForm['qc_type_id'],
                    'status' => $this->standardForm['status'] ?? true,
                    'edited_by' => auth()->user()->id,
                ]);
                $standard->syncQcSchemes($schemeIds);
                $this->message = 'Standard created successfully!';
            }

            DB::commit();
            $this->loadStandards();
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
            DB::beginTransaction();

            $standard = Standards::findOrFail($id);
            
            // Delete associated standard analytes
            $standard->standardAnalytes()->delete();
            $standard->delete();

            DB::commit();
            $this->loadStandards();
            $this->message = 'Standard deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function selectStandard($id)
    {
        $this->selectedStandard = $id;
        $this->loadStandardAnalytes();
        $this->showStandardAnalytes = true;
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
    public function loadStandardAnalytes()
    {
        if ($this->selectedStandard) {
            $this->standardAnalytes = StandardAnalytes::where('standard_id', $this->selectedStandard)
                ->with(['analyte', 'standardValue'])
                ->orderBy('id', 'asc')
                ->get();
        }
    }

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
                    'status' => $this->standardValueForm['status'] ?? true,
                    'edited_by' => auth()->user()->id,
                ]);
                $this->message = 'Standard value updated successfully!';
            } else {
                StandardValue::create([
                    'name' => $this->standardValueForm['name'],
                    'code' => $this->standardValueForm['code'],
                    'status' => $this->standardValueForm['status'] ?? true,
                    'edited_by' => auth()->user()->id,
                ]);
                $this->message = 'Standard value created successfully!';
            }

            DB::commit();
            $this->loadStandardValues();
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
            $this->loadStandardValues();
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

    // Standard Analyte Methods
    public function showCreateStandardAnalyteModal()
    {
        $this->resetStandardAnalyteForm();
        $this->showStandardAnalyteModal = true;
    }

    public function showEditStandardAnalyteModal($id)
    {
        $standardAnalyte = StandardAnalytes::findOrFail($id);
        $this->standardAnalyteForm = [
            'analyte_id' => $standardAnalyte->analyte_id,
            'standard_value_id' => $standardAnalyte->standard_value_id,
            'standard_value_type' => $standardAnalyte->standard_value_type,
            'low' => $standardAnalyte->low,
            'high' => $standardAnalyte->high,
            'standard_is_value' => $standardAnalyte->standard_is_value,
            'comments' => $standardAnalyte->comments,
            'recommendations' => $standardAnalyte->recommendations,
            'expected_value' => $standardAnalyte->expected_value,
            'absolute_tolerance' => $standardAnalyte->absolute_tolerance,
            'is_active' => $standardAnalyte->is_active,
            'mean_value' => $standardAnalyte->mean_value,
            'rel_std_dev' => $standardAnalyte->rel_std_dev,
            'tolerance_1' => $standardAnalyte->tolerance_1,
            'tolerance_2' => $standardAnalyte->tolerance_2,
            'value_type' => $standardAnalyte->value_type
        ];
        $this->editingStandardAnalyte = $id;
        $this->showStandardAnalyteModal = true;
    }

    public function saveStandardAnalyte()
    {
        $this->validate([
            'standardAnalyteForm.analyte_id' => 'required|exists:analytes,id',
            'standardAnalyteForm.standard_value_id' => 'nullable|exists:standard_values,id',
        ]);

        try {
            DB::beginTransaction();

            if ($this->editingStandardAnalyte) {
                $standardAnalyte = StandardAnalytes::findOrFail($this->editingStandardAnalyte);
                $standardAnalyte->update([
                    'analyte_id' => $this->standardAnalyteForm['analyte_id'],
                    'standard_value_id' => $this->standardAnalyteForm['standard_value_id'],
                    'standard_value_type' => $this->standardAnalyteForm['standard_value_type'],
                    'low' => $this->standardAnalyteForm['low'],
                    'high' => $this->standardAnalyteForm['high'],
                    'standard_is_value' => $this->standardAnalyteForm['standard_is_value'],
                    'comments' => $this->standardAnalyteForm['comments'],
                    'recommendations' => $this->standardAnalyteForm['recommendations'],
                    'expected_value' => $this->standardAnalyteForm['expected_value'],
                    'absolute_tolerance' => $this->standardAnalyteForm['absolute_tolerance'],
                    'is_active' => $this->standardAnalyteForm['is_active'] ?? true,
                    'mean_value' => $this->standardAnalyteForm['mean_value'],
                    'rel_std_dev' => $this->standardAnalyteForm['rel_std_dev'],
                    'tolerance_1' => $this->standardAnalyteForm['tolerance_1'],
                    'tolerance_2' => $this->standardAnalyteForm['tolerance_2'],
                    'value_type' => $this->standardAnalyteForm['value_type']
                ]);
                $this->message = 'Standard analyte updated successfully!';
            } else {
                StandardAnalytes::create([
                    'analyte_id' => $this->standardAnalyteForm['analyte_id'],
                    'standard_id' => $this->selectedStandard,
                    'standard_value_id' => $this->standardAnalyteForm['standard_value_id'],
                    'standard_value_type' => $this->standardAnalyteForm['standard_value_type'],
                    'low' => $this->standardAnalyteForm['low'],
                    'high' => $this->standardAnalyteForm['high'],
                    'standard_is_value' => $this->standardAnalyteForm['standard_is_value'],
                    'comments' => $this->standardAnalyteForm['comments'],
                    'recommendations' => $this->standardAnalyteForm['recommendations'],
                    'expected_value' => $this->standardAnalyteForm['expected_value'],
                    'absolute_tolerance' => $this->standardAnalyteForm['absolute_tolerance'],
                    'is_active' => $this->standardAnalyteForm['is_active'] ?? true,
                    'mean_value' => $this->standardAnalyteForm['mean_value'],
                    'rel_std_dev' => $this->standardAnalyteForm['rel_std_dev'],
                    'tolerance_1' => $this->standardAnalyteForm['tolerance_1'],
                    'tolerance_2' => $this->standardAnalyteForm['tolerance_2'],
                    'value_type' => $this->standardAnalyteForm['value_type']
                ]);
                $this->message = 'Standard analyte created successfully!';
            }

            DB::commit();
            $this->loadStandardAnalytes();
            $this->closeStandardAnalyteModal();
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteStandardAnalyte($id)
    {
        try {
            StandardAnalytes::findOrFail($id)->delete();
            $this->loadStandardAnalytes();
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
        $this->editingStandardAnalyte = null;
    }

    public function toggleValueType($type)
    {
        $this->standardAnalyteForm['standard_value_type'] = $type;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.standards.standard-manager');
    }
}