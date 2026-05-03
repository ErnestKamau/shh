<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Models\CRM\SamplePoint;
use App\Livewire\Crm\BaseCrmComponent;
use Livewire\WithPagination;

class CustomerSamplePointsTab extends BaseCrmComponent
{
    use WithPagination;

    public $customer;
    public $search = '';
    public $perPage = 10;

    public $showForm = false;
    public $editingPoint = null;

    protected $listeners = [
        'sample-point-saved' => 'refreshSamplePoints',
        'sample-point-form-closed' => 'closeForm'
    ];

    public $isEditingLabel = false;
    public $labelColumn = '';
    public $customLabel = '';

    public function editLabel($column)
    {
        $this->labelColumn = $column;
        $this->customLabel = $this->customer->$column;
        $this->isEditingLabel = true;
    }

    public function cancelEditLabel()
    {
        $this->isEditingLabel = false;
        $this->labelColumn = '';
        $this->customLabel = '';
    }

    public function saveLabel()
    {
        $this->validate([
            'customLabel' => 'required|string|max:255',
        ]);

        if ($this->labelColumn) {
            $column = $this->labelColumn;
            $this->customer->$column = $this->customLabel;
            $this->customer->save();
            $this->dispatch('customer-updated');
            $this->showSuccess('Label updated successfully');
        }

        $this->cancelEditLabel();
    }

    protected $paginationTheme = 'bootstrap';

    public function mount($customer)
    {
        $this->customer = $customer;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function getSamplePointsProperty()
    {
        return SamplePoint::whereHas('unit', function ($query) {
            $query->where('crm_customer_id', $this->customer->id);
        })
        ->where(function($query) {
            $query->where('name', 'like', '%'.$this->search.'%');
        })
        ->paginate($this->perPage);
    }

    public function openPointForm($pointId = null)
    {
        $this->editingPoint = $pointId ? SamplePoint::find($pointId) : null;
        $this->showForm = true;
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->editingPoint = null;
    }

    public function refreshSamplePoints()
    {
        $this->closeForm();
        $this->resetPage();
    }

    public function exportToExcel()
    {
        $this->checkPermission('crm.permission');
        return (new \App\Exports\CRM\CustomerRegistryTabExport($this->customer->id, 'sample_points', $this->search))
            ->download('customer_sample_points_' . $this->customer->id . '_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.customer-sample-points-tab', [
            'samplePoints' => $this->samplePoints,
        ]);
    }
}
