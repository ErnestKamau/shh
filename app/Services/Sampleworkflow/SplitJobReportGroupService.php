<?php

namespace App\Services\Sampleworkflow;

use App\Enums\Commercial\SampleHeaderPoStatus;
use App\SampleHeader;
use Illuminate\Database\Eloquent\Collection;

/**
 * A job split for missing PO cover and its Awaiting PO parts share one test report,
 * issued from the root job once every part is complete.
 */
class SplitJobReportGroupService
{
    /**
     * Workflow statuses after which a job's results are final for reporting.
     *
     * @var list<string>
     */
    private const REPORT_READY_STATUSES = [
        'Reports In Payment',
        'Reports for Collection',
    ];

    public function rootIdOf(SampleHeader $batch): string
    {
        return filled($batch->split_from_sample_header_id)
            ? (string) $batch->split_from_sample_header_id
            : (string) $batch->id;
    }

    public function isSplitPart(SampleHeader $batch): bool
    {
        return filled($batch->split_from_sample_header_id);
    }

    /**
     * Root first, then live parts in creation order. Cancelled parts are not reported.
     *
     * @return Collection<int, SampleHeader>
     */
    public function members(SampleHeader $batch): Collection
    {
        $rootId = $this->rootIdOf($batch);

        $root = $rootId === (string) $batch->id ? $batch : SampleHeader::query()->find($rootId);
        if ($root === null) {
            return new Collection([$batch]);
        }

        $parts = SampleHeader::query()
            ->where('split_from_sample_header_id', $rootId)
            ->where(function ($query): void {
                $query->whereNull('po_status')
                    ->orWhere('po_status', '!=', SampleHeaderPoStatus::Cancelled->value);
            })
            ->orderBy('created_at')
            ->get();

        return (new Collection([$root]))->concat($parts)->values();
    }

    /**
     * @return list<string>
     */
    public function reportGroupIds(SampleHeader $batch): array
    {
        if (! $this->isSplitPart($batch) && ! $this->hasParts($batch)) {
            return [(string) $batch->id];
        }

        return $this->members($batch)
            ->map(fn (SampleHeader $member): string => (string) $member->id)
            ->all();
    }

    public function hasParts(SampleHeader $batch): bool
    {
        return SampleHeader::query()
            ->where('split_from_sample_header_id', (string) $batch->id)
            ->exists();
    }

    /**
     * Reason the official group report cannot be issued or delivered from this job yet, or null.
     */
    public function reportBlocker(SampleHeader $batch): ?string
    {
        if ($this->isSplitPart($batch)) {
            $rootCode = SampleHeader::query()->whereKey($this->rootIdOf($batch))->value('batch_code');

            return sprintf(
                'Job %s was split off job %s. Its samples are reported on job %s\'s test report.',
                $batch->batch_code,
                $rootCode ?? 'the original job',
                $rootCode ?? 'the original',
            );
        }

        if (! $this->hasParts($batch)) {
            return null;
        }

        $pending = $this->members($batch)
            ->reject(fn (SampleHeader $member): bool => (string) $member->id === (string) $batch->id)
            ->reject(fn (SampleHeader $member): bool => $this->isReportReady($member))
            ->map(fn (SampleHeader $member): string => sprintf(
                '%s (%s)',
                $member->batch_code,
                (string) $member->po_status === SampleHeaderPoStatus::AwaitingPo->value
                    ? SampleHeaderPoStatus::AwaitingPo->label()
                    : (string) $member->status,
            ))
            ->values();

        if ($pending->isEmpty()) {
            return null;
        }

        return sprintf(
            'Job %s shares its test report with %s. Issue the report once %s complete, or cancel the held job.',
            $batch->batch_code,
            $pending->implode(', '),
            $pending->count() === 1 ? 'that job is' : 'those jobs are',
        );
    }

    public function isReportReady(SampleHeader $batch): bool
    {
        $status = (string) $batch->status;

        if (isCompletedReportStatus($status) || in_array($status, self::REPORT_READY_STATUSES, true)) {
            return true;
        }

        return $status === 'Sample Approval' && $batch->hasCompletedSampleApproval();
    }
}
