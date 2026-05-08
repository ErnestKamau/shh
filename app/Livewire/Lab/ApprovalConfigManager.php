<?php

namespace App\Livewire\Lab;

use App\Services\WorkflowService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ApprovalConfigManager extends Component
{
    public string $stageNameFilter = '';
    public ?string $selectedApprovalId = null;
    public bool $showApprovalModal = false;
    public bool $showChecklistItemModal = false;
    public ?string $editingApprovalId = null;
    public ?string $editingChecklistItemId = null;
    public array $approvalForm = [
        'stage_name' => '',
        'code' => '',
        'name' => '',
        'order' => 1,
        'is_active' => true,
    ];
    public array $itemForm = [
        'label' => '',
        'type' => 'checkbox',
        'is_required' => false,
        'options' => '',
        'order' => 1,
    ];

    public function mount(): void
    {
        $this->stageNameFilter = $this->stageOptions()[0] ?? '';
        $this->approvalForm['stage_name'] = $this->stageNameFilter;
        $this->syncSelectedApproval();
    }

    public function updatedStageNameFilter(string $value): void
    {
        $this->stageNameFilter = $value;
        $this->selectedApprovalId = null;
        $this->approvalForm['stage_name'] = $value;
        $this->syncSelectedApproval();
    }

    public function selectApproval(string $approvalId): void
    {
        $this->selectedApprovalId = $approvalId;
    }

    public function openCreateApprovalModal(): void
    {
        $this->authorizePermission('laboratory.components.checklist-approvals.add');

        $this->editingApprovalId = null;
        $this->approvalForm = [
            'stage_name' => $this->stageNameFilter,
            'code' => '',
            'name' => '',
            'order' => $this->nextApprovalOrder(),
            'is_active' => true,
        ];
        $this->showApprovalModal = true;
    }

    public function openEditApprovalModal(string $approvalId): void
    {
        $this->authorizePermission('laboratory.components.checklist-approvals.edit');

        $approval = $this->workflowService()->getApprovalWithChecklist($approvalId);

        if ($approval === null) {
            return;
        }

        $this->editingApprovalId = $approval->id;
        $this->approvalForm = [
            'stage_name' => $approval->stage_name,
            'code' => $approval->code,
            'name' => $approval->name,
            'order' => $approval->order,
            'is_active' => (bool) $approval->is_active,
        ];
        $this->showApprovalModal = true;
    }

    public function closeApprovalModal(): void
    {
        $this->showApprovalModal = false;
        $this->editingApprovalId = null;
        $this->resetErrorBag();
    }

    public function saveApproval(): void
    {
        $this->authorizePermission(
            $this->editingApprovalId ? 'laboratory.components.checklist-approvals.edit' : 'laboratory.components.checklist-approvals.add'
        );

        $this->approvalForm['code'] = $this->approvalForm['code'] !== ''
            ? Str::slug($this->approvalForm['code'], '_')
            : Str::slug($this->approvalForm['name'], '_');

        $this->validate([
            'approvalForm.stage_name' => ['required', Rule::in($this->stageOptions())],
            'approvalForm.code' => ['required', 'string', 'max:255'],
            'approvalForm.name' => ['required', 'string', 'max:255'],
            'approvalForm.order' => ['required', 'integer', 'min:1'],
            'approvalForm.is_active' => ['boolean'],
        ]);

        try {
            $approval = $this->workflowService()->saveApproval($this->approvalForm, $this->editingApprovalId);
            $this->stageNameFilter = $approval->stage_name;
            $this->selectedApprovalId = $approval->id;
            $this->closeApprovalModal();
            session()->flash('success', 'Approval configuration saved successfully.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            session()->flash('error', 'Unable to save approval configuration.');
        }
    }

    public function deleteApproval(string $approvalId): void
    {
        $this->authorizePermission('laboratory.components.checklist-approvals.delete');

        try {
            $this->workflowService()->deleteApproval($approvalId);

            if ($this->selectedApprovalId === $approvalId) {
                $this->selectedApprovalId = null;
                $this->syncSelectedApproval();
            }

            session()->flash('success', 'Approval deleted successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            session()->flash('error', 'Unable to delete approval.');
        }
    }

    public function openCreateChecklistItemModal(): void
    {
        $this->authorizePermission('laboratory.components.checklist-approvals.add');

        if ($this->selectedApprovalId === null) {
            session()->flash('error', 'Select an approval before adding checklist items.');

            return;
        }

        $this->editingChecklistItemId = null;
        $this->itemForm = [
            'label' => '',
            'type' => 'checkbox',
            'is_required' => false,
            'options' => '',
            'order' => $this->nextChecklistItemOrder(),
        ];
        $this->showChecklistItemModal = true;
    }

    public function openEditChecklistItemModal(string $itemId): void
    {
        $this->authorizePermission('laboratory.components.checklist-approvals.edit');

        $approval = $this->selectedApproval();

        if ($approval === null) {
            return;
        }

        $item = $approval->checklistItems->firstWhere('id', $itemId);

        if ($item === null) {
            return;
        }

        $this->editingChecklistItemId = $item->id;
        $this->itemForm = [
            'label' => $item->label,
            'type' => $item->type,
            'is_required' => (bool) $item->is_required,
            'options' => implode(PHP_EOL, $item->options ?? []),
            'order' => $item->order,
        ];
        $this->showChecklistItemModal = true;
    }

    public function closeChecklistItemModal(): void
    {
        $this->showChecklistItemModal = false;
        $this->editingChecklistItemId = null;
        $this->resetErrorBag();
    }

    public function saveChecklistItem(): void
    {
        $this->authorizePermission(
            $this->editingChecklistItemId ? 'laboratory.components.checklist-approvals.edit' : 'laboratory.components.checklist-approvals.add'
        );

        if ($this->selectedApprovalId === null) {
            session()->flash('error', 'Select an approval before saving checklist items.');

            return;
        }

        $rules = [
            'itemForm.label' => ['required', 'string', 'max:255'],
            'itemForm.type' => ['required', Rule::in(['checkbox', 'text', 'select'])],
            'itemForm.is_required' => ['boolean'],
            'itemForm.order' => ['required', 'integer', 'min:1'],
        ];

        if ($this->itemForm['type'] === 'select') {
            $rules['itemForm.options'] = ['required', 'string'];
        }

        $this->validate($rules);

        try {
            $this->workflowService()->saveChecklistItem($this->selectedApprovalId, $this->itemForm, $this->editingChecklistItemId);
            $this->closeChecklistItemModal();
            session()->flash('success', 'Checklist item saved successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            session()->flash('error', 'Unable to save checklist item.');
        }
    }

    public function deleteChecklistItem(string $itemId): void
    {
        $this->authorizePermission('laboratory.components.checklist-approvals.delete');

        try {
            $this->workflowService()->deleteChecklistItem($itemId);
            session()->flash('success', 'Checklist item removed successfully.');
        } catch (\Throwable $exception) {
            report($exception);
            session()->flash('error', 'Unable to delete checklist item.');
        }
    }

    public function render()
    {
        $approvals = $this->stageNameFilter !== ''
            ? $this->workflowService()->getApprovalsByStage($this->stageNameFilter)
            : collect();

        if ($this->selectedApprovalId === null && $approvals->isNotEmpty()) {
            $this->selectedApprovalId = $approvals->first()->id;
        }

        return view('livewire.lab.approval-config-manager', [
            'stageOptions' => $this->stageOptions(),
            'approvals' => $approvals,
            'selectedApproval' => $this->selectedApproval(),
        ]);
    }

    private function selectedApproval()
    {
        if ($this->selectedApprovalId === null) {
            return null;
        }

        return $this->workflowService()->getApprovalWithChecklist($this->selectedApprovalId);
    }

    private function syncSelectedApproval(): void
    {
        $approvals = $this->stageNameFilter !== ''
            ? $this->workflowService()->getApprovalsByStage($this->stageNameFilter)
            : collect();

        if ($approvals->isEmpty()) {
            $this->selectedApprovalId = null;

            return;
        }

        $currentApprovalExists = $this->selectedApprovalId !== null
            && $approvals->contains(fn ($approval) => $approval->id === $this->selectedApprovalId);

        if (!$currentApprovalExists) {
            $this->selectedApprovalId = $approvals->first()->id;
        }
    }

    private function nextApprovalOrder(): int
    {
        $approvals = $this->stageNameFilter !== ''
            ? $this->workflowService()->getApprovalsByStage($this->stageNameFilter)
            : collect();

        return ((int) $approvals->max('order')) + 1;
    }

    private function nextChecklistItemOrder(): int
    {
        $approval = $this->selectedApproval();

        if ($approval === null) {
            return 1;
        }

        return ((int) $approval->checklistItems->max('order')) + 1;
    }

    private function stageOptions(): array
    {
        return array_values(array_filter(getSampleWorflowStages(), function ($stage) {
            return $stage !== 'All Samples';
        }));
    }

    private function workflowService(): WorkflowService
    {
        return app(WorkflowService::class);
    }

    private function authorizePermission(string $permission): void
    {
        if (!auth()->check() || !auth()->user()->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}