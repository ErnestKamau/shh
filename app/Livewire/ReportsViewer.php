<?php

namespace App\Livewire;

use Livewire\Component;
use App\SampleHeader;
use Illuminate\Support\Facades\Storage;

class ReportsViewer extends Component
{
    // Customer Data
    public $customerId;
    public $customer;
    
    // Reports Data
    public $reports = [];
    public $orders = [];

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
        $this->loadReports();
        $this->loadOrders();
    }

    public function loadReports()
    {
        $query = SampleHeader::join('sample_types as st', 'st.id', 'sample_type_id')
            ->selectRaw('sample_headers.*, st.name as sample_type')
            ->where('crm_customer_id', $this->customerId)
            ->where('sample_headers.status', 'Completed');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('sample_headers.batch_code', 'like', '%' . $this->search . '%')
                  ->orWhere('sample_headers.reference_number', 'like', '%' . $this->search . '%')
                  ->orWhere('st.name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('sample_headers.approval_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('sample_headers.approval_date', '<=', $this->dateTo);
        }

        $this->reports = $query->orderBy('sample_headers.approval_date', 'desc')
            ->paginate($this->perPage);
    }

    public function loadOrders()
    {
        $this->orders = SampleHeader::leftJoin('sample_details as sd', 'sample_headers.id', '=', 'sd.sample_header_id')
            ->join('sample_types as st', 'st.id', 'sample_headers.sample_type_id')
            ->selectRaw('sample_headers.id, sample_headers.batch_code, sample_headers.date_collected, sample_headers.reference_number, sample_headers.document_number, sample_headers.status, count(sd.id) as samples, st.name as sample_type')
            ->where('crm_customer_id', $this->customerId)
            ->groupBy('sample_headers.id','sample_headers.batch_code', 'sample_headers.date_collected', 'sample_headers.reference_number', 'sample_headers.document_number', 'sample_headers.status', 'st.name')
            ->whereNotIn('status', ['Completed'])
            ->orderBy('sample_headers.id', 'desc')
            ->get();
    }

    public function updatedSearch()
    {
        $this->loadReports();
    }

    public function updatedDateFrom()
    {
        $this->loadReports();
    }

    public function updatedDateTo()
    {
        $this->loadReports();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->loadReports();
    }

    public function downloadReport($batchId)
    {
        try {
            $sampleHeader = SampleHeader::findOrFail($batchId);
            
            if ($sampleHeader->batch_report_url && Storage::exists('public' . $sampleHeader->batch_report_url)) {
                return response()->download(storage_path('app/public' . $sampleHeader->batch_report_url));
            }
            
            $this->message = 'Report file not found or not available for download.';
            $this->messageType = 'error';

        } catch (\Exception $e) {
            $this->message = 'Error downloading report: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function viewBatchDetails($batchId)
    {
        return redirect()->route('view-batch-details', ['batch' => $batchId, 'client' => $this->customerId]);
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.reports-viewer');
    }
}

