<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\SampleHeader;
use App\Support\VarcharUuidSql;
use App\Livewire\Crm\BaseCrmComponent;
use App\BatchAmmendment;
use App\ChainOfCustody;
use App\SampleDetails;
use Illuminate\Support\Facades\Auth;
use App\BatchLabSectionApprover;
use App\Services\Sampleworkflow\BatchWorkflowStageSyncService;
use App\Services\Sampleworkflow\JobSampleNumberingService;

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
        $query = SampleHeader::join('sample_types as st', function ($join): void {
                $join->whereRaw(VarcharUuidSql::equals('st.id', 'sample_headers.sample_type_id'));
            })
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
                $reason_ids = collect(explode(',', (string) ($s->reason_for_submission ?? '')))
                    ->map(fn ($id) => trim($id))
                    ->filter()
                    ->values()
                    ->all();

                $reasons = $reason_ids === []
                    ? []
                    : \App\RequestType::whereIn('id', $reason_ids)->get()->pluck('name')->toArray();

                // Assign the reasons to a temporary property to access in blade if needed
                $s->fetched_reasons = $reasons;

                // Eager load without overwriting
                $s->load('samples');
                return $s;
            });
    }

    public function loadAmendment($batchId)
    {
        $this->selectedBatchId = (string) $batchId;
        
        // Validate that the batch actually exists for this customer
        $batch = SampleHeader::query()
            ->where('id', $this->selectedBatchId)
            ->where('crm_customer_id', $this->customer->id)
            ->first();
        if (!$batch) {
             $this->dispatch('alert', ['type' => 'error', 'message' => 'Batch not found.']);
             return;
        }
        
        // Fetch samples for this batch to populate the dropdown
        $samples = SampleDetails::query()
            ->where('sample_header_id', $batch->id)
            ->select('id', 'sample_code')
            ->orderBy('sample_code')
            ->get();

        $this->availableAmendmentSamples = $samples->map(function ($sample) {
            return [
                'id' => (string) $sample->id,
                'sample_code' => (string) $sample->sample_code,
            ];
        })->values()->toArray();

        $this->amendmentSamples = [];
        $this->amendmentSearch = '';
        $this->showAmendmentDropdown = false;

        $this->dispatch('open-amendment-modal', batchId: $this->selectedBatchId);
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
        $selectedIds = collect($this->amendmentSamples)
            ->map(fn ($id) => (string) (is_array($id) ? ($id['id'] ?? '') : $id))
            ->filter()
            ->all();

        return collect($this->availableAmendmentSamples)
            ->filter(fn ($sample) => in_array((string) $sample['id'], $selectedIds, true))
            ->values()
            ->all();
    }

    public function toggleAmendmentSample($sampleId)
    {
        $sampleId = (string) $sampleId;
        $selected = collect($this->amendmentSamples)
            ->map(fn ($id) => (string) (is_array($id) ? ($id['id'] ?? '') : $id))
            ->filter()
            ->values()
            ->all();

        if (in_array($sampleId, $selected, true)) {
            $selected = array_values(array_filter($selected, fn ($id) => $id !== $sampleId));
        } else {
            $selected[] = $sampleId;
        }

        $this->amendmentSamples = $selected;
    }

    public function clearAmendmentSample($sampleId)
    {
        $sampleId = (string) $sampleId;

        $this->amendmentSamples = array_values(array_filter(
            collect($this->amendmentSamples)
                ->map(fn ($id) => (string) (is_array($id) ? ($id['id'] ?? '') : $id))
                ->filter()
                ->all(),
            fn ($id) => $id !== $sampleId
        ));
    }

    public function isAmendmentSampleSelected($sampleId)
    {
        $sampleId = (string) $sampleId;
        $selected = collect($this->amendmentSamples)
            ->map(fn ($id) => (string) (is_array($id) ? ($id['id'] ?? '') : $id))
            ->filter()
            ->all();

        return in_array($sampleId, $selected, true);
    }

    public function loadReturn($batchId)
    {
        $this->selectedBatchId = (string) $batchId;
        $this->dispatch('open-return-modal', batchId: $this->selectedBatchId);
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

        $sampleIds = collect($this->amendmentSamples)->map(function ($s) {
            return (string) (is_array($s) ? ($s['id'] ?? '') : $s);
        })->filter()->unique()->values()->all();

        $samples = SampleDetails::whereIn('id', $sampleIds)
            ->where('sample_header_id', $batch->id)
            ->get();

        $t = [];
        foreach ($samples as $sample) {
            $t[$sample->sample_code] = $sample->id;
        }
        $y = json_encode($t);

        $new_ammendment = new BatchAmmendment();
        $new_ammendment->samples = $y;
        $new_ammendment->created_by_id = Auth::id();
        $new_ammendment->reason = $this->amendmentReason;
        $new_ammendment->batch_id = $batch->id;
        $new_ammendment->report_url = BatchAmmendment::snapshotReportUrl($batch);
        $new_ammendment->version_number = ((int) ($batch->is_amendment ?? 0)) + 1;
        $new_ammendment->save();

        $batch->is_amendment = $new_ammendment->version_number;
        $batch->in_ammendment_proccess = 1;

        // Nullify verification and approval data so the batch can re-enter lab workflow.
        $batch->verify_user_id = null;
        $batch->approve_user_id = null;
        $batch->approval_date = null;
        $batch->report_verified_date = null;

        app(BatchWorkflowStageSyncService::class)->applyWorkflowStatus(
            $batch,
            'Samples In Lab',
            'CRM amendment raised: ' . $this->amendmentReason
        );
        $batch->save();

        app(JobSampleNumberingService::class)
            ->syncReportNumbersForBatch($batch, (int) $batch->is_amendment);

        // Reset Verification records (status 0, clear date) to preserve assignments for the next cycle.
        BatchLabSectionApprover::where('batch_id', $batch->id)
            ->where('batch_status', 'Sample Verification')
            ->update([
                'status' => 0,
                'approval_date' => null
            ]);

        // Delete Approval records entirely (to be re-picked after re-verification).
        BatchLabSectionApprover::where('batch_id', $batch->id)
            ->where('batch_status', 'Sample Approval')
            ->delete();

        $this->reset(['selectedBatchId', 'amendmentSamples', 'amendmentReason', 'availableAmendmentSamples', 'amendmentSearch', 'showAmendmentDropdown']);
        $this->dispatch('close-modal', id: 'ammendment-detail');
        $this->dispatch('alert', type: 'success', message: 'Amendment raised. Batch ' . $batch->batch_code . ' is now in Samples In Lab.');
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
