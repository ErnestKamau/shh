<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;
use App\Models\Sampleworkflow\SampleHeaderUserAssignment;
use App\SampleHeader;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SampleHeaderAssignmentService
{
    /**
     * Request-scoped cache so bulk result saves only assign each batch once.
     *
     * @var array<string, SampleHeaderUserAssignment|null>
     */
    private static array $resultEntryAssignments = [];

    public function __construct(
        private readonly LabSectionResultAccess $labSectionResultAccess,
    ) {
    }

    /**
     * Assign a batch to the user who just entered/saved results.
     * Safe to call repeatedly for the same batch in one request.
     * Completes the pending assignment when all integrity-assigned tests for that user are saved.
     */
    public function assignOnResultEntry(SampleHeader|string|null $batch, ?User $actor = null): ?SampleHeaderUserAssignment
    {
        $actor = $actor ?? Auth::user();
        if ($actor === null || ! $actor instanceof User) {
            return null;
        }

        if ($batch === null || $batch === '') {
            return null;
        }

        $batchId = $batch instanceof SampleHeader ? (string) $batch->id : trim((string) $batch);
        if ($batchId === '') {
            return null;
        }

        $cacheKey = $batchId.':'.(string) $actor->id;
        if (array_key_exists($cacheKey, self::$resultEntryAssignments)) {
            $completed = $this->completeIfScopedResultsSaved($batchId, $actor);
            if ($completed !== null) {
                return self::$resultEntryAssignments[$cacheKey] = $completed;
            }

            return self::$resultEntryAssignments[$cacheKey];
        }

        try {
            $header = $batch instanceof SampleHeader
                ? $batch
                : $this->resolveBatch($batchId);

            $assignment = $this->assignSingleBatch(
                $header,
                $actor,
                null,
                $actor,
            );

            $completed = $this->completeIfScopedResultsSaved($header, $actor);

            return self::$resultEntryAssignments[$cacheKey] = $completed ?? $assignment;
        } catch (ValidationException) {
            return self::$resultEntryAssignments[$cacheKey] = null;
        }
    }

    /**
     * Mark the assignee's pending batch assignment completed when every integrity-assigned
     * test for that analyst on the batch has a saved result.
     */
    public function completeIfScopedResultsSaved(SampleHeader|string|null $batch, ?User $assignee = null): ?SampleHeaderUserAssignment
    {
        $assignee = $assignee ?? Auth::user();
        if ($assignee === null || ! $assignee instanceof User) {
            return null;
        }

        if ($batch === null || $batch === '') {
            return null;
        }

        $batchId = $batch instanceof SampleHeader ? (string) $batch->id : trim((string) $batch);
        if ($batchId === '') {
            return null;
        }

        $pending = SampleHeaderUserAssignment::query()
            ->where('sample_header_id', $batchId)
            ->pending()
            ->forAssignee((string) $assignee->id)
            ->latest('created_at')
            ->first();

        if ($pending === null) {
            return null;
        }

        $rows = CapturedResult::query()
            ->where('sample_header_id', $batchId)
            ->get();

        if (! $this->analystScopedResultsAreComplete($assignee, $rows)) {
            return null;
        }

        $pending->update([
            'status' => SampleHeaderUserAssignment::STATUS_COMPLETED,
            'completed_at' => now(),
            'completed_by' => $assignee->id,
        ]);

        return $pending->fresh();
    }

    /**
     * Re-evaluate pending assignments for a user (e.g. dashboard load) and complete any
     * whose integrity-assigned tests are already fully saved.
     */
    public function syncCompletionsForAssignee(string $userId): int
    {
        $assignee = User::query()->find($userId);
        if (! $assignee instanceof User) {
            return 0;
        }

        $batchIds = SampleHeaderUserAssignment::query()
            ->pending()
            ->forAssignee($userId)
            ->pluck('sample_header_id')
            ->map(fn ($id): string => trim((string) $id))
            ->filter(fn (string $id): bool => $id !== '')
            ->unique()
            ->values();

        $completed = 0;
        foreach ($batchIds as $batchId) {
            if ($this->completeIfScopedResultsSaved($batchId, $assignee) !== null) {
                $completed++;
            }
        }

        return $completed;
    }

    /**
     * True when the analyst has at least one integrity-assigned test on the batch that
     * requires a result, and every such test has a non-empty saved result.
     *
     * @param  Collection<int, CapturedResult>|iterable<int, CapturedResult>  $rows
     */
    public function analystScopedResultsAreComplete(User $assignee, iterable $rows): bool
    {
        $scoped = Collection::make($rows)
            ->filter(fn ($row): bool => $row instanceof CapturedResult)
            ->filter(function (CapturedResult $row) use ($assignee): bool {
                if ((bool) ($row->has_no_result_capture ?? false)) {
                    return false;
                }

                return $this->labSectionResultAccess->isAssignedAnalystForResult($assignee, $row);
            })
            ->values();

        if ($scoped->isEmpty()) {
            return false;
        }

        return $scoped->every(
            fn (CapturedResult $row): bool => trim((string) ($row->result ?? '')) !== ''
        );
    }

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
            ->where(function ($query): void {
                $query->where('is_client', 0)
                    ->orWhereNull('is_client');
            })
            ->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'assignee_user_id' => 'Select a valid active lab user.',
            ]);
        }

        return $user;
    }
}
