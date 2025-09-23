<?php

namespace App\Livewire;

use Livewire\Component;
use App\BatchAmmendment;
use App\SampleHeader;

class AmendmentViewer extends Component
{
    // Customer Data
    public $customerId;
    public $customer;
    
    // Amendments Data
    public $amendments = [];

    // Search and Filter
    public $search = '';
    public $statusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->customer = \App\Models\CRM\CRMCustomer::findOrFail($customerId);
        $this->loadAmendments();
    }

    public function loadAmendments()
    {
        $query = BatchAmmendment::whereHas('sampleHeader', function($query) {
                $query->where('crm_customer_id', $this->customerId);
            })
            ->with(['sampleHeader']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('reason', 'like', '%' . $this->search . '%')
                  ->orWhereHas('sampleHeader', function($subQuery) {
                      $subQuery->where('batch_code', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $this->amendments = $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->loadAmendments();
    }

    public function updatedDateFrom()
    {
        $this->loadAmendments();
    }

    public function updatedDateTo()
    {
        $this->loadAmendments();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->loadAmendments();
    }

    public function getAmendmentStatus($amendment)
    {
        $sampleHeader = $amendment->sampleHeader;
        
        if ($sampleHeader->in_ammendment_proccess == 1) {
            return 'In Progress';
        } elseif ($sampleHeader->status == 'Completed') {
            return 'Completed';
        } else {
            return 'Pending';
        }
    }

    public function getAmendmentStatusClass($amendment)
    {
        $status = $this->getAmendmentStatus($amendment);
        
        switch ($status) {
            case 'Completed':
                return 'success';
            case 'In Progress':
                return 'warning';
            case 'Pending':
                return 'info';
            default:
                return 'secondary';
        }
    }

    public function viewAmendmentDetails($amendmentId)
    {
        $amendment = BatchAmmendment::findOrFail($amendmentId);
        return redirect()->route('view-batch-details', ['batch' => $amendment->batch_id, 'client' => $this->customerId]);
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.amendment-viewer');
    }
}

