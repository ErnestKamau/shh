<?php

namespace App\Services;

use App\Models\Workflow\Approval;
use App\Models\Workflow\ApprovalLog;
use App\Models\Workflow\ChecklistItem;
use App\Models\Workflow\ChecklistResponse;
use App\SampleHeader;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkflowService
{
    public function getApprovalsByStage(string $stageName): Collection
    {
        $this->assertValidStage($stageName);

        return Approval::query()
            ->with('checklistItems')
            ->where('stage_name', $stageName)
            ->orderBy('order')
            ->get();
    }

    public function getActiveApprovals(string $stageName): Collection
    {
        $this->assertValidStage($stageName);

        return Approval::query()
            ->with('checklistItems')
            ->where('stage_name', $stageName)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function getApprovalWithChecklist(string $approvalId): ?Approval
    {
        return Approval::query()
            ->with(['checklistItems' => function ($query) {
                $query->orderBy('order');
            }])
            ->find($approvalId);
    }

    public function saveApproval(array $attributes, ?string $approvalId = null): Approval
    {
        $stageName = (string) Arr::get($attributes, 'stage_name', '');
        $this->assertValidStage($stageName);

        $payload = [
            'stage_name' => $stageName,
            'code' => trim((string) Arr::get($attributes, 'code', '')),
            'name' => trim((string) Arr::get($attributes, 'name', '')),
            'order' => (int) Arr::get($attributes, 'order', 1),
            'is_active' => (bool) Arr::get($attributes, 'is_active', true),
        ];

        $duplicateQuery = Approval::query()
            ->where('stage_name', $payload['stage_name'])
            ->where('code', $payload['code']);

        if ($approvalId !== null) {
            $duplicateQuery->where('id', '!=', $approvalId);
        }

        if ($duplicateQuery->exists()) {
            throw ValidationException::withMessages([
                'approvalForm.code' => 'This approval code already exists for the selected stage.',
            ]);
        }

        if ($approvalId !== null) {
            $approval = Approval::query()->findOrFail($approvalId);
            $approval->update($payload);

            return $approval->fresh(['checklistItems']);
        }

        return Approval::query()->create($payload)->fresh(['checklistItems']);
    }

    public function deleteApproval(string $approvalId): void
    {
        Approval::query()->findOrFail($approvalId)->delete();
    }

    public function saveChecklistItem(string $approvalId, array $attributes, ?string $itemId = null): ChecklistItem
    {
        $approval = Approval::query()->findOrFail($approvalId);

        $payload = [
            'approval_id' => $approval->id,
            'label' => trim((string) Arr::get($attributes, 'label', '')),
            'type' => (string) Arr::get($attributes, 'type', 'text'),
            'is_required' => (bool) Arr::get($attributes, 'is_required', false),
            'options' => $this->normalizeOptions(Arr::get($attributes, 'options')),
            'order' => (int) Arr::get($attributes, 'order', 1),
        ];

        if ($payload['type'] !== 'select') {
            $payload['options'] = null;
        }

        if ($itemId !== null) {
            $item = ChecklistItem::query()->where('approval_id', $approval->id)->findOrFail($itemId);
            $item->update($payload);

            return $item->fresh();
        }

        return ChecklistItem::query()->create($payload);
    }

    public function deleteChecklistItem(string $itemId): void
    {
        ChecklistItem::query()->findOrFail($itemId)->delete();
    }

    public function getSampleStageApprovals(string $sampleId, string $stageName): Collection
    {
        $this->assertSampleAndStage($sampleId, $stageName);

        return Approval::query()
            ->with([
                'checklistItems' => function ($query) use ($sampleId) {
                    $query->orderBy('order')
                        ->with(['responses' => function ($responseQuery) use ($sampleId) {
                            $responseQuery->where('sample_id', $sampleId);
                        }]);
                },
                'approvalLogs' => function ($query) use ($sampleId) {
                    $query->where('sample_id', $sampleId)
                        ->with('approvedByUser')
                        ->orderByDesc('approved_at');
                },
            ])
            ->where('stage_name', $stageName)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function getApprovalAuditTrail(string $sampleId, string $stageName): Collection
    {
        $this->assertSampleAndStage($sampleId, $stageName);

        return ApprovalLog::query()
            ->with(['approval', 'approvedByUser'])
            ->where('sample_id', $sampleId)
            ->where('stage_name', $stageName)
            ->orderByDesc('approved_at')
            ->get();
    }

    public function submitApproval(
        string $sampleId,
        string $stageName,
        string $approvalId,
        array $responses,
        ?string $remarks,
        string $status,
        ?string $userId
    ): ApprovalLog {
        $sample = $this->assertSampleAndStage($sampleId, $stageName);

        $approval = Approval::query()
            ->with(['checklistItems' => function ($query) {
                $query->orderBy('order');
            }])
            ->where('stage_name', $stageName)
            ->where('is_active', true)
            ->findOrFail($approvalId);

        if (!in_array($status, ['approved', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'status' => 'The selected approval action is invalid.',
            ]);
        }

        if (ApprovalLog::query()->where('sample_id', $sample->id)->where('approval_id', $approval->id)->exists()) {
            throw ValidationException::withMessages([
                'approval' => 'This approval has already been completed for the selected sample.',
            ]);
        }

        return DB::transaction(function () use ($sample, $approval, $stageName, $responses, $remarks, $status, $userId) {
            foreach ($approval->checklistItems as $item) {
                ChecklistResponse::query()->updateOrCreate(
                    [
                        'sample_id' => $sample->id,
                        'approval_id' => $approval->id,
                        'checklist_item_id' => $item->id,
                    ],
                    [
                        'value' => Arr::get($responses, $item->id),
                        'user_id' => $userId,
                    ]
                );
            }

            return ApprovalLog::query()->create([
                'sample_id' => $sample->id,
                'approval_id' => $approval->id,
                'stage_name' => $stageName,
                'status' => $status,
                'remarks' => $remarks,
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);
        });
    }

    private function assertSampleAndStage(string $sampleId, string $stageName): SampleHeader
    {
        $this->assertValidStage($stageName);

        return SampleHeader::query()->findOrFail($sampleId);
    }

    private function assertValidStage(string $stageName): void
    {
        $stages = array_values(array_filter(getSampleWorflowStages(), function ($stage) {
            return $stage !== 'All Samples';
        }));

        if (!in_array($stageName, $stages, true)) {
            throw ValidationException::withMessages([
                'stage_name' => 'The selected workflow stage is invalid.',
            ]);
        }
    }

    private function normalizeOptions(mixed $options): ?array
    {
        if ($options === null) {
            return null;
        }

        if (is_string($options)) {
            $items = preg_split('/\r\n|\r|\n/', $options) ?: [];

            return array_values(array_filter(array_map('trim', $items), function ($item) {
                return $item !== '';
            }));
        }

        if (is_array($options)) {
            return array_values(array_filter(array_map(function ($item) {
                return is_string($item) ? trim($item) : $item;
            }, $options), function ($item) {
                return $item !== null && $item !== '';
            }));
        }

        return null;
    }
}