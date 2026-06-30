<?php

namespace App\Livewire\Batch\Tabs;

use App\BatchLabSectionApprover;
use App\SampleHeader;
use App\Services\WorkflowService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Approvals extends Component
{
    use WithPagination;

    public SampleHeader $batch;
    public string $search = '';
    public int $perPage = 10;

    // Modal state
    public bool $showStatusModal = false;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public bool $showChecklistRequiredModal = false;

    public string $checklistRequiredMessage = '';

    public string $checklistUrl = '';

    public string $checklistStageName = '';

    // Currently selected approver
    public ?string $currentApproverId = null;

    // Forms
    public array $statusForm = [
        'status' => '1', // 1 = Approved, 2 = Declined (to match legacy)
        'remark' => '',
    ];

    public array $editForm = [
        'user_id' => '',
        'title' => '',
    ];

    /** @var \Illuminate\Support\Collection<int,\App\User> */
    public $users;

    protected $paginationTheme = 'bootstrap';

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;

        // Load potential approvers list (similar to Header component)
        $this->users = \App\User::where('is_client', 0)
            ->whereNull('supplier_id')
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getApproversProperty()
    {
        return $this->batch->approvers()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('approvername', 'like', '%' . $this->search . '%')
                      ->orWhere('title', 'like', '%' . $this->search . '%')
                      ->orWhere('batch_status', 'like', '%' . $this->search . '%')
                      ->orWhere('remark', 'like', '%' . $this->search . '%')
                      ->orWhere('workflow', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function openStatusModal($approverId): void
    {
        $this->currentApproverId = $this->normalizeApproverId($approverId);
        $approver = $this->findApproverById($this->currentApproverId);

        if (!$approver) {
            session()->flash('error', 'Approver not found.');
            return;
        }

        if (!$this->canProceedWithStageChecklist($approver)) {
            return;
        }

        // When opening the modal, default to "Approve" (1) so that
        // clicking save without changing the dropdown actually updates
        // the status instead of leaving it at 0 (pending).
        $this->statusForm = [
            'status' => '1',
            'remark' => $approver->remark ?? '',
        ];

        $this->showStatusModal = true;
    }

    public function openDeleteModal($approverId): void
    {
        $this->currentApproverId = $this->normalizeApproverId($approverId);
        $this->showDeleteModal = true;
    }

    public function openEditModal($approverId): void
    {
        $this->currentApproverId = $this->normalizeApproverId($approverId);
        $approver = $this->findApproverById($this->currentApproverId);

        if (!$approver) {
            session()->flash('error', 'Approver not found.');
            return;
        }

        $this->editForm = [
            'user_id' => (string)$approver->user_id,
            'title' => $approver->title ?? '',
        ];

        $this->showEditModal = true;
    }

    public function updateApproverStatus(): void
    {
        if (!$this->currentApproverId) {
            return;
        }

        $this->validate([
            'statusForm.status' => 'required|in:1,2,3',
            'statusForm.remark' => 'nullable|string',
        ]);

        $approver = $this->findApproverById($this->currentApproverId);

        if (!$approver) {
            session()->flash('error', 'Approver not found.');
            return;
        }

        $status = (int)$this->statusForm['status'];
        $persistedStatus = ($status === 1);

        if ($status === 1 && $approver->approver_order == 2) {
            $pendingTechReviews = \App\BatchLabSectionApprover::where('batch_id', $this->batch->id)
                ->where('approver_order', 1)
                ->where('status', '!=', 1)
                ->exists();

            if ($pendingTechReviews) {
                session()->flash('error', 'Cannot approve. The Technical Reviewer must approve first.');
                return;
            }
        }

        if ($status === 3) {
            if (!$approver->can_send_back_to_lab) {
                session()->flash('error', 'You do not have permission to send this batch back to the lab.');
                return;
            }

            if (empty($this->statusForm['remark'])) {
                session()->flash('error', 'A remark is required when sending a batch back to the lab.');
                return;
            }

            // Create amendment
            $samples = \App\SampleDetails::where('sample_header_id', $this->batch->id)->get();
            $t = [];
            foreach ($samples as $h) {
                $t[$h->sample_code] = $h->id;
            }
            $y = json_encode($t);

            $new_ammendment = new \App\BatchAmmendment();
            $new_ammendment->samples = $y;
            $new_ammendment->created_by_id = auth()->user()->id;
            $new_ammendment->reason = $this->statusForm['remark'];
            $new_ammendment->batch_id = $this->batch->id;
            $new_ammendment->report_url = $this->batch->batch_report_url;
            $new_ammendment->version_number = $this->batch->is_amendment + 1;
            $new_ammendment->save();

            // Log Chain of Custody
            $custody = new \App\ChainOfCustody();
            $custody->sample_header_id = $this->batch->id;
            $custody->workflow_stage = 'Samples In Lab';
            $custody->tracking_stage_id = $this->batch->sample_tracking_stage;
            $custody->moved_in_by = auth()->id();
            $custody->comments = 'Sent back to lab for amendment by ' . auth()->user()->name . '. Reason: ' . $this->statusForm['remark'];
            $custody->save();

            // Revert batch
            $this->batch->is_amendment = $new_ammendment->version_number;
            $this->batch->status = 'Samples In Lab';
            $this->batch->in_ammendment_proccess = 1;
            $this->batch->save();

            $approver->status = $persistedStatus;
            $approver->remark = $this->statusForm['remark'];
            $approver->approval_date = now();
            $approver->save();

            $this->resetStatusModal();
            $this->batch->refresh();
            $this->resetPage();

            session()->flash('success', 'Batch has been sent back to the lab for amendment.');
            return;
        }

        $approver->status = $persistedStatus;
        $approver->remark = ($this->statusForm['remark'] ?? '') !== '' ? $this->statusForm['remark'] : null;
        $approver->approval_date = now();
        $approver->save();

        // If all approvers for this batch/status are no longer pending, optionally
        // we could mirror the legacy behaviour that sets batch approval_date
        // when overall Sample Approval is complete. For now we keep it simple.

        $this->resetStatusModal();

        // Refresh batch relationship and pagination
        $this->batch->refresh();
        $this->resetPage();

        session()->flash('success', 'Approval status updated successfully.');
    }

    public function deleteApprover(): void
    {
        if (!$this->currentApproverId) {
            return;
        }

        $approver = $this->findApproverById($this->currentApproverId);

        if ($approver) {
            $approver->delete();
        }

        $this->resetDeleteModal();

        $this->batch->refresh();
        $this->resetPage();

        session()->flash('success', 'Approver deleted successfully.');
    }

    public function saveApproverEdits(): void
    {
        if (!$this->currentApproverId) {
            return;
        }

        $this->validate([
            'editForm.user_id' => 'required|exists:users,id',
            'editForm.title' => 'required|string|max:255',
        ]);

        $approver = $this->findApproverById($this->currentApproverId);

        if (!$approver) {
            session()->flash('error', 'Approver not found.');
            return;
        }

        $approver->user_id = (string)$this->editForm['user_id'];
        $approver->title = $this->editForm['title'];
        $approver->save();

        $this->resetEditModal();

        $this->batch->refresh();
        $this->resetPage();

        session()->flash('success', 'Approver updated successfully.');
    }

    protected function resetStatusModal(): void
    {
        $this->showStatusModal = false;
        $this->currentApproverId = null;
        $this->statusForm = [
            'status' => '1',
            'remark' => '',
        ];
    }

    public function closeChecklistRequiredModal(): void
    {
        $this->showChecklistRequiredModal = false;
        $this->checklistStageName = '';
    }

    protected function resetDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->currentApproverId = null;
    }

    protected function resetEditModal(): void
    {
        $this->showEditModal = false;
        $this->currentApproverId = null;
        $this->editForm = [
            'user_id' => '',
            'title' => '',
        ];
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.approvals', [
            'approvers' => $this->approvers
        ]);
    }

    private function findApproverById(?string $approverId): ?BatchLabSectionApprover
    {
        $normalizedId = $this->normalizeApproverId($approverId);
        if ($normalizedId === '') {
            return null;
        }

        $batchId = (string) ($this->batch->id ?? '');

        // Prefer a strict lookup scoped to the current batch to avoid cross-batch collisions.
        $query = BatchLabSectionApprover::query()
            ->when($batchId !== '', function ($q) use ($batchId) {
                $q->whereRaw('batch_id::text = ?', [$batchId]);
            });

        $approver = (clone $query)
            ->whereRaw('id::text = ?', [$normalizedId])
            ->first();

        if ($approver) {
            return $approver;
        }

        // Fallback for edge-cases where the incoming value has formatting artifacts.
        $compactId = preg_replace('/[^a-zA-Z0-9-]/', '', $normalizedId) ?? $normalizedId;
        if ($compactId !== '' && $compactId !== $normalizedId) {
            $approver = (clone $query)
                ->whereRaw('id::text = ?', [$compactId])
                ->first();
            if ($approver) {
                return $approver;
            }
        }

        // Last fallback: resolve from the current batch relation already used by this tab.
        return $this->batch->approvers()
            ->get()
            ->first(function ($item) use ($normalizedId, $compactId) {
                $id = (string) ($item->id ?? '');
                return $id === $normalizedId || ($compactId !== '' && $id === $compactId);
            });
    }

    private function normalizeApproverId($approverId): string
    {
        return trim((string) $approverId, " \t\n\r\0\x0B'\"");
    }

    private function canProceedWithStageChecklist(BatchLabSectionApprover $approver): bool
    {
        $stageName = (string) ($approver->batch_status ?? '');
        $requiresChecklist = in_array($stageName, ['Sample Verification', 'Sample Approval'], true)
            && (string) ($this->batch->status ?? '') === $stageName;

        if (!$requiresChecklist) {
            return true;
        }

        try {
            app(WorkflowService::class)
                ->assertStageApprovalsCompleted((string) $this->batch->id, $stageName);

            return true;
        } catch (ValidationException $exception) {
            $this->checklistRequiredMessage = $exception->validator->errors()->first()
                ?: 'Complete and approve the checklist before approving this action.';
            $this->checklistStageName = $stageName;
            $this->checklistUrl = route('sample-approval-checklist.show', [
                'sample' => $this->batch->id,
                'stage_name' => $stageName,
            ]);
            $this->showChecklistRequiredModal = true;

            return false;
        }
    }
}
