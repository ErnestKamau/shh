<?php

namespace App\Services\GroupedWorksheets;

use App\AnalysisType;
use App\Analyte;
use App\CapturedResult;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetResultsCaptureDraft;
use App\Models\GroupedWorksheets\GroupedWorksheetResultsCapturePost;
use App\Models\GroupedWorksheets\GroupedWorksheetResultsCaptureSampleDraft;
use App\Models\TrackSampleResult;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\Sampleworkflow\LabSectionResultAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GroupedResultsCaptureService
{
    /**
     * @return array{is_posted: bool, posted_at: \Illuminate\Support\Carbon|null, posted_by_name: string|null}
     */
    public function getPostingStatus(SampleHeader $batch, GroupedWorksheetHolder $holder): array
    {
        $post = GroupedWorksheetResultsCapturePost::query()
            ->with('postedBy')
            ->where('sample_header_id', $batch->id)
            ->where('grouped_worksheet_holder_id', $holder->id)
            ->first();

        if (! $post) {
            return [
                'is_posted' => false,
                'posted_at' => null,
                'posted_by_name' => null,
            ];
        }

        return [
            'is_posted' => true,
            'posted_at' => $post->posted_at,
            'posted_by_name' => $post->postedBy?->name,
        ];
    }

    /**
     * @return Collection<int, CapturedResult>
     */
    public function loadCapturedResults(SampleHeader $batch, GroupedWorksheetHolder $holder): Collection
    {
        $query = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('grouped_worksheet_holder_id', $holder->id)
            ->where('has_grouped_worksheet', true)
            ->where('has_no_result_capture', false);

        app(LabSectionResultAccess::class)->scopeVisibleCapturedResults($query, Auth::user());

        return $query
            ->orderBy('analysis_type_order')
            ->orderBy('parameters_order')
            ->orderBy('sample_detail_code')
            ->get();
    }

    /**
     * @return array{
     *     parameters: array<int, array{row_key: string, label: string, analysis_type_name: string|null}>,
     *     samples: array<int, array{sample_detail_code: string, sample_detail_id: string|null}>,
     *     cells: array<string, array<string, array{captured_result_id: string, result: string|null, reporting_symbol: string|null, remark: string|null}>>,
     *     sample_comments: array<string, array{header_body: string, main_body: string, notes_body: string}>
     * }
     */
    public function loadMatrixWithDrafts(SampleHeader $batch, GroupedWorksheetHolder $holder): array
    {
        $capturedResults = $this->loadCapturedResults($batch, $holder);
        $matrix = $this->buildMatrix($capturedResults);

        if ($matrix['cells'] === []) {
            $matrix['sample_comments'] = [];

            return $matrix;
        }

        $isPosted = $this->getPostingStatus($batch, $holder)['is_posted'];

        if (! $isPosted) {
            $cellDrafts = GroupedWorksheetResultsCaptureDraft::query()
                ->where('sample_header_id', $batch->id)
                ->where('grouped_worksheet_holder_id', $holder->id)
                ->get()
                ->keyBy('captured_result_id');

            $stagedByCapturedResultId = $this->latestStagedMethodSequenceResults(
                collect($matrix['cells'])->flatMap(fn (array $row) => collect($row)->pluck('captured_result_id'))->unique()->filter()->values()
            );

            foreach ($matrix['cells'] as $rowKey => $rowCells) {
                foreach ($rowCells as $sampleCode => $cell) {
                    $capturedResultId = $cell['captured_result_id'];
                    $draft = $cellDrafts->get($capturedResultId);

                    if ($draft) {
                        $matrix['cells'][$rowKey][$sampleCode]['result'] = $draft->result;
                        $matrix['cells'][$rowKey][$sampleCode]['reporting_symbol'] = $draft->reporting_symbol;
                        $matrix['cells'][$rowKey][$sampleCode]['remark'] = $draft->remark;

                        continue;
                    }

                    if (! $this->cellResultNeedsStagedSeed($cell['result'] ?? null)) {
                        continue;
                    }

                    $staged = $stagedByCapturedResultId->get($capturedResultId);
                    if (! $staged) {
                        continue;
                    }

                    $matrix['cells'][$rowKey][$sampleCode]['result'] = $staged->result;
                    $matrix['cells'][$rowKey][$sampleCode]['reporting_symbol'] = $staged->reporting_symbol;
                    $matrix['cells'][$rowKey][$sampleCode]['remark'] = $staged->remark;
                }
            }
        }

        $sampleDetailIds = collect($matrix['samples'])
            ->pluck('sample_detail_id')
            ->filter()
            ->values();

        $sampleDrafts = GroupedWorksheetResultsCaptureSampleDraft::query()
            ->where('sample_header_id', $batch->id)
            ->where('grouped_worksheet_holder_id', $holder->id)
            ->whereIn('sample_detail_id', $sampleDetailIds)
            ->get()
            ->keyBy('sample_detail_id');

        $sampleDetails = SampleDetails::query()
            ->whereIn('id', $sampleDetailIds)
            ->get()
            ->keyBy('id');

        $sampleComments = [];
        foreach ($matrix['samples'] as $sample) {
            $sampleDetailId = $sample['sample_detail_id'];
            if (! $sampleDetailId) {
                continue;
            }

            $detail = $sampleDetails->get($sampleDetailId);
            $fromSample = [
                'header_body' => (string) ($detail?->header_body ?? ''),
                'main_body' => (string) ($detail?->main_body ?? ''),
                'notes_body' => (string) ($detail?->notes_body ?? ''),
            ];

            if ($isPosted) {
                $sampleComments[$sampleDetailId] = $fromSample;

                continue;
            }

            $draft = $sampleDrafts->get($sampleDetailId);
            if ($draft && $this->sampleCommentDraftHasContent($draft)) {
                $sampleComments[$sampleDetailId] = [
                    'header_body' => (string) ($draft->header_body ?? ''),
                    'main_body' => (string) ($draft->main_body ?? ''),
                    'notes_body' => (string) ($draft->notes_body ?? ''),
                ];

                continue;
            }

            $sampleComments[$sampleDetailId] = $fromSample;
        }

        $matrix['sample_comments'] = $sampleComments;

        return $matrix;
    }

    /**
     * @param  Collection<int, CapturedResult>  $capturedResults
     * @return array{
     *     parameters: array<int, array{row_key: string, label: string, analysis_type_name: string|null}>,
     *     samples: array<int, array{sample_detail_code: string, sample_detail_id: string|null}>,
     *     cells: array<string, array<string, array{captured_result_id: string, result: string|null, reporting_symbol: string|null, remark: string|null}>>
     * }
     */
    public function buildMatrix(Collection $capturedResults): array
    {
        if ($capturedResults->isEmpty()) {
            return [
                'parameters' => [],
                'samples' => [],
                'cells' => [],
            ];
        }

        $analysisTypeIds = $capturedResults->pluck('analysis_type_id')->unique()->filter()->values();
        $analysisTypes = AnalysisType::query()
            ->whereIn('id', $analysisTypeIds)
            ->get()
            ->keyBy('id');

        $analyteIds = $capturedResults->pluck('analyte_id')->unique()->filter()->values();
        $analytes = Analyte::query()
            ->whereIn('id', $analyteIds)
            ->get()
            ->keyBy('id');

        $rowMeta = [];
        $samples = [];
        $cells = [];

        foreach ($capturedResults as $result) {
            $sampleCode = (string) ($result->sample_detail_code ?? '');
            if ($sampleCode === '') {
                continue;
            }

            $analysisType = $analysisTypes->get((string) $result->analysis_type_id);
            $analysisTypeName = $analysisType?->name ?? $analysisType?->code ?? null;
            $analyte = $analytes->get((string) $result->analyte_id);
            $analyteName = $this->resolveAnalyteName($result, $analyte);
            $rowKey = $this->rowKey((string) $result->analysis_type_id, (string) $result->analyte_id);

            if (! isset($rowMeta[$rowKey])) {
                $label = $analyteName;
                if ($analysisTypeName && $this->analyteAppearsUnderMultipleTypes($capturedResults, (string) $result->analyte_id)) {
                    $label = $analyteName.' ('.$analysisTypeName.')';
                }

                $rowMeta[$rowKey] = [
                    'row_key' => $rowKey,
                    'label' => $label,
                    'analysis_type_name' => $analysisTypeName,
                    'sort_order' => ($result->analysis_type_order ?? 0) * 10000 + ($result->parameters_order ?? 0),
                ];
            }

            if (! isset($samples[$sampleCode])) {
                $samples[$sampleCode] = [
                    'sample_detail_code' => $sampleCode,
                    'sample_detail_id' => $result->sample_detail_id ? (string) $result->sample_detail_id : null,
                ];
            }

            $cells[$rowKey][$sampleCode] = [
                'captured_result_id' => (string) $result->id,
                'result' => $result->result,
                'reporting_symbol' => $result->result_reporting_symbol,
                'remark' => $result->remark,
            ];
        }

        uasort($rowMeta, fn (array $a, array $b) => $a['sort_order'] <=> $b['sort_order']);
        ksort($samples, SORT_NATURAL);

        $parameters = array_values(array_map(
            fn (array $row) => [
                'row_key' => $row['row_key'],
                'label' => $row['label'],
                'analysis_type_name' => $row['analysis_type_name'],
            ],
            $rowMeta
        ));

        return [
            'parameters' => $parameters,
            'samples' => array_values($samples),
            'cells' => $cells,
        ];
    }

    /**
     * @param  array<string, array{result?: string|null, reporting_symbol?: string|null, remark?: string|null}>  $cellPayload  keyed by captured_result_id
     * @param  array<string, array{header_body?: string|null, main_body?: string|null, notes_body?: string|null}>  $sampleCommentsPayload  keyed by sample_detail_id
     */
    public function saveDrafts(
        SampleHeader $batch,
        GroupedWorksheetHolder $holder,
        array $cellPayload,
        array $sampleCommentsPayload
    ): void {
        $this->assertUserCanEditCellPayload($batch, $holder, $cellPayload);
        $this->saveDraftsWithoutAuthCheck($batch, $holder, $cellPayload, $sampleCommentsPayload);
    }

    /**
     * @param  array<string, array{result?: string|null, reporting_symbol?: string|null, remark?: string|null}>  $cellPayload
     * @param  array<string, array{header_body?: string|null, main_body?: string|null, notes_body?: string|null}>  $sampleCommentsPayload
     */
    protected function saveDraftsWithoutAuthCheck(
        SampleHeader $batch,
        GroupedWorksheetHolder $holder,
        array $cellPayload,
        array $sampleCommentsPayload
    ): void {
        DB::transaction(function () use ($batch, $holder, $cellPayload, $sampleCommentsPayload): void {
            foreach ($cellPayload as $capturedResultId => $data) {
                GroupedWorksheetResultsCaptureDraft::query()->updateOrCreate(
                    [
                        'sample_header_id' => $batch->id,
                        'grouped_worksheet_holder_id' => $holder->id,
                        'captured_result_id' => (string) $capturedResultId,
                    ],
                    [
                        'result' => $data['result'] ?? null,
                        'reporting_symbol' => $data['reporting_symbol'] ?? null,
                        'remark' => $data['remark'] ?? null,
                    ]
                );
            }

            foreach ($sampleCommentsPayload as $sampleDetailId => $comments) {
                GroupedWorksheetResultsCaptureSampleDraft::query()->updateOrCreate(
                    [
                        'sample_header_id' => $batch->id,
                        'grouped_worksheet_holder_id' => $holder->id,
                        'sample_detail_id' => (string) $sampleDetailId,
                    ],
                    [
                        'header_body' => $comments['header_body'] ?? null,
                        'main_body' => $comments['main_body'] ?? null,
                        'notes_body' => $comments['notes_body'] ?? null,
                    ]
                );
            }
        });
    }

    /**
     * @param  array<string, array{result?: string|null, reporting_symbol?: string|null, remark?: string|null}>  $cellPayload
     */
    protected function assertUserCanEditCellPayload(
        SampleHeader $batch,
        GroupedWorksheetHolder $holder,
        array $cellPayload
    ): void {
        $access = app(LabSectionResultAccess::class);
        $user = Auth::user();

        if (! $access->hasLabSectionAssignment($user)) {
            throw new InvalidArgumentException($access->denyEditMessage($user));
        }

        $allowedIds = $this->loadCapturedResults($batch, $holder)->pluck('id')->map(fn ($id) => (string) $id)->all();

        foreach (array_keys($cellPayload) as $capturedResultId) {
            $captured = CapturedResult::query()->find($capturedResultId);
            if (! $captured) {
                continue;
            }

            if (! in_array((string) $capturedResultId, $allowedIds, true)
                || ! $access->canEditCapturedResult($user, $captured)
            ) {
                throw new InvalidArgumentException($access->denyEditMessage($user));
            }
        }
    }

    /**
     * @param  array<string, array{result?: string|null, reporting_symbol?: string|null, remark?: string|null}>  $cellPayload  keyed by captured_result_id
     * @param  array<string, array{header_body?: string|null, main_body?: string|null, notes_body?: string|null}>  $sampleCommentsPayload  keyed by sample_detail_id
     */
    public function postResults(
        SampleHeader $batch,
        GroupedWorksheetHolder $holder,
        array $cellPayload,
        array $sampleCommentsPayload
    ): void {
        $this->assertUserCanEditCellPayload($batch, $holder, $cellPayload);

        $userId = Auth::id();

        DB::transaction(function () use ($batch, $holder, $cellPayload, $sampleCommentsPayload, $userId): void {
            $this->saveDraftsWithoutAuthCheck($batch, $holder, $cellPayload, $sampleCommentsPayload);

            foreach ($cellPayload as $capturedResultId => $data) {
                $captured = CapturedResult::query()->find($capturedResultId);
                if (! $captured) {
                    continue;
                }

                $attributes = [
                    'result' => $data['result'] ?? null,
                    'result_reporting_symbol' => $data['reporting_symbol'] ?? null,
                    'worksheet_posted' => true,
                ];

                if (array_key_exists('remark', $data) && $data['remark'] !== null && $data['remark'] !== '') {
                    $attributes['remark'] = $data['remark'];
                }

                $analystId = $userId ? (string) $userId : null;
                app(\App\Services\Sampleworkflow\CapturedResultCaptureService::class)
                    ->applyOnSave($captured, $attributes, $analystId);

                if ($captured->analysisElement && ! $captured->equipment_id && $captured->analysisElement->equipment_id) {
                    $captured->equipment_id = $captured->analysisElement->equipment_id;
                    $captured->save();
                }
            }

            foreach ($sampleCommentsPayload as $sampleDetailId => $comments) {
                $sample = SampleDetails::query()->find($sampleDetailId);
                if (! $sample) {
                    continue;
                }

                // Statement of conformity (header_body) is always auto-managed.
                $sample->main_body = $comments['main_body'] ?? null;
                $sample->notes_body = $comments['notes_body'] ?? null;
                $sample->save();

                GroupedWorksheetResultsCaptureSampleDraft::query()->updateOrCreate(
                    [
                        'sample_header_id' => $batch->id,
                        'grouped_worksheet_holder_id' => $holder->id,
                        'sample_detail_id' => (string) $sampleDetailId,
                    ],
                    [
                        'header_body' => $sample->header_body,
                        'main_body' => $sample->main_body,
                        'notes_body' => $sample->notes_body,
                    ]
                );
            }

            $affectedSampleIds = collect($cellPayload)
                ->keys()
                ->map(function ($capturedResultId) {
                    return CapturedResult::query()->whereKey($capturedResultId)->value('sample_detail_id');
                })
                ->merge(array_keys($sampleCommentsPayload))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($affectedSampleIds !== []) {
                app(\App\Services\Sampleworkflow\StatementOfConformityService::class)
                    ->ensureForSampleIds($affectedSampleIds, $batch);
            }

            // Refresh draft header_body after auto SoC for samples we touched via comments.
            foreach (array_keys($sampleCommentsPayload) as $sampleDetailId) {
                $sample = SampleDetails::query()->find($sampleDetailId);
                if (! $sample) {
                    continue;
                }

                GroupedWorksheetResultsCaptureSampleDraft::query()->updateOrCreate(
                    [
                        'sample_header_id' => $batch->id,
                        'grouped_worksheet_holder_id' => $holder->id,
                        'sample_detail_id' => (string) $sampleDetailId,
                    ],
                    [
                        'header_body' => $sample->header_body,
                        'main_body' => $sample->main_body,
                        'notes_body' => $sample->notes_body,
                    ]
                );
            }

            $postedQuery = CapturedResult::query()
                ->where('sample_header_id', $batch->id)
                ->where('grouped_worksheet_holder_id', $holder->id)
                ->where('has_grouped_worksheet', true)
                ->where('has_no_result_capture', false);
            app(LabSectionResultAccess::class)->scopeVisibleCapturedResults($postedQuery, Auth::user());
            $postedQuery->update(['worksheet_posted' => true]);

            GroupedWorksheetResultsCapturePost::query()->updateOrCreate(
                [
                    'sample_header_id' => $batch->id,
                    'grouped_worksheet_holder_id' => $holder->id,
                ],
                [
                    'posted_at' => now(),
                    'posted_by_user_id' => $userId ? (string) $userId : null,
                ]
            );
        });
    }

    /**
     * @deprecated Use saveDrafts() or postResults() instead.
     *
     * @param  array<string, array{captured_result_id: string, result?: string|null, reporting_symbol?: string|null}>  $payload  keyed by captured_result_id
     */
    public function saveCellResults(array $payload): void
    {
        if ($payload === []) {
            return;
        }

        $access = app(LabSectionResultAccess::class);
        $user = Auth::user();
        if (! $access->hasLabSectionAssignment($user)) {
            throw new InvalidArgumentException($access->denyEditMessage($user));
        }

        $userId = Auth::id();

        DB::transaction(function () use ($payload, $userId, $access, $user): void {
            foreach ($payload as $capturedResultId => $data) {
                $captured = CapturedResult::query()->find($capturedResultId);
                if (! $captured) {
                    continue;
                }

                if (! $access->canEditCapturedResult($user, $captured)) {
                    throw new InvalidArgumentException($access->denyEditMessage($user));
                }

                $captured->result = $data['result'] ?? null;
                $captured->result_reporting_symbol = $data['reporting_symbol'] ?? null;
                $captured->assignAnalyst($userId ? (string) $userId : null);
                $captured->save();
            }
        });
    }

    protected function sampleCommentDraftHasContent(GroupedWorksheetResultsCaptureSampleDraft $draft): bool
    {
        foreach (['header_body', 'main_body', 'notes_body'] as $field) {
            $value = $draft->{$field};
            if ($value !== null && trim(strip_tags((string) $value)) !== '') {
                return true;
            }
        }

        return false;
    }

    protected function cellResultNeedsStagedSeed(mixed $result): bool
    {
        if ($result === null) {
            return true;
        }

        $normalized = trim((string) $result);

        return $normalized === '' || strcasecmp($normalized, 'No attachment') === 0;
    }

    /**
     * Latest TrackSampleResult values from final method-sequence stages, keyed by captured_result_id.
     *
     * @param  Collection<int, string>  $capturedResultIds
     * @return Collection<string, TrackSampleResult>
     */
    protected function latestStagedMethodSequenceResults(Collection $capturedResultIds): Collection
    {
        if ($capturedResultIds->isEmpty()) {
            return collect();
        }

        return TrackSampleResult::query()
            ->whereIn('captured_result_id', $capturedResultIds->all())
            ->whereHas('track.testStage', function ($query) {
                $query->where('is_result_stage', true)
                    ->where('is_end_stage', true);
            })
            ->orderByDesc('recorded_at')
            ->orderByDesc('created_at')
            ->get()
            ->unique('captured_result_id')
            ->keyBy(fn (TrackSampleResult $row) => (string) $row->captured_result_id);
    }

    public function hasPostedResults(SampleHeader $batch, GroupedWorksheetHolder $holder): bool
    {
        return GroupedWorksheetResultsCapturePost::query()
            ->where('sample_header_id', $batch->id)
            ->where('grouped_worksheet_holder_id', $holder->id)
            ->exists();
    }

    protected function rowKey(string $analysisTypeId, string $analyteId): string
    {
        return $analysisTypeId.'|'.$analyteId;
    }

    protected function analyteAppearsUnderMultipleTypes(Collection $capturedResults, string $analyteId): bool
    {
        return $capturedResults
            ->where('analyte_id', $analyteId)
            ->pluck('analysis_type_id')
            ->unique()
            ->count() > 1;
    }

    protected function resolveAnalyteName(CapturedResult $result, ?Analyte $analyte): string
    {
        $analyteName = $analyte?->name;
        $analyteCode = $result->analyte_code;

        if (! $analyteName && $analyte) {
            $analyteName = $analyte->code;
        }

        if (! $analyteName) {
            $analyteName = is_string($analyteCode) && ! str_starts_with($analyteCode, 'eyJ')
                ? $analyteCode
                : '—';
        }

        return $analyteName;
    }
}
