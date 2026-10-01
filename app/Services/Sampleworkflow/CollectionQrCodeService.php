<?php

namespace App\Services\Sampleworkflow;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Models\CollectionQrCode;
use App\Models\SamplingSchedule;
use App\Models\SubmissionFormInstance;
use App\Models\TestReportDocument;
use App\SampleDetails;
use App\SampleHeader;
use App\SampleType;
use App\Services\Reports\QrCodeImageService;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Pre-collection container QR codes: one per TRF sample row (or planner sample slot), plus extras.
 *
 * TRF row N is linked to the Nth sample created for the job, matching how
 * CreateSamplesFromAcceptanceFormJob maps TRF row data onto samples.
 */
class CollectionQrCodeService
{
    public const MAX_EXTRAS_PER_REQUEST = 50;

    private const TOKEN_LENGTH = 12;

    private const STICKER_TEXT_LIMIT = 120;

    public function __construct(
        private readonly SampleTestReportDocumentService $reportDocuments,
        private readonly QrCodeImageService $qrCodeImages,
        private readonly TrfSampleFieldMapper $trfMapper,
    ) {}

    /**
     * Codes for a planner schedule; the first call creates one per expected sample.
     *
     * @return Collection<int, CollectionQrCode>
     */
    public function codesForSchedule(SamplingSchedule $schedule, ?string $userId = null): Collection
    {
        $hasCodes = CollectionQrCode::query()
            ->where('sampling_schedule_id', (string) $schedule->id)
            ->exists();

        if (! $hasCodes) {
            $expectedSamples = max(1, (int) ($schedule->number_of_samples ?? 0));
            for ($slot = 1; $slot <= $expectedSamples; $slot++) {
                $this->createCode([
                    'sampling_schedule_id' => (string) $schedule->id,
                    'slot_no' => $slot,
                ], $userId);
            }
        }

        return CollectionQrCode::query()
            ->where('sampling_schedule_id', (string) $schedule->id)
            ->where('status', '!=', CollectionQrCode::STATUS_VOID)
            ->orderBy('slot_no')
            ->orderBy('id')
            ->get();
    }

    public function addScheduleExtras(SamplingSchedule $schedule, int $quantity, ?string $userId = null): void
    {
        $nextSlot = (int) CollectionQrCode::query()
            ->where('sampling_schedule_id', (string) $schedule->id)
            ->max('slot_no') + 1;

        foreach (range(0, $this->clampQuantity($quantity) - 1) as $offset) {
            $this->createCode([
                'sampling_schedule_id' => (string) $schedule->id,
                'slot_no' => $nextSlot + $offset,
            ], $userId);
        }
    }

    /**
     * Codes for a TRF: planner codes are moved onto its rows first, then any row still
     * without a code gets a new one. Reprinting never creates duplicates.
     *
     * @return Collection<int, CollectionQrCode>
     */
    public function codesForInstance(SubmissionFormInstance $instance, ?string $userId = null): Collection
    {
        $this->assignScheduleCodesForInstance($instance);

        $rowCount = $this->trfRowCount($instance);
        $existingRows = $this->instanceRowIndexesWithCodes($instance);

        for ($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++) {
            if (in_array($rowIndex, $existingRows, true)) {
                continue;
            }

            $this->createCode([
                'submission_form_instance_id' => (string) $instance->id,
                'sampling_schedule_id' => $instance->sampling_schedule_id ?: null,
                'row_index' => $rowIndex,
                'slot_no' => $rowIndex + 1,
            ], $userId);
        }

        return CollectionQrCode::query()
            ->where('submission_form_instance_id', (string) $instance->id)
            ->where('status', '!=', CollectionQrCode::STATUS_VOID)
            ->orderByRaw('row_index IS NULL')
            ->orderBy('row_index')
            ->orderBy('slot_no')
            ->orderBy('id')
            ->get();
    }

