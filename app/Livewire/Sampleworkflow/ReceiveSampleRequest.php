<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\SubmissionFormInstance;
use App\Models\Workflow\Approval;
use App\Services\Sampleworkflow\SampleReceivingCheckInService;
use App\Services\WorkflowService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ReceiveSampleRequest extends Component
{
    public const STAGE_NAME = 'Samples Receiving';

    public const APPROVAL_CODE = 'sro_receiving_sample';

    /** @var array<int, string> */
    public array $selectedFormInstanceIds = [];

    /** @var array<int, array{id: string, label: string, customer: string}> */
    public array $selectedFormSummaries = [];

    /** @var list<array<string, mixed>> */
    public array $checkInContexts = [];

    public array $responses = [];

    public string $remarks = '';

    public ?string $loadError = null;

    public function mount(array $selectedFormInstanceIds = [], array $selectedFormSummaries = []): void
    {
        $this->selectedFormInstanceIds = array_values(array_filter($selectedFormInstanceIds));
        $this->selectedFormSummaries = $selectedFormSummaries;
        $this->syncLoadErrorFromApproval();
        $this->initializeResponses();
        $this->refreshCheckInContexts();
    }

    public function updatedSelectedFormInstanceIds(): void
    {
        $this->initializeResponses();
        $this->refreshCheckInContexts();
    }

    /**
     * Receives data from the parent WorkflowBoard and shows the Bootstrap modal
     * once this component's state is fully updated in the same response cycle.
     *
     * @param  array<int, string>  $instanceIds
     * @param  array<int, array{id: string, label: string, customer: string}>  $summaries
     */
    public function handleReceiveModalOpen(array $instanceIds, array $summaries): void
    {
        $this->selectedFormInstanceIds = array_values(array_filter($instanceIds));
        $this->selectedFormSummaries = $summaries;
        $this->remarks = '';
        $this->resetValidation();
        $this->syncLoadErrorFromApproval();
        $this->responses = [];
        $this->initializeResponses();
        $this->refreshCheckInContexts();

        $this->dispatch('show-receive-sample-modal');
    }

    protected function getListeners(): array
    {
        return [
            'receive-modal-open' => 'handleReceiveModalOpen',
        ];
    }

    public function openRejectWizard(string $instanceId): void
    {
        $this->dispatch('open-rejection-wizard', submissionFormInstanceId: $instanceId);
        $this->dispatch('hide-receive-sample-modal');
    }

    public function confirmReceive(): void
    {
        if ($this->selectedFormInstanceIds === []) {
            $this->addError('selection', 'Select at least one submitted request to receive.');

            return;
        }

        $approval = $this->resolveApproval();
        if ($approval === null) {
            return;
        }

        $rules = $this->rulesForApproval($approval);
        if (! empty($rules)) {
            $this->validate($rules, $this->messagesForApproval($approval));
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            $this->addError('selection', 'You must be signed in to receive samples.');

            return;
        }

        $checkInService = app(SampleReceivingCheckInService::class);
        $processed = 0;
        $skipped = 0;
        $blockedReasons = [];

        DB::transaction(function () use ($approval, $user, $checkInService, &$processed, &$skipped, &$blockedReasons) {
            $instances = SubmissionFormInstance::query()
                ->with(['batches', 'submissionForm', 'sampleSubmissionRequest'])
                ->whereIn('id', $this->selectedFormInstanceIds)
                ->get();

            foreach ($instances as $instance) {
                if (! $checkInService->canReceiveInstance($instance)) {
                    $skipped++;
                    $reason = $checkInService->receiveBlockReason($instance);
                    if ($reason !== null) {
                        $blockedReasons[] = $reason;
                    }

                    continue;
                }

                $this->workflowService()->submitFormInstanceApproval(
                    $instance->id,
                    self::STAGE_NAME,
                    $approval->id,
                    $this->approvalResponses($approval),
                    $this->remarks !== '' ? $this->remarks : null,
                    'approved',
                    (string) $user->id,
                );

                $instance->markAsReceived($user, $this->remarks !== '' ? $this->remarks : null);
                $processed++;
            }
        });

        if ($processed === 0) {
            $message = 'No eligible submitted requests were received.';
            if ($blockedReasons !== []) {
                $message .= ' '.collect($blockedReasons)->unique()->implode(' ');
            } else {
                $message .= ' They may already be received or have batches created.';
            }
            $this->addError('selection', $message);

            return;
        }

        $message = $processed === 1
            ? '1 request marked as received.'
            : "{$processed} requests marked as received.";

        if ($skipped > 0) {
            $message .= " ({$skipped} skipped.)";
        }

        session()->flash('success', $message);
        $this->dispatch('receive-completed');
    }

    public function render()
    {
        return view('livewire.sampleworkflow.receive-sample-request', [
            'approval' => $this->resolveApproval(),
        ]);
    }

    private function refreshCheckInContexts(): void
    {
        if ($this->selectedFormInstanceIds === []) {
            $this->checkInContexts = [];

            return;
        }

        $this->checkInContexts = app(SampleReceivingCheckInService::class)
            ->buildCheckInContexts($this->selectedFormInstanceIds);
    }

    private function resolveApproval(): ?Approval
    {
        $approval = $this->workflowService()->getApprovalByCode(self::STAGE_NAME, self::APPROVAL_CODE);
        $this->syncLoadErrorFromApproval($approval);

        return $approval;
    }

    private function syncLoadErrorFromApproval(?Approval $approval = null): void
    {
        $approval ??= $this->workflowService()->getApprovalByCode(self::STAGE_NAME, self::APPROVAL_CODE);

        $this->loadError = $approval === null
            ? 'Receiving checklist is not configured. Add approval code "sro_receiving_sample" for Samples Receiving.'
            : null;
    }

    private function initializeResponses(): void
    {
        $approval = $this->workflowService()->getApprovalByCode(self::STAGE_NAME, self::APPROVAL_CODE);
        if ($approval === null) {
            return;
        }

        foreach ($approval->checklistItems as $item) {
            if (! array_key_exists($item->id, $this->responses) && $item->type === 'checkbox') {
                $this->responses[$item->id] = false;
            }
        }
    }

    private function rulesForApproval(Approval $approval): array
    {
        $rules = [];

        foreach ($approval->checklistItems as $item) {
            $key = 'responses.'.$item->id;

            if ($item->type === 'checkbox') {
                $rules[$key] = $item->is_required ? ['accepted'] : ['nullable', 'boolean'];
                continue;
            }

            if ($item->type === 'select') {
                $selectRules = [$item->is_required ? 'required' : 'nullable'];
                $selectRules[] = Rule::in($item->options ?? []);
                $rules[$key] = $selectRules;
                continue;
            }

            $rules[$key] = $item->is_required
                ? ['required', 'string']
                : ['nullable', 'string'];
        }

        return $rules;
    }

    private function messagesForApproval(Approval $approval): array
    {
        $messages = [];

        foreach ($approval->checklistItems as $item) {
            $key = 'responses.'.$item->id;
            $messages[$key.'.required'] = $item->label.' is required.';
            $messages[$key.'.accepted'] = $item->label.' must be checked.';
            $messages[$key.'.in'] = 'Select a valid option for '.$item->label.'.';
        }

        return $messages;
    }

    private function approvalResponses(Approval $approval): array
    {
        $payload = [];

        foreach ($approval->checklistItems as $item) {
            $payload[$item->id] = $this->responses[$item->id] ?? null;
        }

        return $payload;
    }

    private function workflowService(): WorkflowService
    {
        return app(WorkflowService::class);
    }
}
