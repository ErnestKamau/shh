<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\SampleHeader;
use App\Livewire\Crm\BaseCrmComponent;
use App\BatchAmmendment;
use App\ChainOfCustody;
use App\SampleDetails;
use Illuminate\Support\Facades\Auth;
use App\BatchLabSectionApprover;

class CustomerReportsTab extends BaseCrmComponent
{
    use \Livewire\WithPagination;

    public $customer;
    public $search = '';
    public $perPage = 10;
    
    // Action properties
    public $selectedBatchId;
    public $amendmentSamples = [];
    public $amendmentReason;
    public $returnComment;
    public $availableAmendmentSamples = [];
    public $amendmentSearch = '';
    public $showAmendmentDropdown = false;

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

    public function exportToExcel()
    {
        $this->checkPermission('crm.permission');
        return (new \App\Exports\CRM\CustomerRegistryTabExport($this->customer->id, 'reports', $this->search))
            ->download('customer_reports_' . $this->customer->id . '_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function getReportsProperty()
    {
        // 1. Explicitly select the ID as 'batch_id' to avoid ANY ambiguity
        $query = SampleHeader::join('sample_types as st', 'st.id', '=', 'sample_headers.sample_type_id')
            ->select(
                'sample_headers.*', 
                'sample_headers.id as batch_id', // <--- CRITICAL FIX
                'st.name as sample_type'
            )
            ->where('sample_headers.crm_customer_id', $this->customer->id)
            ->where('sample_headers.status', "Completed");

        if ($this->search) {
            $query->where(function($q) {
                $q->where('sample_headers.batch_code', 'like', '%' . $this->search . '%')
                ->orWhere('sample_headers.reference_number', 'like', '%' . $this->search . '%')
                ->orWhere('sample_headers.description', 'like', '%' . $this->search . '%');
            });
        }
            
        return $query->orderBy('sample_headers.id', 'desc') // Be specific here too
            ->paginate($this->perPage)
            ->through(function ($s) {
                $reason_ids = explode(",", $s->reason_for_submission ?? '');
                $reasons = \App\RequestType::whereIn('id', $reason_ids)->get()->pluck('name')->toArray();
                
                // Assign the reasons to a temporary property to access in blade if needed
                $s->fetched_reasons = $reasons; 
                
                // Eager load without overwriting
                $s->load('samples'); 
                return $s;
            });
    }

    public function loadAmendment($batchId)
    {
        $this->selectedBatchId = $batchId;
        
        // Validate that the batch actually exists
        $batch = SampleHeader::find($batchId);
        if (!$batch) {
             $this->dispatch('alert', ['type' => 'error', 'message' => 'Batch not found.']);
             return;
        }
        
        // Fetch samples for this batch to populate the dropdown
        $samples = SampleDetails::join('sample_headers', 'sample_headers.id', '=', 'sample_details.sample_header_id')
            ->where('sample_details.sample_header_id', $batchId)
            ->where('sample_headers.crm_customer_id', $this->customer->id)
            ->select('sample_details.id', 'sample_details.sample_code')
            ->get();

        $this->availableAmendmentSamples = $samples->map(function ($sample) {
            return [
                'id' => (int) $sample->id,
                'sample_code' => (string) $sample->sample_code,
            ];
        })->values()->toArray();

        $this->amendmentSamples = [];
        $this->amendmentSearch = '';
        $this->showAmendmentDropdown = false;

        $this->dispatch('open-amendment-modal', batchId: $batchId);
    }

    public function getFilteredAmendmentOptionsProperty()
    {
        $search = trim(strtolower($this->amendmentSearch));

        return collect($this->availableAmendmentSamples)
            ->when($search !== '', function ($samples) use ($search) {
                return $samples->filter(function ($sample) use ($search) {
                    return str_contains(strtolower($sample['sample_code'] ?? ''), $search);
                });
            })
            ->take(80)
            ->values()
            ->all();
    }

    public function getSelectedAmendmentSampleBadgesProperty()
    {
        $selectedIds = collect($this->amendmentSamples)->map(fn($id) => (int) $id)->all();

        return collect($this->availableAmendmentSamples)
            ->filter(fn($sample) => in_array((int) $sample['id'], $selectedIds, true))
            ->values()
            ->all();
    }

    public function toggleAmendmentSample($sampleId)
    {
        $sampleId = (int) $sampleId;
        $selected = collect($this->amendmentSamples)->map(fn($id) => (int) $id)->all();

        if (in_array($sampleId, $selected, true)) {
            $selected = array_values(array_filter($selected, fn($id) => $id !== $sampleId));
        } else {
            $selected[] = $sampleId;
        }

        $this->amendmentSamples = $selected;
    }

    public function clearAmendmentSample($sampleId)
    {
        $sampleId = (int) $sampleId;

        $this->amendmentSamples = array_values(array_filter(
            collect($this->amendmentSamples)->map(fn($id) => (int) $id)->all(),
            fn($id) => $id !== $sampleId
        ));
    }

    public function isAmendmentSampleSelected($sampleId)
    {
        $sampleId = (int) $sampleId;
        $selected = collect($this->amendmentSamples)->map(fn($id) => (int) $id)->all();

        return in_array($sampleId, $selected, true);
    }

    public function loadReturn($batchId)
    {
        $this->selectedBatchId = $batchId;
        $this->dispatch('open-return-modal', batchId: $batchId);
    }

    public function saveAmendment()
    {
        $this->validate([
            'selectedBatchId' => 'required',
            'amendmentSamples' => 'required|array|min:1',
            'amendmentReason' => 'required',
        ]);

        $batch = SampleHeader::find($this->selectedBatchId);

        if (!$batch) {
            $this->dispatch('alert', ['type' => 'error',  'message' => 'Batch not found!']);
            return;
        }

        // Flatten and cast IDs to integers for security
        $sampleIds = collect($this->amendmentSamples)->map(function ($s) {
            return (int) (is_array($s) ? $s['id'] : $s);
        })->unique()->toArray();

        // Efficient single query (Refactored from N+1 loop)
        $samples = SampleDetails::whereIn('id', $sampleIds)->get();

        $t = [];
        foreach ($samples as $sample) {
            // Controller logic uses the ID as value
            $t[$sample->sample_code] = $sample->id;
        }
        $y = json_encode($t);

        $new_ammendment = new BatchAmmendment();
        $new_ammendment->samples = $y;
        $new_ammendment->created_by_id = Auth::id();
        $new_ammendment->reason = $this->amendmentReason;
        $new_ammendment->batch_id = $batch->id;
        $new_ammendment->report_url = $batch->batch_report_url;
        $new_ammendment->version_number = $batch->is_amendment + 1;
        $new_ammendment->save();

        $batch->is_amendment = $new_ammendment->version_number;
        $batch->status = 'Sample Verification';
        $batch->in_ammendment_proccess = 1;
        
        // Nullify verification and approval data
        $batch->verify_user_id = null;
        $batch->approve_user_id = null;
        $batch->approval_date = null;
        $batch->report_verified_date = null;
        
        $batch->save();

        // Refined Logic based on discussion:
        // 1. Reset Verification records (status 0, clear date) to preserve assignments
        BatchLabSectionApprover::where('batch_id', $batch->id)
            ->where('batch_status', 'Sample Verification')
            ->update([
                'status' => 0,
                'approval_date' => null
            ]);

        // 2. Delete Approval records entirely (to be re-picked after re-verification)
        BatchLabSectionApprover::where('batch_id', $batch->id)
            ->where('batch_status', 'Sample Approval')
            ->delete();

        $this->reset(['selectedBatchId', 'amendmentSamples', 'amendmentReason']);
        $this->dispatch('close-modal', id: 'ammendment-detail');
        $this->dispatch('alert', type: 'success', message: 'Amendment added successfully!');
    }

    public function saveReturnVerification()
    {
        $this->validate([
            'selectedBatchId' => 'required',
            'returnComment' => 'required',
        ]);

        $batch = SampleHeader::find($this->selectedBatchId);

        if (!$batch) {
            $this->dispatch('alert', ['type' => 'error',  'message' => 'Batch not found!']);
            return;
        }

        $current = $batch->status;
        $batch->status = "Sample Verification";
        $batch->save();

        $custodyDetails = array(
            "batch_id" => $batch->id,
            "comments" => $this->returnComment,
            "current" => array(
                "status" => $current,
                "tracking_stage" => $batch->sample_tracking_stage,
            ),
            "target" => array(
                "status" => $batch->status,
                "tracking_stage" => $batch->sample_tracking_stage,
            )
        );

        $this->updateChainofCustody($custodyDetails);

        $this->reset(['selectedBatchId', 'returnComment']);
        $this->dispatch('close-modal', id: 'return-verification');
        $this->dispatch('alert', type: 'success', message: 'Batch returned to verification successfully');
    }

    public function updateChainofCustody($data)
    {
        ChainOfCustody::where('sample_header_id', $data['batch_id'])
            ->whereNull('moved_out_date')->update([
                'moved_out_date' => \Carbon\Carbon::now(),
                'moved_out_by' => Auth::id(),
                'comments' => $data['comments']
            ]);

        $custody = new ChainOfCustody;
        $custody->workflow_stage = $data['target']['status'];
        $custody->tracking_stage_id = $data['target']['tracking_stage'];
        $custody->moved_in_by = Auth::id();
        $custody->sample_header_id = $data['batch_id'];

        $custody->save();

        return true;
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.customer-reports-tab', [
            'reports' => $this->reports,
        ]);
    }
}
