<?php

namespace App\Livewire\CRM;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Area;
use App\SampleType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class AreaManager extends Component
{
    use WithPagination, WithFileUploads;

    // Area Management
    public $editingArea = null;
    public $showAreaModal = false;
    public $showBulkUploadModal = false;
    
    // Area Form
    public $areaForm = [
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
    public $selectedAreas = [];
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

    protected $rules = [
        'areaForm.name' => 'required|string|max:255',
        'areaForm.code' => 'required|string|max:255',
    ];

    protected $messages = [
        'areaForm.name.required' => 'Area name is required.',
        'areaForm.code.required' => 'Area code is required.',
    ];

    public function getAreasProperty()
    {
        $query = Area::with(['creator'])
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
            $query->whereIn('id', function($q) {
                $q->select('area_id')
                  ->from('sampletype_area_relation')
                  ->where('sample_type_id', $this->sampleTypeFilter);
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
            $this->selectedAreas = $this->areas->pluck('id')->toArray();
        } else {
            $this->selectedAreas = [];
        }
    }

    public function bulkDelete()
    {
        if (empty($this->selectedAreas)) {
            $this->message = 'Please select areas to delete.';
            $this->messageType = 'error';
            return;
        }

        try {
            DB::beginTransaction();
            
            Area::whereIn('id', $this->selectedAreas)->delete();
            
            DB::commit();
            
            $this->selectedAreas = [];
            $this->selectAll = false;
            $this->message = 'Selected areas deleted successfully!';
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

    // Area Methods
    public function showCreateAreaModal()
    {
        $this->resetAreaForm();
        $this->showAreaModal = true;
    }

    public function showEditAreaModal($id)
    {
        $area = Area::findOrFail($id);
        
        $this->areaForm = [
            'name' => $area->name,
            'code' => $area->code,
        ];
        
        $this->editingArea = $area;
        $this->showAreaModal = true;
    }

    public function saveArea()
    {
        $rules = $this->rules;
        
        // Add unique validation for code, excluding current record if editing
        if ($this->editingArea) {
            $rules['areaForm.code'] = 'required|string|max:255|unique:crm_areas,code,' . $this->editingArea->id;
        } else {
            $rules['areaForm.code'] = 'required|string|max:255|unique:crm_areas,code';
        }
        
        $this->validate($rules);

        try {
            DB::beginTransaction();

            if ($this->editingArea) {
                // Update existing area
                $area = $this->editingArea;
                $area->name = $this->areaForm['name'];
                $area->code = $this->areaForm['code'];
            } else {
                // Create new area
                $area = new Area();
                $area->name = $this->areaForm['name'];
                $area->code = $this->areaForm['code'];
                $area->created_by = Auth::id();
            }

            $area->save();

            DB::commit();
            
            $this->closeAreaModal();
            $this->message = $this->editingArea ? 'Area updated successfully!' : 'Area created successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteArea($id)
    {
        try {
            DB::beginTransaction();

            $area = Area::findOrFail($id);
            $area->delete();

            DB::commit();
            
            $this->message = 'Area deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeAreaModal()
    {
        $this->showAreaModal = false;
        $this->resetAreaForm();
    }

    public function resetAreaForm()
    {
        $this->areaForm = [
            'name' => '',
            'code' => '',
        ];
        $this->editingArea = null;
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
        $filename = 'areas_template.xlsx';
        
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
                    'code' => 'required|string|max:255|unique:crm_areas,code',
                    'name' => 'required|string|max:255',
                ]);

                if ($validator->fails()) {
                    $errorCount++;
                    $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // Create area
                Area::create([
                    'code' => $code,
                    'name' => $name,
                    'created_by' => Auth::id(),
                ]);

                $successCount++;
            }

            DB::commit();

            $this->closeBulkUploadModal();
            
            if ($errorCount > 0) {
                $this->message = "{$successCount} area(s) created successfully. {$errorCount} row(s) failed. Errors: " . implode(' | ', array_slice($errors, 0, 5));
                $this->messageType = 'warning';
            } else {
                $this->message = "{$successCount} area(s) created successfully!";
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
        if (empty($this->selectedAreas)) {
            $this->message = 'Please select at least one area.';
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
        if (empty($this->selectedAreas)) {
            $this->message = 'Please select at least one area.';
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
            foreach ($this->selectedAreas as $areaId) {
                foreach ($this->selectedSampleTypes as $sampleTypeId) {
                    // Check if relation already exists
                    $exists = DB::table('sampletype_area_relation')
                        ->where('sample_type_id', $sampleTypeId)
                        ->where('area_id', $areaId)
                        ->exists();

                    if (!$exists) {
                        DB::table('sampletype_area_relation')->insert([
                            'sample_type_id' => $sampleTypeId,
                            'area_id' => $areaId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $assignedCount++;
                    }
                }
            }

            DB::commit();

            $this->closeAssignSampleTypeModal();
            $this->selectedAreas = [];
            $this->selectAll = false;
            $this->message = "Sample types assigned successfully! ({$assignedCount} relation(s) created)";
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error assigning sample types: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function getSampleTypesForArea($areaId): string
    {
        static $sampleTypesCache = null;
        
        if ($sampleTypesCache === null) {
            $areaIds = $this->areas->pluck('id')->toArray();
            
            if (empty($areaIds)) {
                return '';
            }
            
            $sampleTypesCache = DB::table('sampletype_area_relation')
                ->join('sample_types', 'sampletype_area_relation.sample_type_id', '=', 'sample_types.id')
                ->whereIn('sampletype_area_relation.area_id', $areaIds)
                ->where('sample_types.active', 1)
                ->select('sampletype_area_relation.area_id', 'sample_types.name')
                ->get()
                ->groupBy('area_id')
                ->map(function ($items) {
                    return $items->pluck('name')->implode(', ');
                })
                ->toArray();
        }
        
        return $sampleTypesCache[$areaId] ?? '';
    }

    public function render()
    {
        return view('livewire.c-r-m.area-manager', [
            'areas' => $this->areas
        ]);
    }
}
