<?php

namespace App\Services\Sampleworkflow;

use App\Models\Sampleworkflow\SampleHeaderUserAssignment;
use App\SampleHeader;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SampleHeaderAssignmentService
{
    /**
     * @param  array<int, string>  $batchIds
     * @return array{processed:int, skipped:int}
     */
    public function assignToUser(array $batchIds, string $toUserId, ?string $comment, User $actor): array
    {
        $assignee = $this->resolveAssignableUser($toUserId);

        $processed = 0;
        $skipped = 0;

        foreach ($batchIds as $batchId) {
            $batchId = trim((string) $batchId);
            if ($batchId === '') {
                $skipped++;
                continue;
            }

            try {
                $batch = $this->resolveBatch($batchId);
                $this->assignSingleBatch($batch, $assignee, $comment, $actor);
                $processed++;
            } catch (ValidationException $exception) {
                $skipped++;
            }
        }

        return [
            'processed' => $processed,
            'skipped' => $skipped,
        ];
    }

    public function assignSingleBatch(SampleHeader $batch, User $assignee, ?string $comment, User $actor): SampleHeaderUserAssignment
    {
        return DB::transaction(function () use ($batch, $assignee, $comment, $actor): SampleHeaderUserAssignment {
            $existingPending = SampleHeaderUserAssignment::query()
                ->where('sample_header_id', $batch->id)
                ->pending()
                ->latest('created_at')
                ->first();

            if ($existingPending !== null) {
                if ((string) $existingPending->to_user_id === (string) $assignee->id) {
                    if ($comment !== null && trim($comment) !== '') {
                        $existingPending->comment = trim($comment);
                        $existingPending->save();
                    }

                    return $existingPending;
                }

                $existingPending->update([
                    'status' => SampleHeaderUserAssignment::STATUS_COMPLETED,
                    'completed_at' => now(),
                    'completed_by' => $actor->id,
                ]);
            }

            $fromUserId = $existingPending !== null
                ? $existingPending->to_user_id
                : $actor->id;

            return SampleHeaderUserAssignment::query()->create([
                'sample_header_id' => $batch->id,
                'from_user_id' => $fromUserId,
                'to_user_id' => $assignee->id,
                'assigned_by_user_id' => $actor->id,
                'comment' => $comment !== null && trim($comment) !== '' ? trim($comment) : null,
                'status' => SampleHeaderUserAssignment::STATUS_PENDING,
            ]);
        });
    }

    public function resolveBatch(string $batchId): SampleHeader
    {
        $batch = SampleHeader::query()
            ->where('isactive', 1)
            ->find($batchId);

        if ($batch === null) {
            throw ValidationException::withMessages([
                'batch_ids' => 'One or more selected batches were not found.',
            ]);
        }

        return $batch;
    }

    public function resolveAssignableUser(string $userId): User
    {
        $user = User::query()
            ->where('id', $userId)
            ->where('active', 1)
            ->where('is_client', 0)
            ->whereNull('supplier_id')
            ->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'assignee_user_id' => 'Select a valid active lab user.',
            ]);
        }

        return $user;
    }
}
