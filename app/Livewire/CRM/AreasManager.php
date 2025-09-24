<?php

namespace App\Livewire\CRM;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SamplePointArea;
use App\Models\CRM\CRMCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AreasManager extends Component
{
    use WithPagination;

    // Customer Data
    public $customerId;
    public $customer;
    
    // Area Management
    public $editingArea = null;
    public $showAreaModal = false;
    
    // Area Form
    public $areaForm = [
        'name' => '',
        'code' => '',
        'description' => '',
        'active' => true
    ];

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
        'areaForm.name' => 'required|string|max:255',
        'areaForm.code' => 'required|string|max:50',
        'areaForm.description' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'areaForm.name.required' => 'Area name is required.',
        'areaForm.code.required' => 'Area code is required.',
    ];

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->customer = CRMCustomer::findOrFail($customerId);
    }

    public function getAreasProperty()
    {
        $query = SamplePointArea::where('crm_customer_id', $this->customerId)
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('active', $this->statusFilter);
            })
            ->orderBy('name');

        return $query->paginate($this->perPage);
    }

    public function showCreateAreaModal()
    {
        $this->resetAreaForm();
        $this->showAreaModal = true;
    }

    public function showEditAreaModal($id)
    {
        $area = SamplePointArea::findOrFail($id);
        
        $this->areaForm = [
            'name' => $area->name,
            'code' => $area->code,
            'description' => $area->description ?? '',
            'active' => $area->active
        ];
        
        $this->editingArea = $area;
        $this->showAreaModal = true;
    }

    public function saveArea()
    {
        // Update validation rules for code uniqueness
        $this->rules['areaForm.code'] = [
            'required',
            'string',
            'max:50',
            Rule::unique('sample_point_area', 'code')->where(function ($query) {
                return $query->where('crm_customer_id', $this->customerId);
            })->ignore($this->editingArea ? $this->editingArea->id : null)
        ];

        $this->validate();

        try {
            DB::beginTransaction();

            if ($this->editingArea) {
                // Update existing area
                $area = $this->editingArea;
            } else {
                // Create new area
                $area = new SamplePointArea();
                $area->crm_customer_id = $this->customerId;
            }

            $area->name = $this->areaForm['name'];
            $area->code = $this->areaForm['code'];
            $area->description = $this->areaForm['description'];
            $area->active = $this->areaForm['active'];
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

            $area = SamplePointArea::findOrFail($id);
            
            // Check if area has sample points
            if ($area->samplePoints()->count() > 0) {
                $this->message = 'Cannot delete area that has sample points. Please reassign or delete sample points first.';
                $this->messageType = 'error';
                return;
            }

            // Soft delete
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
            'description' => '',
            'active' => true
        ];
        $this->editingArea = null;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.crm.areas-manager');
    }
}
