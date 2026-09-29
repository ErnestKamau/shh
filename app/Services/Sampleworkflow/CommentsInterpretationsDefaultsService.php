<?php

namespace App\Services\Sampleworkflow;

use App\AnalysisType;
use App\CapturedResult;
use App\SampleDetails;
use App\SampleHeader;
use App\Standards;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CommentsInterpretationsDefaultsService
{
    private const PESTICIDE_NOTE = 'Refer to attached list of Pesticide Residues tested. If pesticide residue is not reflected in the result table above, the residue is Not Detected in the sample';

    private const SUBCONTRACTED_NOTE = "'¹' denotes that the test was performed by a subcontracted laboratory.";

    private const NON_ACCREDITED_NOTE = "'*' denotes that the test is not accredited.";

    public function __construct(
        private readonly StandardPassFailCommentService $passFailComments,
    ) {}

    /**
     * @return array{header_body: string, notes_body: string}
     */
    public function generate(SampleDetails $sample, ?SampleHeader $batch = null): array
    {
        $batch ??= SampleHeader::find($sample->sample_header_id);
        $results = CapturedResult::query()
            ->where('sample_detail_id', $sample->id)
            ->get();

        return [
            'header_body' => $this->buildRemarksHtml($sample, $results),
            'notes_body' => $this->buildNotesHtml($sample, $batch, $results),
        ];
    }

    /**
     * Build the report statement of conformity from per-specification outcomes.
     * Positive (conform) clauses come before negative (do not conform) clauses.
     *
     * @param  list<array{id?: string, name: string, passed: bool, comment?: string}>  $outcomes
     */
    public function formatConformityRemark(array $outcomes): string
    {
        if ($outcomes === []) {
            return '';
        }

        if (count($outcomes) === 1) {
            $comment = trim((string) ($outcomes[0]['comment'] ?? ''));
            if ($comment !== '') {
                return $comment;
            }

            return $this->defaultSentenceForOutcome($outcomes[0]);
        }

        $ordered = $this->orderOutcomesPositiveFirst($outcomes);
        $parts = [];
        foreach ($ordered as $outcome) {
            $label = $this->displayNameForOutcome($outcome);
            $parts[] = ($outcome['passed'] ? 'conform to ' : 'do not conform to ').$label;
        }

        return 'The above test results '.implode(' and ', $parts);
    }

    /**
     * @param  Collection<int, CapturedResult>  $results
     */
    private function buildRemarksHtml(SampleDetails $sample, Collection $results): string
    {
        $outcomes = $this->resolveSpecificationOutcomes($sample, $results);

        if ($outcomes === []) {
            return '';
        }

        return e($this->formatConformityRemark($outcomes));
    }

    /**
     * One outcome per distinct specification used on the sample's results.
     * A specification fails when any result evaluated against it is non-conforming.
     *
     * @param  Collection<int, CapturedResult>  $results
     * @return list<array{id: string, name: string, passed: bool, comment: string}>
     */
    private function resolveSpecificationOutcomes(SampleDetails $sample, Collection $results): array
    {
        /** @var array<string, array{id: string, passed: bool, order: int}> $grouped */
        $grouped = [];
        $order = 0;

        foreach ($this->standardSlots() as $slot) {
            foreach ($results as $result) {
                $standardId = $this->effectiveStandardId($result, $sample, $slot['result_id'], $slot['sample_id']);
                if ($standardId === null) {
                    continue;
                }

                $passed = ! is_non_conforming_remark($result->{$slot['remark']} ?? null);

                if (! isset($grouped[$standardId])) {
                    $grouped[$standardId] = [
                        'id' => $standardId,
                        'passed' => $passed,
                        'order' => $order++,
                    ];
                    continue;
                }

                if (! $passed) {
                    $grouped[$standardId]['passed'] = false;
                }
            }
        }

        if ($grouped === []) {
            return [];
        }

        $namesById = Standards::query()
            ->whereIn('id', array_keys($grouped))
            ->get(['id', 'name', 'code'])
            ->keyBy(fn ($row) => (string) $row->id);

        $outcomes = [];
        foreach ($grouped as $standardId => $meta) {
            $row = $namesById->get($standardId);
            $name = trim((string) ($row->name ?? $row->code ?? ''));
            if ($name === '') {
                continue;
            }

            $outcomes[] = [
                'id' => $standardId,
                'name' => $name,
                'passed' => $meta['passed'],
                'comment' => $this->passFailComments->commentFor(
                    $standardId,
                    null,
                    $meta['passed'] ? 'PASS' : 'FAIL',
                ),
                'order' => $meta['order'],
            ];
        }

        usort($outcomes, static fn (array $a, array $b): int => $a['order'] <=> $b['order']);

        return array_map(static function (array $outcome): array {
            unset($outcome['order']);

            return $outcome;
        }, $outcomes);
    }

    /**
     * @return list<array{result_id: string, sample_id: string, remark: string}>
     */
    private function standardSlots(): array
    {
        return [
            ['result_id' => 'main_standard_id', 'sample_id' => 'main_standard', 'remark' => 'remark'],
            ['result_id' => 'secondary_standard_id', 'sample_id' => 'secondary_standard', 'remark' => 'sec_remark'],
            ['result_id' => 'third_standard_id', 'sample_id' => 'third_standard_id', 'remark' => 'third_remark'],
        ];
    }

    private function effectiveStandardId(
        CapturedResult $result,
        SampleDetails $sample,
        string $resultColumn,
        string $sampleColumn,
    ): ?string {
        $id = trim((string) ($result->{$resultColumn} ?: $sample->{$sampleColumn} ?: ''));

        return $id !== '' ? $id : null;
    }

    /**
     * @param  list<array{name: string, passed: bool, comment?: string}>  $outcomes
     * @return list<array{name: string, passed: bool, comment?: string}>
     */
    private function orderOutcomesPositiveFirst(array $outcomes): array
    {
        $passed = [];
        $failed = [];

        foreach ($outcomes as $outcome) {
            if ($outcome['passed']) {
                $passed[] = $outcome;
            } else {
                $failed[] = $outcome;
            }
        }

        return array_merge($passed, $failed);
    }

    /**
     * @param  array{name: string, passed: bool, comment?: string}  $outcome
     */
    private function displayNameForOutcome(array $outcome): string
    {
        $comment = trim((string) ($outcome['comment'] ?? ''));
        if ($comment !== '' && preg_match('/(?:do\s+not\s+)?conform\s+to\s+["\']?(.+?)["\']?\s*$/iu', $comment, $matches) === 1) {
            $extracted = trim($matches[1], " \t\"'");
            if ($extracted !== '') {
                return $extracted;
            }
        }

        return $outcome['name'];
    }

    /**
     * @param  array{name: string, passed: bool}  $outcome
     */
    private function defaultSentenceForOutcome(array $outcome): string
    {
        $name = $outcome['name'];
        if ($outcome['passed']) {
            return 'The above test results conform to '.$name;
        }

        return 'The above test results do not conform to '.$name;
    }

    /**
     * @param  Collection<int, CapturedResult>  $results
     */
    private function buildNotesHtml(SampleDetails $sample, ?SampleHeader $batch, Collection $results): string
    {
        $notes = [
            'Test results relate only to the samples tested',
            'This report shall not be reproduced except in full, without the written approval of the Laboratory',
        ];

        if ($batch === null || (int) ($batch->sampled_by_company_personnel ?? 0) !== 1) {
            $notes[] = 'Results apply only to the sample as received';
        }

        if ($this->sampleHasPesticide($results)) {
            $notes[] = self::PESTICIDE_NOTE;
        }

        if ($results->contains(fn (CapturedResult $r) => (int) ($r->analyte_status_contracted ?? 0) === 1)) {
            $notes[] = self::SUBCONTRACTED_NOTE;
        }

        if ($results->contains(fn (CapturedResult $r) => (int) ($r->analyte_accredited ?? 1) === 0)) {
            $notes[] = self::NON_ACCREDITED_NOTE;
        }

        $items = '';
        foreach ($notes as $note) {
            $items .= '<li>'.e($note).'</li>';
        }

        return '<ol>'.$items.'</ol>';
    }

    /**
     * @param  Collection<int, CapturedResult>  $results
     */
    private function sampleHasPesticide(Collection $results): bool
    {
        if ($results->contains(fn (CapturedResult $r) => (int) ($r->is_pesticide ?? 0) === 1)) {
            return true;
        }

        $typeIds = $results->pluck('analysis_type_id')->filter()->unique()->values()->all();
        if ($typeIds === []) {
            return false;
        }

        return AnalysisType::query()
            ->whereIn('id', $typeIds)
            ->where('is_pesticide', 1)
            ->exists();
    }

    public function isHtmlEmpty(?string $html): bool
    {
        if ($html === null) {
            return true;
        }

        $plain = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $plain = preg_replace('/\x{00A0}/u', ' ', $plain) ?? $plain;
        $plain = trim($plain);

        return $plain === '' || Str::lower($plain) === '&nbsp;';
    }
}
