<?php

namespace App\Livewire\Sampleworkflow;

use App\Services\SubmissionForm\SubmissionFormIntrayService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class MoveToIntray extends Component
{
    /** @var array<int, string> */
    public array $selectedFormInstanceIds = [];

    /** @var array<int, array{id: string, label: string, customer: string}> */
    public array $selectedFormSummaries = [];

    /** @var array<int, array{id: string, name: string}> */
    public array $assignableUsers = [];

    /**
     * Instances with an existing pending intray assignment (reassignment context).
     *
     * @var array<int, array{id: string, label: string, assignee_name: string}>
     */
    public array $pendingIntrayAssignments = [];

    public string $assigneeUserId = '';

    public string $comment = '';

    public function mount(
        array $selectedFormInstanceIds = [],
        array $selectedFormSummaries = [],
        array $assignableUsers = [],
        array $pendingIntrayAssignments = []
    ): void {
        $this->selectedFormInstanceIds = array_values(array_filter($selectedFormInstanceIds));
        $this->selectedFormSummaries = $selectedFormSummaries;
        $this->assignableUsers = $assignableUsers;
        $this->pendingIntrayAssignments = $pendingIntrayAssignments;
    }

    /**
     * @param  array<int, string>  $instanceIds
     * @param  array<int, array{id: string, label: string, customer: string}>  $summaries
     * @param  array<int, array{id: string, name: string}>  $assignableUsers
     * @param  array<int, array{id: string, label: string, assignee_name: string}>  $pendingIntrayAssignments
     */
    public function handleIntrayModalOpen(
        array $instanceIds,
        array $summaries,
        array $assignableUsers = [],
        array $pendingIntrayAssignments = []
    ): void {
        $this->selectedFormInstanceIds = array_values(array_filter($instanceIds));
        $this->selectedFormSummaries = $summaries;
        $this->assignableUsers = $assignableUsers;
        $this->pendingIntrayAssignments = $pendingIntrayAssignments;
        $this->assigneeUserId = '';
        $this->comment = '';
        $this->resetValidation();

        $this->dispatch('show-move-to-intray-modal');
    }

    protected function getListeners(): array
    {
        return [
            'intray-modal-open' => 'handleIntrayModalOpen',
        ];
    }

    public function confirmMove(): void
    {
        if ($this->selectedFormInstanceIds === []) {
            $this->addError('selection', 'Select at least one request to move to an intray.');

            return;
        }

        $assignableIds = collect($this->assignableUsers)->pluck('id')->map(fn ($id) => (string) $id)->all();

        $this->validate([
            'assigneeUserId' => ['required', 'string', \Illuminate\Validation\Rule::in($assignableIds)],
            'comment' => ['nullable', 'string', 'max:2000'],
        ], [
            'assigneeUserId.required' => 'Select a user to assign this request to.',
            'assigneeUserId.in' => 'Select a valid lab user.',
        ]);

        $user = Auth::user();
        if (! $user instanceof User) {
            $this->addError('selection', 'You must be signed in to move requests to an intray.');

            return;
        }

        $processed = 0;
        $skipped = 0;

        foreach ($this->selectedFormInstanceIds as $instanceId) {
            try {
                $this->intrayService()->assignToUser(
                    (string) $instanceId,
                    $this->assigneeUserId,
                    $this->comment !== '' ? $this->comment : null,
                    $user,
                );
                $processed++;
            } catch (ValidationException $exception) {
                $skipped++;
            }
        }

        if ($processed === 0) {
            $this->addError('selection', 'No requests were moved. They may be ineligible or the assignee is invalid.');

            return;
        }

        $message = $processed === 1
            ? '1 request moved to intray.'
            : "{$processed} requests moved to intray.";

        if ($skipped > 0) {
            $message .= " ({$skipped} skipped.)";
        }

        session()->flash('success', $message);
        $this->dispatch('intray-move-completed');
    }

    public function render()
    {
        return view('livewire.sampleworkflow.move-to-intray');
    }

    private function intrayService(): SubmissionFormIntrayService
    {
        return app(SubmissionFormIntrayService::class);
    }
}
