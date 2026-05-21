<?php

namespace App\Services\Sampleworkflow;

use App\Livewire\Sampleworkflow\ReceiveSampleRequest;
use App\Models\Workflow\ChecklistResponse;
use App\Services\WorkflowService;

class SampleReceivingIntegrityService
{
    public function __construct(
        private readonly WorkflowService $workflowService,
    ) {}

    /**
     * @return array{incomplete: bool, items: list<array{id: string, label: string}>}
     */
    public function assessFormInstance(?string $submissionFormInstanceId): array
    {
        if ($submissionFormInstanceId === null || $submissionFormInstanceId === '') {
            return ['incomplete' => false, 'items' => []];
        }

        $approval = $this->workflowService->getApprovalByCode(
            ReceiveSampleRequest::STAGE_NAME,
            ReceiveSampleRequest::APPROVAL_CODE
        );

        if ($approval === null) {
            return ['incomplete' => false, 'items' => []];
        }

        $approval->load(['checklistItems' => fn ($q) => $q->orderBy('order')]);

        $responses = ChecklistResponse::query()
            ->where('submission_form_instance_id', $submissionFormInstanceId)
            ->where('approval_id', $approval->id)
            ->get()
            ->keyBy('checklist_item_id');

        $incompleteItems = [];

        foreach ($approval->checklistItems as $item) {
            if (!$item->is_required) {
                continue;
            }

            $value = $responses->get($item->id)?->value;
            if (!$this->isResponseSatisfied($item->type, $value)) {
                $incompleteItems[] = [
                    'id' => (string) $item->id,
                    'label' => (string) $item->label,
                ];
            }
        }

        return [
            'incomplete' => $incompleteItems !== [],
            'items' => $incompleteItems,
        ];
    }

    private function isResponseSatisfied(string $type, mixed $value): bool
    {
        if ($type === 'checkbox') {
            if (is_bool($value)) {
                return $value;
            }

            if (is_numeric($value)) {
                return (int) $value === 1;
            }

            if (is_string($value)) {
                $normalized = strtolower(trim($value));

                return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
            }

            return false;
        }

        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return (bool) $value;
    }
}