    public function addInstanceExtras(SubmissionFormInstance $instance, int $quantity, ?string $userId = null): void
    {
        $nextSlot = max(
            $this->trfRowCount($instance),
            (int) CollectionQrCode::query()
                ->where('submission_form_instance_id', (string) $instance->id)
                ->max('slot_no'),
        ) + 1;

        foreach (range(0, $this->clampQuantity($quantity) - 1) as $offset) {
            $this->createCode([
                'submission_form_instance_id' => (string) $instance->id,
                'sampling_schedule_id' => $instance->sampling_schedule_id ?: null,
                'row_index' => null,
                'slot_no' => $nextSlot + $offset,
            ], $userId);
        }
    }

    /**
     * Move a schedule's unassigned codes onto its TRF rows, in order (oldest TRF first).
     * Leftover codes stay with the schedule and resolve to "No report generated".
     */
    public function assignScheduleCodesToInstances(SamplingSchedule $schedule): void
    {
        $freeCodes = CollectionQrCode::query()
            ->where('sampling_schedule_id', (string) $schedule->id)
            ->whereNull('submission_form_instance_id')
            ->where('status', '!=', CollectionQrCode::STATUS_VOID)
            ->orderBy('slot_no')
            ->orderBy('id')
            ->get();

        if ($freeCodes->isEmpty()) {
            return;
        }

        $instances = SubmissionFormInstance::query()
            ->where('sampling_schedule_id', (string) $schedule->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        foreach ($instances as $instance) {
            $rowCount = $this->trfRowCount($instance);
            $existingRows = $this->instanceRowIndexesWithCodes($instance);

            for ($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++) {
                if ($freeCodes->isEmpty()) {
                    return;
                }

                if (in_array($rowIndex, $existingRows, true)) {
                    continue;
                }

                $code = $freeCodes->shift();
                $code->submission_form_instance_id = (string) $instance->id;
                $code->row_index = $rowIndex;
                $code->save();
            }
        }
    }

    /**
     * Link the job's samples to the codes on the TRF rows they were created from.
     */
    public function linkSamplesForBatch(SampleHeader $batch): void
    {
        $instanceId = trim((string) ($batch->submission_form_instance_id ?? ''));
        if ($instanceId === '' || ! $this->sampleDetailsHaveRowIndex()) {
            return;
        }

        $instance = SubmissionFormInstance::query()->find($instanceId);
        if ($instance !== null) {
            $this->assignScheduleCodesForInstance($instance);
        }

        $detailsByRow = SampleDetails::query()
            ->where('sample_header_id', (string) $batch->id)
            ->whereNotNull('trf_row_index')
            ->orderBy('sample_code')
            ->get(['id', 'trf_row_index'])
            ->unique('trf_row_index')
            ->keyBy(fn (SampleDetails $detail): int => (int) $detail->trf_row_index);

        if ($detailsByRow->isEmpty()) {
            return;
        }

        CollectionQrCode::query()
            ->where('submission_form_instance_id', $instanceId)
            ->whereNotNull('row_index')
            ->whereNull('sample_detail_id')
            ->where('status', '!=', CollectionQrCode::STATUS_VOID)
            ->get()
            ->each(function (CollectionQrCode $code) use ($detailsByRow, $batch): void {
                $detail = $detailsByRow->get((int) $code->row_index);
                if ($detail === null) {
                    return;
                }

                $code->batch_id = (string) $batch->id;
                $code->sample_detail_id = (string) $detail->id;
                $code->status = CollectionQrCode::STATUS_LINKED;
                $code->linked_at = now();
                $code->save();
            });
    }

    /**
     * Latest official per-sample Test Report for each sample behind this code.
     *
     * @return Collection<int, TestReportDocument>
     */
    public function reportDocumentsFor(CollectionQrCode $code): Collection
    {
        return collect($this->resolveSampleIds($code))
            ->map(fn (string $sampleId): ?TestReportDocument => $this->reportDocuments->latestOfficialDocumentFor($sampleId))
            ->filter()
            ->values();
    }

    /**
     * @return list<string>
     */
    public function resolveSampleIds(CollectionQrCode $code): array
    {
        if (blank($code->submission_form_instance_id) && filled($code->sampling_schedule_id)) {
            $schedule = SamplingSchedule::query()->find($code->sampling_schedule_id);
            if ($schedule !== null) {
                $this->assignScheduleCodesToInstances($schedule);
                $code->refresh();
            }
        }

        $sampleIds = [];
        if (filled($code->sample_detail_id)) {
            $sampleIds[] = (string) $code->sample_detail_id;
        }

        if (filled($code->submission_form_instance_id) && $code->row_index !== null) {
            $headerIds = SampleHeader::query()
                ->where('submission_form_instance_id', (string) $code->submission_form_instance_id)
                ->pluck('id')
                ->map(static fn ($id): string => (string) $id)
                ->all();

            foreach ($headerIds as $headerId) {
                $sampleId = $this->sampleIdForRow($headerId, (int) $code->row_index);
                if ($sampleId !== null) {
                    $sampleIds[] = $sampleId;
                }
            }
        }

        return array_values(array_unique($sampleIds));
    }

    public function recordScan(CollectionQrCode $code): void
    {
        $code->increment('scan_count', 1, ['last_scanned_at' => now()]);
    }

    /**
     * @param  Collection<int, CollectionQrCode>  $codes
     * @return list<array{token: string, qr: string, client: string, collection_date: string, sample_type: string, tests: string}>
     */
    public function stickersForSchedule(SamplingSchedule $schedule, Collection $codes): array
    {
        $schedule->loadMissing('client');

        $entries = $this->scheduleEntries($schedule);
        $combined = $this->combineEntries($entries);
        $client = trim((string) ($schedule->client?->name ?? ''));
        $collectionDate = $schedule->sampling_datetime?->format('Y-m-d') ?? '';

        return $codes->values()->map(function (CollectionQrCode $code) use ($entries, $combined, $client, $collectionDate): array {
            $entry = $entries[$code->slot_no - 1] ?? (count($entries) === 1 ? $entries[0] : $combined);

            return $this->sticker($code, $client, $collectionDate, $entry['sample_type'], $entry['tests']);
        })->all();
    }

    /**
     * @param  Collection<int, CollectionQrCode>  $codes
     * @return list<array{token: string, qr: string, client: string, collection_date: string, sample_type: string, tests: string}>
     */
    public function stickersForInstance(SubmissionFormInstance $instance, Collection $codes): array
    {
        $instance->loadMissing(['crmCustomer', 'samplingSchedule.client']);

        $rowEntries = $this->instanceRowEntries($instance);
        $combined = $this->combineEntries(array_values($rowEntries));

        $client = $this->firstFilled([
            $instance->crmCustomer?->name,
            $this->instanceDisplayValue($instance, ['customer_name', 'client_name']),
            $instance->samplingSchedule?->client?->name,
        ]);

        $collectionDate = $this->firstFilled([
            $this->instanceDisplayValue($instance, ['sampling_date', 'date_of_sampling', 'collection_date', 'date_collected']),
            $instance->samplingSchedule?->sampling_datetime?->format('Y-m-d'),
        ]);

        return $codes->values()->map(function (CollectionQrCode $code) use ($rowEntries, $combined, $client, $collectionDate): array {
            $entry = $code->row_index !== null && isset($rowEntries[$code->row_index])
                ? $rowEntries[$code->row_index]
                : $combined;

            return $this->sticker($code, $client, $collectionDate, $entry['sample_type'], $entry['tests']);
        })->all();
    }

    public function clampQuantity(int $quantity): int
    {
        return max(1, min(self::MAX_EXTRAS_PER_REQUEST, $quantity));
    }

    /**
     * Number of sample rows on the TRF, counted the same way sample creation reads them.
     */
    public function trfRowCount(SubmissionFormInstance $instance): int
    {
        $instance->loadMissing('values.element');

        $formData = app(SubmissionFormValueNormalizer::class)->valuesMapFromInstance($instance);

        return max(1, count($this->trfMapper->sampleRowsFromFormData($formData)));
    }

    private function assignScheduleCodesForInstance(SubmissionFormInstance $instance): void
    {
        if (blank($instance->sampling_schedule_id)) {
            return;
        }

        $schedule = SamplingSchedule::query()->find($instance->sampling_schedule_id);
        if ($schedule !== null) {
            $this->assignScheduleCodesToInstances($schedule);
        }
    }

    /**
     * @return list<int>
     */
    private function instanceRowIndexesWithCodes(SubmissionFormInstance $instance): array
    {
        return CollectionQrCode::query()
            ->where('submission_form_instance_id', (string) $instance->id)
            ->whereNotNull('row_index')
            ->pluck('row_index')
            ->map(static fn ($rowIndex): int => (int) $rowIndex)
            ->all();
    }

    /**
     * Sample created from a TRF row. Jobs created before rows were recorded fall back to
     * sample order, which is how their TRF row data was mapped onto samples.
     */
    private function sampleIdForRow(string $headerId, int $rowIndex): ?string
    {
        $details = SampleDetails::query()
            ->where('sample_header_id', $headerId)
            ->orderBy('sample_code')
            ->orderBy('created_at')
            ->get($this->sampleDetailsHaveRowIndex() ? ['id', 'trf_row_index'] : ['id']);

        if ($details->isEmpty()) {
            return null;
        }

        $rowsAreRecorded = $this->sampleDetailsHaveRowIndex()
            && $details->contains(static fn (SampleDetails $detail): bool => $detail->trf_row_index !== null);

        $detail = $rowsAreRecorded
            ? $details->first(static fn (SampleDetails $detail): bool => $detail->trf_row_index !== null && (int) $detail->trf_row_index === $rowIndex)
            : $details->values()->get($rowIndex);

        return $detail !== null ? (string) $detail->id : null;
    }

    private function sampleDetailsHaveRowIndex(): bool
    {
        static $hasColumn = null;

        return $hasColumn ??= Schema::hasColumn('sample_details', 'trf_row_index');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createCode(array $attributes, ?string $userId): CollectionQrCode
    {
        return CollectionQrCode::query()->create(array_merge($attributes, [
            'token' => $this->uniqueToken(),
            'status' => CollectionQrCode::STATUS_UNLINKED,
            'generated_by' => $userId,
        ]));
    }

    private function uniqueToken(): string
    {
        do {
            $token = Str::random(self::TOKEN_LENGTH);
        } while (CollectionQrCode::query()->where('token', $token)->exists());

        return $token;
    }

    /**
     * @return array{token: string, qr: string, client: string, collection_date: string, sample_type: string, tests: string}
     */
    private function sticker(CollectionQrCode $code, string $client, string $collectionDate, string $sampleType, string $tests): array
    {
        return [
            'token' => (string) $code->token,
            'qr' => $this->qrCodeImages->svgDataUri($code->publicUrl(), 200),
            'client' => Str::limit($client, self::STICKER_TEXT_LIMIT),
            'collection_date' => $collectionDate,
            'sample_type' => Str::limit($sampleType, self::STICKER_TEXT_LIMIT),
            'tests' => Str::limit($tests, self::STICKER_TEXT_LIMIT),
        ];
    }

    /**
     * Sample type and tests per TRF row index.
     *
     * @return array<int, array{sample_type: string, tests: string}>
     */
    private function instanceRowEntries(SubmissionFormInstance $instance): array
    {
        $lines = app(SubmissionRequestSampleLineService::class)->linesForInstance($instance);
        $linesByRow = collect($lines)->groupBy(static fn (array $line): int => (int) ($line['row_index'] ?? 0));

        $entries = [];
        foreach ($linesByRow as $rowIndex => $rowLines) {
            $entries[(int) $rowIndex] = [
                'sample_type' => $this->joinNames($rowLines->pluck('sample_type_name')->all()),
                'tests' => $this->joinNames($rowLines->map(
                    static fn (array $line): ?string => $line['parameter_label'] ?? $line['analysis_type_name'] ?? null
                )->all()),
            ];
        }

        return $entries;
    }

    /**
     * Sample type and tests per planner sample entry (falls back to the legacy single-entry columns).
     *
     * @return list<array{sample_type: string, tests: string}>
     */
    private function scheduleEntries(SamplingSchedule $schedule): array
    {
        $rawEntries = array_values(array_filter(
            (array) ($schedule->sample_details ?? []),
            static fn ($entry): bool => is_array($entry),
        ));

        if ($rawEntries === []) {
            $rawEntries = [[
                'sample_type_id' => $schedule->sample_type_id,
                'analysis_type_id' => $schedule->analysis_type_id,
                'parameters' => (array) ($schedule->parameters ?? []),
            ]];
        }

        $sampleTypeNames = SampleType::query()
            ->whereIn('id', collect($rawEntries)->pluck('sample_type_id')->filter()->unique()->values()->all())
            ->pluck('name', 'id');
        $analysisTypeNames = AnalysisType::query()
            ->whereIn('id', collect($rawEntries)->pluck('analysis_type_id')->filter()->unique()->values()->all())
            ->pluck('name', 'id');
        $parameterNames = $this->parameterNames(
            collect($rawEntries)->flatMap(static fn (array $entry): array => (array) ($entry['parameters'] ?? []))->all()
        );

        return array_map(function (array $entry) use ($sampleTypeNames, $analysisTypeNames, $parameterNames): array {
            $parameters = array_map(
                static fn ($id): string => (string) ($parameterNames[trim((string) $id)] ?? ''),
                (array) ($entry['parameters'] ?? []),
            );

            $tests = $this->joinNames($parameters);
            if ($tests === '') {
                $tests = trim((string) ($analysisTypeNames[(string) ($entry['analysis_type_id'] ?? '')] ?? ''));
            }

            return [
                'sample_type' => trim((string) ($sampleTypeNames[(string) ($entry['sample_type_id'] ?? '')] ?? '')),
                'tests' => $tests,
            ];
        }, $rawEntries);
    }

    /**
     * @param  list<mixed>  $parameterIds
     * @return array<string, string>
     */
    private function parameterNames(array $parameterIds): array
    {
        $ids = collect($parameterIds)
            ->map(static fn ($id): string => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return [];
        }

        $names = Analyte::query()
            ->whereIn('id', $ids)
            ->pluck('name', 'id')
            ->map(static fn ($name): string => trim((string) $name))
            ->reject(static fn (string $name): bool => $name === '' || Str::isUuid($name))
            ->all();

        $missing = array_values(array_diff($ids, array_keys($names)));
        if ($missing !== []) {
            AnalysisElements::query()
                ->with('analyte')
                ->whereIn('id', $missing)
                ->get()
                ->each(function ($element) use (&$names): void {
                    $name = trim((string) ($element->analyte?->name ?? ''));
                    if ($name !== '' && ! Str::isUuid($name)) {
                        $names[(string) $element->id] = $name;
                    }
                });
        }

        return $names;
    }

    /**
     * @param  list<array{sample_type: string, tests: string}>  $entries
     * @return array{sample_type: string, tests: string}
     */
    private function combineEntries(array $entries): array
    {
        return [
            'sample_type' => $this->joinNames(array_column($entries, 'sample_type')),
            'tests' => $this->joinNames(array_column($entries, 'tests')),
        ];
    }

    /**
     * @param  list<mixed>  $names
     */
    private function joinNames(array $names): string
    {
        return collect($names)
            ->map(static fn ($name): string => trim(strip_tags((string) $name)))
            ->reject(static fn (string $name): bool => $name === '' || $name === '-' || Str::isUuid($name))
            ->unique()
            ->implode(', ');
    }

    /**
     * @param  list<string>  $elementNames
     */
    private function instanceDisplayValue(SubmissionFormInstance $instance, array $elementNames): ?string
    {
        foreach ($elementNames as $elementName) {
            $display = $instance->resolveDisplayValueByName($elementName);
            $value = is_scalar($display) ? trim(strip_tags((string) $display)) : '';

            if ($value !== '' && strcasecmp($value, 'N/A') !== 0 && ! Str::isUuid($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  list<string|null>  $values
     */
    private function firstFilled(array $values): string
    {
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}
