<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\Workflow\Approval;
use App\Services\WorkflowService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ApprovalChecklist extends Component
{
    public string $sampleId;
    public string $stageName;
    public string $batchCode = '';
    public array $responses = [];
    public array $remarks = [];

    public function mount(string $sampleId, string $stageName): void
    {
        $this->sampleId = $sampleId;
        $this->stageName = $stageName;
        
        $batch = \App\SampleHeader::find($sampleId);
        $this->batchCode = $batch ? $batch->batch_code : $sampleId;

        $this->hydrateSavedState();
    }

    public function submitApproval(string $approvalId, string $status): void
    {
        $this->authorizePermission('laboratory.components.sample-approval-checklist.edit');

        $approval = $this->workflowService()->getApprovalWithChecklist($approvalId);

        if ($approval === null || $approval->stage_name !== $this->stageName || !$approval->is_active) {
            session()->flash('error', 'The selected approval is not available for this workflow stage.');

            return;
        }

        $this->validate($this->rulesForApproval($approval), $this->messagesForApproval($approval));

        try {
            $this->workflowService()->submitApproval(
                $this->sampleId,
                $this->stageName,
                $approvalId,
                $this->approvalResponses($approval),
                $this->remarks[$approvalId] ?? null,
                $status,
                auth()->id() ? (string) auth()->id() : null,
            );

            $this->hydrateSavedState();
            session()->flash('success', ucfirst($status) . ' recorded successfully.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            session()->flash('error', 'Unable to complete the approval action.');
        }
    }

    public function render()
    {
        return view('livewire.sampleworkflow.approval-checklist', [
            'approvals' => $this->workflowService()->getSampleStageApprovals($this->sampleId, $this->stageName),
            'auditTrail' => $this->workflowService()->getApprovalAuditTrail($this->sampleId, $this->stageName),
        ]);
    }

    private function hydrateSavedState(): void
    {
        $approvals = $this->workflowService()->getSampleStageApprovals($this->sampleId, $this->stageName);

        foreach ($approvals as $approval) {
            $latestLog = $approval->approvalLogs->first();

            if ($latestLog !== null) {
                $this->remarks[$approval->id] = (string) ($latestLog->remarks ?? '');
            }

            foreach ($approval->checklistItems as $item) {
                $response = $item->responses->first();

                if ($response !== null) {
                    $this->responses[$item->id] = $response->value;
                } elseif (!array_key_exists($item->id, $this->responses) && $item->type === 'checkbox') {
                    $this->responses[$item->id] = false;
                }
            }
        }
    }

    private function rulesForApproval(Approval $approval): array
    {
        $rules = [];

        foreach ($approval->checklistItems as $item) {
            $key = 'responses.' . $item->id;

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
            $key = 'responses.' . $item->id;
            $messages[$key . '.required'] = $item->label . ' is required.';
            $messages[$key . '.accepted'] = $item->label . ' must be checked.';
            $messages[$key . '.in'] = 'Select a valid option for ' . $item->label . '.';
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

    private function authorizePermission(string $permission): void
    {
        if (!auth()->check() || !auth()->user()->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}