<?php

namespace App\Services\LiveData\Handlers;

use Illuminate\Support\Facades\DB;
use App\Services\LiveData\Contracts\LiveDataHandlerInterface;
use App\Services\LiveData\Contracts\FormatterInterface;
use App\Services\LiveData\DTOs\LiveDataResult;

class SampleHandler implements LiveDataHandlerInterface
{
    private const REVIEW_STATUSES = [
        'Samples Request Review',
        'Sample Verification',
        'Sample Approval',
    ];

    public function __construct(protected FormatterInterface $formatter) {}

    public function supports(string $intent): bool
    {
        return str_starts_with($intent, 'sample_');
    }

    public function handle(string $intent, ?string $question = null): LiveDataResult
    {
        $data = match ($intent) {
            'sample_count_total'          => $this->sampleCountTotal(),
            'sample_count_pending_review' => $this->sampleCountPendingReview(),
            'sample_count_in_lab'         => $this->sampleCountInLab(),
            'samples_by_status'           => $this->samplesByStatus(),
            default => ['reply' => "Unsupported sample intent: {$intent}", 'value' => null]
        };

        return LiveDataResult::fromArray($intent, $data);
    }

    private function sampleCountTotal(): array
    {
        $count = DB::table('sample_headers')->where('isactive', 1)->count();
        $earliest = DB::table('sample_headers')->where('isactive', 1)->min('created_at');

        return [
            'reply' => $this->formatter->formatCount(
                $count,
                'Total Samples Processed',
                'This is the total number of batches logged since launch.',
                $earliest ? \Carbon\Carbon::parse($earliest)->format('d M Y') : 'N/A'
            ),
            'value' => $count,
        ];
    }

    private function sampleCountPendingReview(): array
    {
        $counts = DB::table('sample_headers')
            ->whereIn('status', self::REVIEW_STATUSES)
            ->where('isactive', 1)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        $total = array_sum($counts);
        $breakdown = collect($counts)->map(fn($cnt, $status) => "- **{$status}**: {$cnt}")->implode("\n");

        $reply = "## Samples Awaiting Review\n\n"
            . "**{$total}** batches currently require laboratory sign-off.\n\n"
            . ($breakdown ?: "No samples currently in review stage.");

        return ['reply' => $reply, 'value' => $total];
    }

    private function sampleCountInLab(): array
    {
        $count = DB::table('sample_headers')
            ->where('status', 'Samples In Lab')
            ->where('isactive', 1)
            ->count();

        return [
            'reply' => $this->formatter->formatCount(
                $count,
                'Samples Currently In Lab',
                'These batches are actively being processed / analyzed.'
            ),
            'value' => $count,
        ];
    }

    private function samplesByStatus(): array
    {
        $rows = DB::table('sample_headers')
            ->where('isactive', 1)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->orderByDesc('cnt')
            ->get();

        $total = $rows->sum('cnt');
        $table = $this->formatter->buildTable(['Stage', 'Count'], $rows->toArray());

        return [
            'reply' => "## Samples by Workflow Stage\n\n" . $table,
            'value' => $total
        ];
    }
}
