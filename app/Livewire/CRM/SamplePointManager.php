<?php

namespace App\Livewire\CRM;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\SamplePoint;
use App\SampleType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class SamplePointManager extends Component
{
    use WithPagination, WithFileUploads;

    // Sample Point Management
    public $editingSamplePoint = null;
    public $showSamplePointModal = false;
    public $showBulkUploadModal = false;
    
    // Sample Point Form
    public $samplePointForm = [
        'name' => '',
        'code' => '',
    ];

    // Bulk Upload
    public $bulkFile = null;

    // Search and Filter
    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $sampleTypeFilter = '';

    // Bulk Operations
    public $selectedSamplePoints = [];
    public $selectAll = false;

    // Sample Type Assignment
    public $showAssignSampleTypeModal = false;
    public $selectedSampleTypes = [];
    public $sampleTypeSearch = '';
    public $sampleTypeDropdownOpen = false;

    // UI State
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];
    
    // Cache for sample types
    private $sampleTypesCache = null;

    

    protected $rules = [
        'samplePointForm.name' => 'required|string|max:255',
        'samplePointForm.code' => 'required|string|max:255',
    ];

    protected $messages = [
        'samplePointForm.name.required' => 'Sample point name is required.',
        'samplePointForm.code.required' => 'Sample point code is required.',
    ];

    public function getSamplePointsProperty()
    {
        $query = SamplePoint::with(['creator'])
            ->orderBy('name');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        if ($this->sampleTypeFilter) {
            // Use Eloquent relationship for filtering
            $query->whereHas('sampleTypes', function($q) {
                $q->where('sample_types.id', $this->sampleTypeFilter);
            });
        }

        return $query->paginate($this->perPage);
    }

    public function getSampleTypesProperty()
    {
        $companyId = getUserCompany();
        return SampleType::where('active', 1)
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSampleTypeFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->sampleTypeFilter = '';
        $this->resetPage();
    }

    // Bulk Operations
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedSamplePoints = $this->samplePoints->pluck('id')->toArray();
        } else {
            $this->selectedSamplePoints = [];
        }
    }

    public function bulkDelete()
    {
        if (empty($this->selectedSamplePoints)) {
            $this->message = 'Please select sample points to delete.';
            $this->messageType = 'error';
            return;
        }

        try {
            DB::beginTransaction();
            
            SamplePoint::whereIn('id', $this->selectedSamplePoints)->delete();
            
            DB::commit();
            
            $this->selectedSamplePoints = [];
            $this->selectAll = false;
            $this->message = 'Selected sample points deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // Sample Point Methods
    public function showCreateSamplePointModal()
    {
        $this->resetSamplePointForm();
        $this->showSamplePointModal = true;
    }

    public function showEditSamplePointModal($id)
    {
        $samplePoint = SamplePoint::findOrFail($id);
        
        $this->samplePointForm = [
            'name' => $samplePoint->name,
            'code' => $samplePoint->code,
        ];
        
        $this->editingSamplePoint = $samplePoint;
        $this->showSamplePointModal = true;
    }

    public function saveSamplePoint()
    {
        $rules = $this->rules;
        
        // Add unique validation for code, excluding current record if editing
        if ($this->editingSamplePoint) {
            $rules['samplePointForm.code'] = 'required|string|max:255|unique:crm_sample_points,code,' . $this->editingSamplePoint->id;
        } else {
            $rules['samplePointForm.code'] = 'required|string|max:255|unique:crm_sample_points,code';
        }
        
        $this->validate($rules);

        try {
            DB::beginTransaction();

            if ($this->editingSamplePoint) {
                // Update existing sample point
                $samplePoint = $this->editingSamplePoint;
                $samplePoint->name = $this->samplePointForm['name'];
                $samplePoint->code = $this->samplePointForm['code'];
            } else {
                // Create new sample point
                $samplePoint = new SamplePoint();
                $samplePoint->name = $this->samplePointForm['name'];
                $samplePoint->code = $this->samplePointForm['code'];
                $samplePoint->created_by = Auth::id();
            }

            $samplePoint->save();

            DB::commit();
            
            $this->closeSamplePointModal();
            $this->message = $this->editingSamplePoint ? 'Sample point updated successfully!' : 'Sample point created successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteSamplePoint($id)
    {
        try {
            DB::beginTransaction();

            $samplePoint = SamplePoint::findOrFail($id);
            $samplePoint->delete();

            DB::commit();
            
            $this->message = 'Sample point deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeSamplePointModal()
    {
        $this->showSamplePointModal = false;
        $this->resetSamplePointForm();
    }

    public function resetSamplePointForm()
    {
        $this->samplePointForm = [
            'name' => '',
            'code' => '',
        ];
        $this->editingSamplePoint = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    // Bulk Upload Methods
    public function openBulkUploadModal()
    {
        $this->showBulkUploadModal = true;
        $this->bulkFile = null;
        $this->dispatch('bulk-upload-modal-opened');
    }

    public function closeBulkUploadModal()
    {
        $this->showBulkUploadModal = false;
        $this->bulkFile = null;
    }

    public function downloadTemplate()
    {
        $filename = 'sample_points_template.xlsx';
        
        $headers = [
            ['Code', 'Name']
        ];

        return Excel::download(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $data;
            
            public function __construct($data)
            {
                $this->data = $data;
            }
            
            public function array(): array
            {
                return $this->data;
            }
        }, $filename);
    }

    public function processBulkUpload()
    {
        $this->validate([
            'bulkFile' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $path = $this->bulkFile->getRealPath();
            $data = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                public function array(array $array)
                {
                    return $array;
                }
            }, $path);

            if (empty($data) || empty($data[0])) {
                throw new \Exception('The uploaded file is empty.');
            }

            $rows = $data[0];
            $header = array_shift($rows); // Remove header row

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because of header and 0-index
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $code = trim($row[0] ?? '');
                $name = trim($row[1] ?? '');

                // Validate row data
                $validator = Validator::make([
                    'code' => $code,
                    'name' => $name,
                ], [
                    'code' => 'required|string|max:255|unique:crm_sample_points,code',
                    'name' => 'required|string|max:255',
                ]);

                if ($validator->fails()) {
                    $errorCount++;
                    $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // Create sample point
                SamplePoint::create([
                    'code' => $code,
                    'name' => $name,
                    'created_by' => Auth::id(),
                ]);

                $successCount++;
            }

            DB::commit();

            $this->closeBulkUploadModal();
            
            if ($errorCount > 0) {
                $this->message = "{$successCount} sample point(s) created successfully. {$errorCount} row(s) failed. Errors: " . implode(' | ', array_slice($errors, 0, 5));
                $this->messageType = 'warning';
            } else {
                $this->message = "{$successCount} sample point(s) created successfully!";
                $this->messageType = 'success';
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error processing file: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    // Sample Type Assignment Methods
    public function showAssignSampleTypeModalInitiator()
    {
       
        
        if (empty($this->selectedSamplePoints)) {
            $this->message = 'Please select at least one sample point.';
            $this->messageType = 'error';
            return;
        }
        
        $this->selectedSampleTypes = [];
        $this->sampleTypeSearch = '';
        $this->sampleTypeDropdownOpen = false;
        $this->showAssignSampleTypeModal = true;
    }

    public function closeAssignSampleTypeModal()
    {
        $this->showAssignSampleTypeModal = false;
        $this->selectedSampleTypes = [];
        $this->sampleTypeSearch = '';
        $this->sampleTypeDropdownOpen = false;
    }

    public function toggleSampleType($sampleTypeId)
    {
        $index = array_search($sampleTypeId, $this->selectedSampleTypes);
        if ($index !== false) {
            unset($this->selectedSampleTypes[$index]);
            $this->selectedSampleTypes = array_values($this->selectedSampleTypes);
        } else {
            $this->selectedSampleTypes[] = $sampleTypeId;
        }
    }

    public function isSampleTypeSelected($sampleTypeId)
    {
        return in_array($sampleTypeId, $this->selectedSampleTypes);
    }

    public function getFilteredSampleTypesProperty()
    {
        $sampleTypes = $this->sampleTypes;
        if (empty($this->sampleTypeSearch)) {
            return $sampleTypes;
        }
        $search = strtolower($this->sampleTypeSearch);
        return $sampleTypes->filter(function($st) use ($search) {
            return str_contains(strtolower($st->name), $search) || str_contains(strtolower($st->code), $search);
        });
    }

    public function getSelectedSampleTypeNames()
    {
        if (empty($this->selectedSampleTypes)) {
            return '';
        }
        $names = [];
        foreach ($this->selectedSampleTypes as $id) {
            $st = $this->sampleTypes->firstWhere('id', $id);
            if ($st) {
                $names[] = $st->name;
            }
        }
        if (count($names) > 3) {
            return implode(', ', array_slice($names, 0, 3)) . ' +' . (count($names) - 3) . ' more';
        }
        return implode(', ', $names);
    }

    public function toggleSampleTypeDropdown()
    {
        $this->sampleTypeDropdownOpen = !$this->sampleTypeDropdownOpen;
    }

    public function assignSampleTypes()
    {
        if (empty($this->selectedSamplePoints)) {
            $this->message = 'Please select at least one sample point.';
            $this->messageType = 'error';
            return;
        }

        if (empty($this->selectedSampleTypes)) {
            $this->message = 'Please select at least one sample type.';
            $this->messageType = 'error';
            return;
        }

        try {
            DB::beginTransaction();

            $assignedCount = 0;
            foreach ($this->selectedSamplePoints as $samplePointId) {
                $samplePoint = SamplePoint::find($samplePointId);
                if (!$samplePoint) {
                    continue;
                }
                
                foreach ($this->selectedSampleTypes as $sampleTypeId) {
                    // Check if relation already exists using Eloquent
                    $exists = $samplePoint->sampleTypes()->where('sample_types.id', $sampleTypeId)->exists();

                    if (!$exists) {
                        // Use Eloquent attach method
                        $samplePoint->sampleTypes()->attach($sampleTypeId);
                        $assignedCount++;
                    }
                }
            }

            DB::commit();

            $this->closeAssignSampleTypeModal();
            $this->selectedSamplePoints = [];
            $this->selectAll = false;
            $this->message = "Sample types assigned successfully! ({$assignedCount} relation(s) created)";
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error assigning sample types: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function getSampleTypesForSamplePoint($samplePointId): string
    {
        static $sampleTypesCache = null;
        
        if ($sampleTypesCache === null) {
            $samplePointIds = $this->samplePoints->pluck('id')->toArray();
            
            if (empty($samplePointIds)) {
                return '';
            }
            
            // Use Eloquent to get sample types for sample points
            $samplePoints = SamplePoint::whereIn('id', $samplePointIds)
                ->with(['sampleTypes' => function($query) {
                    $query->where('active', 1)->select('sample_types.id', 'sample_types.name');
                }])
                ->get();
            
            $sampleTypesCache = $samplePoints->mapWithKeys(function($samplePoint) {
                return [$samplePoint->id => $samplePoint->sampleTypes->pluck('name')->implode(', ')];
            })->toArray();
        }
        
        return $sampleTypesCache[$samplePointId] ?? '';
    }

    public function render()
    {
        return view('livewire.c-r-m.sample-point-manager', [
            'samplePoints' => $this->samplePoints
        ]);
    }
}
