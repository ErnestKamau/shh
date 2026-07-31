<?php

namespace App\Services\Sampleworkflow;

use App\AnalysisElements;
use App\CapturedResult;
use App\Lab;
use App\Models\SampleSubmissionRequest;
use App\Models\SubcontractingDispatchAssignment;
use App\SampleAnalysisStage;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SubcontractingAssignmentService
{
    public function __construct(
        private readonly EnquiryReceptionReadinessService $readinessService,
    ) {}

    /**
     * @return list<array{id: string, label: string, analysis_type: string}>
     */
    public function resolveSubcontractedTests(SampleSubmissionRequest $enquiry): array
    {
        return $this->mapElementsToTests($this->resolveSubcontractedElementIds($enquiry));
    }

    /**
     * Tests that remain in-house (Amspec) on a partially subcontracted request.
     *
     * @return list<array{id: string, label: string, analysis_type: string, lab_section_id: ?string, lab_section_name: string}>
     */
    public function resolveInHouseTests(SampleSubmissionRequest $enquiry): array
    {
        $subcontracted = array_flip($this->resolveSubcontractedElementIds($enquiry));
        $inHouseIds = array_values(array_filter(
            $this->resolveAllRequestedElementIds($enquiry),
            fn (string $id): bool => ! isset($subcontracted[$id])
        ));

        if ($inHouseIds === []) {
            return [];
        }

        $configService = app(AcceptanceFormSampleConfigService::class);

        return AnalysisElements::query()
            ->with(['analyte:id,name,code', 'analysis_type:id,name,code,lab_section_id'])
            ->whereIn('id', $inHouseIds)
            ->get()
            ->map(function (AnalysisElements $element) use ($configService): array {
                $analyteName = trim((string) ($element->analyte?->name ?? $element->analyte?->code ?? ''));
                $analyteCode = trim((string) ($element->analyte?->code ?? ''));
                $label = $analyteName !== '' ? $analyteName : ('Element '.$element->id);
                if ($analyteCode !== '' && $analyteCode !== $analyteName) {
                    $label .= ' ('.$analyteCode.')';
                }

                $analysisType = trim((string) ($element->analysis_type?->name ?? $element->analysis_type?->code ?? ''));
                $labSectionId = $configService->resolveLabSectionIdForElement(
                    (string) $element->id,
                    $element->analysis_type_id !== null ? (string) $element->analysis_type_id : null,
                );
                $labSectionName = $labSectionId !== null
                    ? (string) (SampleAnalysisStage::query()->whereKey($labSectionId)->value('name') ?? 'Lab section')
                    : 'Unassigned section';

                return [
                    'id' => (string) $element->id,
                    'label' => $label,
                    'analysis_type' => $analysisType !== '' ? $analysisType : '—',
                    'lab_section_id' => $labSectionId,
                    'lab_section_name' => $labSectionName,
                ];
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * Distinct lab sections for in-house tests (for analyst assignment UI).
     *
     * @return list<array{id: string, name: string, test_ids: list<string>, test_labels: list<string>}>
     */
    public function resolveInHouseLabSections(SampleSubmissionRequest $enquiry): array
    {
        $tests = $this->resolveInHouseTests($enquiry);
        $grouped = [];

        foreach ($tests as $test) {
            $sectionId = (string) ($test['lab_section_id'] ?? '');
            if ($sectionId === '') {
                $sectionId = '_unassigned';
            }

            $grouped[$sectionId] ??= [
                'id' => $sectionId === '_unassigned' ? '' : $sectionId,
                'name' => (string) ($test['lab_section_name'] ?? 'Lab section'),
                'test_ids' => [],
                'test_labels' => [],
            ];
            $grouped[$sectionId]['test_ids'][] = (string) $test['id'];
            $grouped[$sectionId]['test_labels'][] = (string) $test['label'];
        }

        return array_values(array_filter(
            $grouped,
            fn (array $section): bool => ($section['id'] ?? '') !== ''
        ));
    }

    /**
     * @param  list<string>  $elementIds
     * @return list<array{id: string, label: string, analysis_type: string}>
     */
    private function mapElementsToTests(array $elementIds): array
    {
        if ($elementIds === []) {
            return [];
        }

        return AnalysisElements::query()
            ->with(['analyte:id,name,code', 'analysis_type:id,name,code'])
            ->whereIn('id', $elementIds)
            ->get()
            ->map(function (AnalysisElements $element): array {
                $analyteName = trim((string) ($element->analyte?->name ?? $element->analyte?->code ?? ''));
                $analyteCode = trim((string) ($element->analyte?->code ?? ''));
                $label = $analyteName !== '' ? $analyteName : ('Element '.$element->id);
                if ($analyteCode !== '' && $analyteCode !== $analyteName) {
                    $label .= ' ('.$analyteCode.')';
                }

                $analysisType = trim((string) ($element->analysis_type?->name ?? $element->analysis_type?->code ?? ''));

                return [
                    'id' => (string) $element->id,
                    'label' => $label,
                    'analysis_type' => $analysisType !== '' ? $analysisType : '—',
                ];
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * All analysis element IDs selected on the enquiry / quotation (subcontracted + in-house).
     *
     * @return list<string>
     */
    public function resolveAllRequestedElementIds(SampleSubmissionRequest $enquiry): array
    {
        $ids = collect();

        $enquiry->loadMissing([
            'requestedAnalyses:id,sample_submission_request_id,analysis_element_id',
            'currentQuotation.details',
            'acceptedQuotation.details',
        ]);

        foreach ($enquiry->requestedAnalyses as $analysis) {
            $elementId = trim((string) ($analysis->analysis_element_id ?? ''));
            if ($elementId !== '') {
                $ids->push($elementId);
            }
        }

        $quotation = $this->readinessService->resolveAcceptedQuotation($enquiry)
            ?? $enquiry->currentQuotation
            ?? $enquiry->acceptedQuotation;

        if ($quotation !== null) {
            $quotation->loadMissing('details');
            foreach ($quotation->details as $detail) {
                $directId = trim((string) ($detail->analysis_element_id ?? ''));
                if ($directId !== '') {
                    $ids->push($directId);
                }

                foreach (['default_analytes', 'accredited_analytes', 'subcontracted_analytes', 'sub_acc_analytes'] as $field) {
                    foreach (explode(',', (string) ($detail->{$field} ?? '')) as $rawId) {
                        $elementId = trim($rawId);
                        if ($elementId !== '') {
                            $ids->push($elementId);
                        }
                    }
                }
            }
        }

        $parameterIds = $enquiry->parameter_ids ?? null;
        if (is_string($parameterIds) && $parameterIds !== '') {
            $decoded = json_decode($parameterIds, true);
            $parameterIds = is_array($decoded) ? $decoded : [];
        }
        if (is_array($parameterIds)) {
            foreach ($parameterIds as $rawId) {
                $id = trim((string) $rawId);
                if ($id !== '') {
                    $ids->push($id);
                }
            }
        }

        $sampleLines = $enquiry->sample_lines ?? null;
        if (is_string($sampleLines) && $sampleLines !== '') {
            $decoded = json_decode($sampleLines, true);
            $sampleLines = is_array($decoded) ? $decoded : [];
        }
        if (is_array($sampleLines)) {
            foreach ($sampleLines as $line) {
                if (! is_array($line)) {
                    continue;
                }
                $id = trim((string) ($line['analysis_element_id'] ?? ''));
                if ($id !== '') {
                    $ids->push($id);
                }
            }
        }

        $config = $enquiry->enquiry_sample_configuration ?? null;
        if (is_array($config)) {
            foreach ($config as $row) {
                if (! is_array($row)) {
                    continue;
                }
                foreach ((array) ($row['parameter_keys'] ?? []) as $key) {
                    $id = trim((string) $key);
                    if ($id !== '') {
                        $ids->push($id);
                    }
                }
            }
        }

        $uniqueIds = $ids->filter()->unique()->values()->all();
        if ($uniqueIds === []) {
            return [];
        }

        return AnalysisElements::query()
            ->whereIn('id', $uniqueIds)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function resolveSubcontractedElementIds(SampleSubmissionRequest $enquiry): array
    {
        $ids = collect();

        $enquiry->loadMissing([
            'requestedAnalyses.analysisElement:id,sub_contracted',
            'currentQuotation.details',
            'acceptedQuotation.details',
        ]);

        foreach ($enquiry->requestedAnalyses as $analysis) {
            $elementId = trim((string) ($analysis->analysis_element_id ?? ''));
            if ($elementId === '') {
                continue;
            }

            if ((bool) ($analysis->analysisElement?->sub_contracted ?? false)) {
                $ids->push($elementId);
            }
        }

        $quotation = $this->readinessService->resolveAcceptedQuotation($enquiry)
            ?? $enquiry->currentQuotation
            ?? $enquiry->acceptedQuotation;

        if ($quotation !== null) {
            $quotation->loadMissing('details');
            foreach ($quotation->details as $detail) {
                foreach (explode(',', (string) ($detail->subcontracted_analytes ?? '')) as $rawId) {
                    $elementId = trim($rawId);
                    if ($elementId !== '') {
                        $ids->push($elementId);
                    }
                }
            }

            foreach ($this->readinessService->resolveElementFlagsFromQuotation($quotation) as $elementId => $flags) {
                if (! empty($flags['subcontracted'])) {
                    $ids->push((string) $elementId);
                }
            }
        }

        foreach ($this->elementIdsFromJsonSelections($enquiry) as $elementId) {
            $ids->push($elementId);
        }

        $uniqueIds = $ids->filter()->unique()->values()->all();
        if ($uniqueIds === []) {
            return [];
        }

        return AnalysisElements::query()
            ->whereIn('id', $uniqueIds)
            ->where(function ($query) use ($uniqueIds, $quotation): void {
                $query->where('sub_contracted', 1);

                if ($quotation === null) {
                    return;
                }

                $quoteFlagged = collect($this->readinessService->resolveElementFlagsFromQuotation($quotation))
                    ->filter(fn (array $flags): bool => ! empty($flags['subcontracted']))
                    ->keys()
                    ->map(fn ($id) => (string) $id)
                    ->all();

                $quoteIds = [];
                foreach ($quotation->details as $detail) {
                    foreach (explode(',', (string) ($detail->subcontracted_analytes ?? '')) as $rawId) {
                        $id = trim($rawId);
                        if ($id !== '') {
                            $quoteIds[] = $id;
                        }
                    }
                }

                $forcedIds = array_values(array_unique(array_merge($quoteFlagged, $quoteIds)));
                $forcedIds = array_values(array_intersect($forcedIds, $uniqueIds));
                if ($forcedIds !== []) {
                    $query->orWhereIn('id', $forcedIds);
                }
            })
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string>  $assignments  analysis_element_id => lab_id
     */
    public function persistAssignments(
        SampleSubmissionRequest $enquiry,
        array $assignments,
        ?string $sampleHeaderId = null,
    ): Collection {
        $normalized = collect($assignments)
            ->mapWithKeys(fn ($labId, $elementId) => [
                trim((string) $elementId) => trim((string) $labId),
            ])
            ->filter(fn (string $labId, string $elementId): bool => $elementId !== '' && $labId !== '');

        SubcontractingDispatchAssignment::query()
            ->where('sample_submission_request_id', $enquiry->id)
            ->delete();

        $rows = collect();
        foreach ($normalized as $elementId => $labId) {
            $rows->push(SubcontractingDispatchAssignment::query()->create([
                'id' => (string) Str::uuid(),
                'sample_submission_request_id' => $enquiry->id,
                'analysis_element_id' => $elementId,
                'lab_id' => $labId,
                'sample_header_id' => $sampleHeaderId,
            ]));
        }

        return $rows;
    }

    /**
     * @return array<string, string> analysis_element_id => lab_id
     */
    public function labByElementIdForRequest(SampleSubmissionRequest $enquiry): array
    {
        if (! Schema::hasTable('subcontracting_dispatch_assignments')) {
            return [];
        }

        return SubcontractingDispatchAssignment::query()
            ->where('sample_submission_request_id', $enquiry->id)
            ->get(['analysis_element_id', 'lab_id'])
            ->mapWithKeys(fn (SubcontractingDispatchAssignment $row): array => [
                (string) $row->analysis_element_id => (string) $row->lab_id,
            ])
            ->all();
    }

    /**
     * @param  iterable<int, SampleSubmissionRequest|string>  $enquiries
     * @return array<string, string>
     */
    public function labByElementIdForEnquiries(iterable $enquiries): array
    {
        if (! Schema::hasTable('subcontracting_dispatch_assignments')) {
            return [];
        }

        $requestIds = collect($enquiries)
            ->map(function ($enquiry): string {
                if ($enquiry instanceof SampleSubmissionRequest) {
                    return (string) $enquiry->id;
                }

                return trim((string) $enquiry);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($requestIds === []) {
            return [];
        }

        return SubcontractingDispatchAssignment::query()
            ->whereIn('sample_submission_request_id', $requestIds)
            ->get(['analysis_element_id', 'lab_id'])
            ->mapWithKeys(fn (SubcontractingDispatchAssignment $row): array => [
                (string) $row->analysis_element_id => (string) $row->lab_id,
            ])
            ->all();
    }

    /**
     * Resolve analysis_element_id => lab_id for a sample batch.
     *
     * Prefers explicit dispatch assignment rows, then falls back to a single
     * enquiry-level dispatch lab for all subcontracted captured results.
     *
     * @return array<string, string>
     */
    public function labByElementIdForSampleHeader(string $sampleHeaderId): array
    {
        $sampleHeaderId = trim($sampleHeaderId);
        if ($sampleHeaderId === '') {
            return [];
        }

        if (Schema::hasTable('subcontracting_dispatch_assignments')) {
            $byHeader = SubcontractingDispatchAssignment::query()
                ->where('sample_header_id', $sampleHeaderId)
                ->get(['analysis_element_id', 'lab_id'])
                ->mapWithKeys(fn (SubcontractingDispatchAssignment $row): array => [
                    (string) $row->analysis_element_id => (string) $row->lab_id,
                ])
                ->all();

            if ($byHeader !== []) {
                return $byHeader;
            }

            $requestIds = SampleSubmissionRequest::query()
                ->where('sample_header_id', $sampleHeaderId)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            $byEnquiry = $this->labByElementIdForEnquiries($requestIds);
            if ($byEnquiry !== []) {
                return $byEnquiry;
            }
        }

        return $this->fallbackLabByElementFromEnquiryDispatchLabs($sampleHeaderId);
    }

    /**
     * When per-test assignment rows are missing but the enquiry stores one
     * receiving lab, map that lab onto every subcontracted captured result.
     *
     * @return array<string, string>
     */
    private function fallbackLabByElementFromEnquiryDispatchLabs(string $sampleHeaderId): array
    {
        if (! Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_lab_ids')) {
            return [];
        }

        $labIds = SampleSubmissionRequest::query()
            ->where('sample_header_id', $sampleHeaderId)
            ->pluck('subcontracting_dispatch_lab_ids')
            ->flatMap(function ($raw): array {
                return collect(explode(',', (string) $raw))
                    ->map(fn ($id) => trim((string) $id))
                    ->filter()
                    ->all();
            })
            ->unique()
            ->values()
            ->all();

        if (count($labIds) !== 1) {
            return [];
        }

        $onlyLabId = $labIds[0];
        $elementIds = CapturedResult::query()
            ->where('sample_header_id', $sampleHeaderId)
            ->where('analyte_status_contracted', 1)
            ->whereNotNull('analysis_element_id')
            ->pluck('analysis_element_id')
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($elementIds === []) {
            return [];
        }

        return array_fill_keys($elementIds, $onlyLabId);
    }

    public function syncAssignmentsToSampleHeader(SampleSubmissionRequest $enquiry, string $sampleHeaderId): void
    {
        if (Schema::hasTable('subcontracting_dispatch_assignments')) {
            SubcontractingDispatchAssignment::query()
                ->where('sample_submission_request_id', $enquiry->id)
                ->update(['sample_header_id' => $sampleHeaderId]);
        }

        $this->applyAssignmentsToCapturedResults(
            $sampleHeaderId,
            $this->labByElementIdForSampleHeader($sampleHeaderId)
        );
    }

    /**
     * @param  array<string, string>  $labByElementId
     */
    public function applyAssignmentsToCapturedResults(string $sampleHeaderId, array $labByElementId): void
    {
        if ($labByElementId === [] || ! Schema::hasColumn('captured_results', 'subcontracted_lab_id')) {
            return;
        }

        $labIds = array_values(array_unique(array_filter($labByElementId)));
        $labsById = Lab::query()
            ->whereIn('id', $labIds)
            ->get(['id', 'code', 'name'])
            ->keyBy(fn (Lab $lab) => (string) $lab->id);

        $results = CapturedResult::query()
            ->where('sample_header_id', $sampleHeaderId)
            ->whereIn('analysis_element_id', array_keys($labByElementId))
            ->get();

        foreach ($results as $result) {
            $elementId = (string) ($result->analysis_element_id ?? '');
            $labId = $labByElementId[$elementId] ?? null;
            if ($labId === null || ! $labsById->has($labId)) {
                continue;
            }

            $result->subcontracted_lab_id = $labId;
            $result->analyte_status_contracted = 1;
            $result->save();
        }
    }

    /**
     * @return list<string>
     */
    private function elementIdsFromJsonSelections(SampleSubmissionRequest $enquiry): array
    {
        $ids = [];

        $parameterIds = $enquiry->parameter_ids ?? null;
        if (is_string($parameterIds) && $parameterIds !== '') {
            $decoded = json_decode($parameterIds, true);
            $parameterIds = is_array($decoded) ? $decoded : [];
        }
        if (is_array($parameterIds)) {
            foreach ($parameterIds as $rawId) {
                $id = trim((string) $rawId);
                if ($id !== '') {
                    $ids[] = $id;
                }
            }
        }

        $sampleLines = $enquiry->sample_lines ?? null;
        if (is_string($sampleLines) && $sampleLines !== '') {
            $decoded = json_decode($sampleLines, true);
            $sampleLines = is_array($decoded) ? $decoded : [];
        }
        if (is_array($sampleLines)) {
            foreach ($sampleLines as $line) {
                if (! is_array($line)) {
                    continue;
                }
                $id = trim((string) ($line['analysis_element_id'] ?? ''));
                if ($id !== '') {
                    $ids[] = $id;
                }
            }
        }

        if ($ids === []) {
            return [];
        }

        return AnalysisElements::query()
            ->whereIn('id', $ids)
            ->where('sub_contracted', 1)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }
}
