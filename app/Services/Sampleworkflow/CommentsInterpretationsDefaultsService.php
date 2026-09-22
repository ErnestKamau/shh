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
     * @param  Collection<int, CapturedResult>  $results
     */
    private function buildRemarksHtml(SampleDetails $sample, Collection $results): string
    {
        $standards = $this->resolveAssignedStandards($sample);

        if ($standards === []) {
            return '';
        }

        $outcomes = [];
        foreach ($standards as $index => $standard) {
            $passed = $this->standardPassed($results, $index);
            $outcomes[] = [
                'name' => $standard['name'],
                'passed' => $passed,
                // One statement per specification from the standard pass/fail set (not per analyte).
                'comment' => $this->passFailComments->commentFor(
                    (string) $standard['id'],
                    null,
                    $passed ? 'PASS' : 'FAIL',
                ),
            ];
        }

        return e($this->formatConformityRemark($outcomes));
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    private function resolveAssignedStandards(SampleDetails $sample): array
    {
        $ids = array_values(array_filter([
            $sample->main_standard ?: null,
            $sample->secondary_standard ?: null,
            $sample->third_standard_id ?: null,
        ], fn ($id) => filled($id)));

        if ($ids === []) {
            return [];
        }

        $namesById = Standards::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'code'])
            ->keyBy('id');

        $standards = [];
        foreach ($ids as $id) {
            $row = $namesById->get($id);
            $name = trim((string) ($row->name ?? $row->code ?? ''));
            if ($name === '') {
                continue;
            }
            $standards[] = ['id' => (string) $id, 'name' => $name];
        }

        return $standards;
    }

    /**
     * @param  Collection<int, CapturedResult>  $results
     */
    private function standardPassed(Collection $results, int $index): bool
    {
        $remarkColumn = match ($index) {
            0 => 'remark',
            1 => 'sec_remark',
            default => 'third_remark',
        };

        foreach ($results as $result) {
            if (is_non_conforming_remark($result->{$remarkColumn} ?? null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<array{name: string, passed: bool, comment?: string}>  $outcomes
     */
    private function formatConformityRemark(array $outcomes): string
    {
        $customParts = [];
        $hasCustom = false;
        foreach ($outcomes as $outcome) {
            $comment = trim((string) ($outcome['comment'] ?? ''));
            if ($comment !== '') {
                $hasCustom = true;
                $customParts[] = $comment;
            } else {
                $customParts[] = $this->defaultSentenceForOutcome($outcome);
            }
        }

        if ($hasCustom) {
            return implode(' ', $customParts);
        }

        $count = count($outcomes);

        if ($count === 1) {
            return $this->defaultSentenceForOutcome($outcomes[0]);
        }

        if ($count === 2) {
            $first = $outcomes[0];
            $second = $outcomes[1];

            if ($first['passed'] && $second['passed']) {
                return 'The above test results conform to "'.$first['name'].'" & "'.$second['name'].'"';
            }

            if (! $first['passed'] && ! $second['passed']) {
                return 'The above test results do not conform to "'.$first['name'].'" & "'.$second['name'].'"';
            }

            if ($first['passed'] && ! $second['passed']) {
                return 'The above test results conform to "'.$first['name'].'" & do not conform to "'.$second['name'].'"';
            }

            return 'The above test results do not conform to "'.$first['name'].'" & conform to "'.$second['name'].'"';
        }

        $parts = [];
        foreach ($outcomes as $outcome) {
            $parts[] = ($outcome['passed'] ? 'conform to' : 'do not conform to').' "'.$outcome['name'].'"';
        }

        return 'The above test results '.implode(' & ', $parts);
    }

    /**
     * @param  array{name: string, passed: bool}  $outcome
     */
    private function defaultSentenceForOutcome(array $outcome): string
    {
        $name = $outcome['name'];
        if ($outcome['passed']) {
            return 'The above test results conform to "'.$name.'"';
        }

        return 'The above test results do not conform to "'.$name.'"';
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
