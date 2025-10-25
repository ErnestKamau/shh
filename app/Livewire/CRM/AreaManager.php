<?php

namespace App\Livewire\CRM;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Area;
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

    // Bulk Operations
    public $selectedAreas = [];
    public $selectAll = false;

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

        return $query->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
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

    public function render()
    {
        return view('livewire.c-r-m.area-manager', [
            'areas' => $this->areas
        ]);
    }
}
