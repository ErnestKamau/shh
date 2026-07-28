<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\SampleHeader;
use App\Models\CRM\CRMCustomer;
use Illuminate\Support\Facades\DB;

class ReportsViewer extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination;

    // Customer Data
    public $customerId;
    public $customer;
    
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
        $this->loadCustomerData();
    }

    public function loadCustomerData()
    {
        $this->customer = CRMCustomer::findOrFail($this->customerId);
    }

    public function getReportsProperty()
    {
        $query = SampleHeader::join('sample_types as st', 'st.id', 'sample_type_id')
            ->selectRaw('sample_headers.*, st.name as sample_type')
            ->where('crm_customer_id', $this->customerId)
            ->when($this->search, function ($query) {
                $this->applyCaseInsensitiveSearch($query, ['sample_headers.batch_code', 'st.name'], (string) $this->search);
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('sample_headers.status', $this->statusFilter);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('sample_headers.created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('sample_headers.created_at', '<=', $this->dateTo);
            })
            ->orderBy('sample_headers.id', 'desc');

        return $query->paginate($this->perPage);
    }

    public function downloadReport($batchId)
    {
        $sampleHeader = SampleHeader::findOrFail($batchId);
        
        if ($sampleHeader->batch_report_url) {
            return response()->download(storage_path('app/public' . $sampleHeader->batch_report_url));
        }
        
        $this->message = 'Report not available for download.';
        $this->messageType = 'error';
    }

    public function viewReport($batchId)
    {
        $sampleHeader = SampleHeader::findOrFail($batchId);
        
        if ($sampleHeader->batch_report_url) {
            return redirect()->to(asset('storage' . $sampleHeader->batch_report_url));
        }
        
        $this->message = 'Report not available for viewing.';
        $this->messageType = 'error';
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.reports.reports-viewer', [
            'reports' => $this->reports
        ]);
    }
}