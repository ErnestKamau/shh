<?php

namespace App\Livewire\Batch\Tabs;

use App\BatchLabSectionApprover;
use App\SampleHeader;
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

    // Currently selected approver
    public ?int $currentApproverId = null;

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
            ->where('supplier_id', 0)
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

    public function openStatusModal(int $approverId): void
    {
        $this->currentApproverId = $approverId;
        $approver = BatchLabSectionApprover::find($approverId);

        if (!$approver) {
            session()->flash('error', 'Approver not found.');
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

    public function openDeleteModal(int $approverId): void
    {
        $this->currentApproverId = $approverId;
        $this->showDeleteModal = true;
    }

    public function openEditModal(int $approverId): void
    {
        $this->currentApproverId = $approverId;
        $approver = BatchLabSectionApprover::find($approverId);

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
            'statusForm.status' => 'required|in:1,2',
            'statusForm.remark' => 'nullable|string',
        ]);

        $approver = BatchLabSectionApprover::find($this->currentApproverId);

        if (!$approver) {
            session()->flash('error', 'Approver not found.');
            return;
        }

        $status = (int)$this->statusForm['status'];

        $approver->status = $status;
        $approver->remark = $this->statusForm['remark'] ?? '';
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

        $approver = BatchLabSectionApprover::find($this->currentApproverId);

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

        $approver = BatchLabSectionApprover::find($this->currentApproverId);

        if (!$approver) {
            session()->flash('error', 'Approver not found.');
            return;
        }

        $approver->user_id = (int)$this->editForm['user_id'];
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
}
