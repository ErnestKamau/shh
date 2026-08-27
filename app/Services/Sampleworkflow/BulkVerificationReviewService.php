<?php

namespace App\Services\Sampleworkflow;

use App\BatchLabSectionApprover;
use App\CapturedResult;
use App\SampleHeader;
use App\SampleType;
use Illuminate\Support\Collection;

/**
 * Builds the Sample Verification bulk-review payload (samples + captured results).
 * Shared by Brazil (brl) and UAE (uae) — both companies use this workflow board.
 */
final class BulkVerificationReviewService
{
    public function __construct(
        private readonly BatchVerificationReadinessService $readiness,
    ) {
    }

    /**
     * @param  list<string>  $batchIds
     * @return list<array{
     *     id: string,
     *     batch_code: string,
     *     customer: string,
     *     sample_type: string,
     *     lab_sections: string,
     *     entered: int,
     *     missing: int,
     *     verified: bool,
     *     result_count: int,
     *     samples: list<array{
     *         id: string,
     *         code: string,
     *         sample_type: string,
     *         notes: string,
     *         results: list<array{
     *             analyte: string,
     *             analysis_type: string,
     *             result: string,
     *             unit: string,
     *             remark: string,
     *             remark_tone: string,
     *             analyst: string,
     *             entered: bool,
     *             no_capture: bool
     *         }>
     *     }>
     * }>
     */
    public function build(array $batchIds): array
    {
        $ids = collect($batchIds)
            ->map(static fn ($id): string => trim((string) $id))
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $batches = SampleHeader::query()
            ->with([
                'customer:id,name',
                'sample_type:id,name',
                'approvers',
                'samples' => static fn ($query) => $query->orderBy('sample_code'),
            ])
            ->whereIn('id', $ids->all())
            ->get()
            ->keyBy(static fn (SampleHeader $batch): string => (string) $batch->id);

        $resultsByBatch = CapturedResult::query()
            ->whereIn('sample_header_id', $ids->all())
            ->whereNotNull('captured_results.analyte_id')
            ->whereRaw("captured_results.analyte_id::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$'")
            ->with([
                'sample:id,sample_code',
                'analysis_type:id,name',
                'my_analyte:id,name,code',
                'operator:id,name',
            ])
            ->orderBy('sample_detail_id')
            ->get()
            ->groupBy(static fn (CapturedResult $row): string => (string) $row->sample_header_id);

        $sampleTypeIds = $batches
            ->flatMap(static fn (SampleHeader $batch) => $batch->samples->pluck('sample_type_id'))
            ->map(static fn ($id): string => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $sampleTypeNames = $sampleTypeIds === []
            ? collect()
            : SampleType::query()->whereIn('id', $sampleTypeIds)->pluck('name', 'id');

        $verifiedIds = BatchLabSectionApprover::query()
            ->whereIn('batch_id', $ids->all())
            ->where('batch_status', 'Sample Verification')
            ->where('is_technical_reviewer', true)
            ->where('status', 1)
            ->pluck('batch_id')
            ->map(static fn ($id): string => (string) $id)
            ->all();

        $ordered = [];
        foreach ($ids as $id) {
            $batch = $batches->get($id);
            if (! $batch instanceof SampleHeader) {
                continue;
            }

            $ordered[] = $this->mapBatch(
                $batch,
                $resultsByBatch->get($id, collect()),
                $sampleTypeNames,
                in_array((string) $batch->id, $verifiedIds, true),
            );
        }

        return $ordered;
    }

    /**
     * @param  Collection<int, CapturedResult>  $results
     * @param  Collection<string, string>  $sampleTypeNames
     * @return array<string, mixed>
     */
    private function mapBatch(SampleHeader $batch, Collection $results, Collection $sampleTypeNames, bool $verified): array
    {
        $capturable = $results->filter(
            fn (CapturedResult $row): bool => $this->readiness->requiresResultCapture($row)
        );
        $entered = $capturable
            ->filter(fn (CapturedResult $row): bool => $this->readiness->isEnteredResult($row))
            ->count();
        $missing = max(0, $capturable->count() - $entered);

        $resultsBySample = $results->groupBy(
            static fn (CapturedResult $row): string => (string) $row->sample_detail_id
        );

        $samples = [];
        foreach ($batch->samples as $sample) {
            $sampleId = (string) $sample->id;
            $sampleResults = $resultsBySample->get($sampleId, collect());

            $samples[] = [
                'id' => $sampleId,
                'code' => trim((string) ($sample->sample_code ?? '')) ?: '—',
                'sample_type' => trim((string) ($sampleTypeNames->get((string) ($sample->sample_type_id ?? '')) ?? '')),
                'notes' => $this->sampleNotes($sample->comments ?? null),
                'results' => $sampleResults
                    ->map(fn (CapturedResult $row): array => $this->mapResult($row))
                    ->values()
                    ->all(),
            ];
        }

        $orphanGroups = $resultsBySample->filter(
            static fn (Collection $group, string $sampleId): bool => ! $batch->samples->contains(
                static fn ($sample): bool => (string) $sample->id === $sampleId
            )
        );

        foreach ($orphanGroups as $sampleId => $group) {
            /** @var CapturedResult|null $first */
            $first = $group->first();
            $samples[] = [
                'id' => (string) $sampleId,
                'code' => trim((string) ($first?->sample?->sample_code ?? $first?->sample_detail_code ?? '')) ?: '—',
                'sample_type' => '',
                'notes' => '',
                'results' => $group
                    ->map(fn (CapturedResult $row): array => $this->mapResult($row))
                    ->values()
                    ->all(),
            ];
        }

        $labSections = collect($batch->labSectionsForDisplay())
            ->map(static fn (array $section): string => trim((string) ($section['name'] ?? $section['code'] ?? '')))
            ->filter()
            ->implode(', ');

        return [
            'id' => (string) $batch->id,
            'batch_code' => (string) ($batch->batch_code ?? ''),
            'customer' => trim((string) ($batch->customer?->name ?? '')) ?: '—',
            'customer_id' => trim((string) ($batch->crm_customer_id ?? '')),
            'sample_type' => trim((string) ($batch->sample_type?->name ?? '')) ?: '—',
            'lab_sections' => $labSections !== '' ? $labSections : '—',
            'entered' => $entered,
            'missing' => $missing,
            'verified' => $verified,
            'approved' => $batch->hasCompletedSampleApproval(),
            'result_count' => $results->count(),
            'samples' => $samples,
        ];
    }

    /**
     * @return array{
     *     analyte: string,
     *     analysis_type: string,
     *     result: string,
     *     unit: string,
     *     remark: string,
     *     remark_tone: string,
     *     analyst: string,
     *     entered: bool,
     *     no_capture: bool
     * }
     */
    private function mapResult(CapturedResult $row): array
    {
        $noCapture = ! $this->readiness->requiresResultCapture($row);
        $entered = $this->readiness->isEnteredResult($row);
        $analyte = trim((string) ($row->my_analyte?->name ?? $row->analyte_code ?? ''));
        $remarkRaw = (string) ($row->remark ?? '');
        $remark = function_exists('format_result_remark')
            ? format_result_remark($remarkRaw)
            : $remarkRaw;
        $tone = 'neutral';
        if (function_exists('is_non_conforming_remark') && is_non_conforming_remark($remarkRaw)) {
            $tone = 'fail';
        } elseif ($remark === 'Conforming') {
            $tone = 'pass';
        }

        $resultText = trim((string) ($row->result ?? ''));
        if ($noCapture) {
            $resultText = 'No capture required';
        } elseif (! $entered) {
            $resultText = 'Not entered';
        }

        return [
            'analyte' => $analyte !== '' ? $analyte : '—',
            'analysis_type' => trim((string) ($row->analysis_type?->name ?? '')) ?: '—',
            'result' => $resultText !== '' ? $resultText : '—',
            'unit' => trim((string) ($row->effectiveReportingUnitName() ?? '')) ?: '—',
            'remark' => $remark !== '' ? $remark : '—',
            'remark_tone' => $tone,
            'analyst' => trim((string) ($row->operator?->name ?? '')) ?: '—',
            'entered' => $entered,
            'no_capture' => $noCapture,
        ];
    }

    private function sampleNotes(mixed $comments): string
    {
        if (is_array($comments)) {
            $comments = $comments['text'] ?? $comments['html'] ?? $comments[0] ?? '';
        }

        $notes = trim(strip_tags(html_entity_decode((string) $comments, ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        if ($notes === '' || str_starts_with($notes, 'eyJ')) {
            return '';
        }

        return mb_strlen($notes) > 180 ? mb_substr($notes, 0, 177).'…' : $notes;
    }
}
