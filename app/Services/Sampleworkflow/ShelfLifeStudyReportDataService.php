<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;
use App\Models\SampleShelfLifeCondition;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\ResultRemarkService;

class ShelfLifeStudyReportDataService
{
    public function __construct(
        private readonly TestRequestReportDataService $testRequestReportDataService,
        private readonly ResultRemarkService $resultRemarkService,
    ) {}

    /**
     * @param  array{logoPublicUrlFallback?: bool}  $options
     * @return array<string, mixed>
     */
    public function build(SampleHeader $batch, string $reportNumber, array $options = []): array
    {
        $reportData = $this->testRequestReportDataService->build($batch, $reportNumber, $options);

        $sampleDetails = SampleDetails::query()
            ->where('sample_header_id', $batch->id)
            ->with(['shelfLifeCondition', 'product'])
            ->orderBy('sample_code')
            ->get()
            ->keyBy('id');

        $conditionsBySampleDetailId = [];
        foreach ($sampleDetails as $detail) {
            $conditionsBySampleDetailId[(string) $detail->id] = $this->conditionPayload($detail->shelfLifeCondition);
        }

        $samples = $reportData['samples'] ?? collect();
        $baseContexts = $reportData['sampleDetailContexts'] ?? [];
        $shelfLifeContexts = [];

        foreach ($samples->values() as $index => $sample) {
            $detail = $sampleDetails->get($sample->id);
            $condition = $detail?->shelfLifeCondition;
            $baseRows = $baseContexts[$index]['rows'] ?? [];
            $valueMap = $this->valueMapFromRows($baseRows);

            $manufacturer = $this->firstNonEmpty(
                $detail?->product?->name ?? null,
            ) ?? '-';

            $sampleDeliveredBy = $this->firstNonEmpty(
                $valueMap['sampled_by'] ?? null,
                $batch->submit_by ?? null,
            ) ?? '-';

            $shelfLifeContexts[] = [
                'rows' => $this->buildShelfLifeDetailRows(
                    $valueMap,
                    $manufacturer,
                    $sampleDeliveredBy,
                    $condition,
                ),
                'lab_section' => (string) ($baseContexts[$index]['lab_section'] ?? ($batch->getLabSectionsNames() ?: 'Laboratory')),
                'conducted_by' => (string) ($baseContexts[$index]['conducted_by'] ?? '-'),
                'conditions' => $conditionsBySampleDetailId[(string) $sample->id]
                    ?? $this->conditionPayload(null),
                'sample_detail_id' => (string) $sample->id,
            ];
        }

        $reportData['sampleDetailContexts'] = $shelfLifeContexts;
        $reportData['shelfLifeConditionsBySampleDetailId'] = $conditionsBySampleDetailId;
        $reportData['isShelfLifeReport'] = true;

        return $reportData;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CapturedResult>|iterable<int, CapturedResult>  $capturedResults
     * @return array<string, string>
     */
    public function buildConclusionIndex($capturedResults): array
    {
        $index = [];

        foreach ($capturedResults as $capturedResult) {
            $remark = trim((string) ($capturedResult->remark ?? ''));
            if ($remark === '') {
                $remark = (string) $this->resultRemarkService->calculateRemark(
                    $capturedResult,
                    $capturedResult->result !== null ? (string) $capturedResult->result : null,
                );
            }

            $upper = strtoupper($remark);
            $index[(string) $capturedResult->id] = match ($upper) {
                'PASS' => 'Pass',
                'FAIL' => 'Fail',
                '-', '' => '—',
                default => $remark !== '' ? ucfirst(strtolower($remark)) : '—',
            };
        }

        return $index;
    }

    /**
     * @return array{
     *     study_type: string,
     *     accelerated_temperature: string,
     *     study_duration: string,
     *     relative_humidity: string,
     *     evaluation_type: string,
     *     sampling_frequency: string,
     *     declared_shelf_life: string,
     *     storage_condition: string,
     *     notes: string
     * }
     */
    private function conditionPayload(?SampleShelfLifeCondition $condition): array
    {
        return [
            'study_type' => $this->display($condition?->study_type),
            'accelerated_temperature' => $this->display($condition?->accelerated_temperature),
            'study_duration' => $condition
                ? $this->display($condition->studyDurationDisplay() === '—' ? null : $condition->studyDurationDisplay())
                : '—',
            'relative_humidity' => $this->display($condition?->relative_humidity),
            'evaluation_type' => $this->display($condition?->evaluation_type),
            'sampling_frequency' => $this->display($condition?->sampling_frequency),
            'declared_shelf_life' => $this->display($condition?->declared_shelf_life),
            'storage_condition' => $this->display($condition?->storage_condition),
            'notes' => $this->display($condition?->notes, ''),
        ];
    }

    /**
     * @param  list<array{left: array{label: string, value: string}, right: array{label: string, value: string}}>  $rows
     * @return array<string, string>
     */
    private function valueMapFromRows(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            if (isset($row['left']['label'])) {
                $map[(string) $row['left']['label']] = (string) ($row['left']['value'] ?? '-');
            }
            if (isset($row['right']['label'])) {
                $map[(string) $row['right']['label']] = (string) ($row['right']['value'] ?? '-');
            }
        }

        return $map;
    }

    /**
     * @param  array<string, string>  $valueMap
     * @return list<array{left: array{label: string, value: string, emphasize?: bool}, right: array{label: string, value: string, emphasize?: bool}}>
     */
    private function buildShelfLifeDetailRows(
        array $valueMap,
        string $manufacturer,
        string $sampleDeliveredBy,
        ?SampleShelfLifeCondition $condition,
    ): array {
        $declared = $this->display($condition?->declared_shelf_life);
        $storage = $this->display($condition?->storage_condition);

        return [
            [
                'left' => ['label' => 'job_no', 'value' => $valueMap['job_no'] ?? '-'],
                'right' => ['label' => 'sample_no', 'value' => $valueMap['sample_no'] ?? '-'],
            ],
            [
                'left' => ['label' => 'sample_description', 'value' => $valueMap['sample_description'] ?? '-'],
                'right' => ['label' => 'report_no', 'value' => $valueMap['report_no'] ?? '-', 'emphasize' => true],
            ],
            [
                'left' => ['label' => 'sample_type', 'value' => $valueMap['sample_type'] ?? '-'],
                'right' => ['label' => 'date_received', 'value' => $valueMap['date_received'] ?? '-'],
            ],
            [
                'left' => ['label' => 'manufacturer', 'value' => $manufacturer],
                'right' => ['label' => 'analysis_start_date', 'value' => $valueMap['analysis_start_date'] ?? '-'],
            ],
            [
                'left' => ['label' => 'lot_no', 'value' => $valueMap['lot_no'] ?? '-'],
                'right' => ['label' => 'analysis_end_date', 'value' => $valueMap['analysis_end_date'] ?? '-'],
            ],
            [
                'left' => ['label' => 'production_date', 'value' => $valueMap['production_date'] ?? '-'],
                'right' => ['label' => 'reporting_date', 'value' => $valueMap['reporting_date'] ?? '-'],
            ],
            [
                'left' => ['label' => 'weight', 'value' => $valueMap['weight'] ?? '-'],
                'right' => ['label' => 'container_type', 'value' => $valueMap['container_type'] ?? '-'],
            ],
            [
                'left' => ['label' => 'sampled_by', 'value' => $valueMap['sampled_by'] ?? '-'],
                'right' => ['label' => 'origin_country', 'value' => $valueMap['origin_country'] ?? '-'],
            ],
            [
                'left' => ['label' => 'sample_point', 'value' => $valueMap['sample_point'] ?? '-'],
                'right' => ['label' => 'declared_shelf_life', 'value' => $declared],
            ],
            [
                'left' => ['label' => 'sampling_location', 'value' => $valueMap['sampling_location'] ?? '-'],
                'right' => ['label' => 'sampling_method', 'value' => $valueMap['sampling_method'] ?? '-'],
            ],
            [
                'left' => ['label' => 'sample_condition', 'value' => $valueMap['sample_condition'] ?? '-'],
                'right' => ['label' => 'storage_condition', 'value' => $storage],
            ],
            [
                'left' => ['label' => 'sample_temperature', 'value' => $valueMap['sample_temperature'] ?? '-'],
                'right' => ['label' => 'sample_delivered_by', 'value' => $sampleDeliveredBy],
            ],
            [
                'left' => ['label' => 'transport_condition', 'value' => $valueMap['transport_condition'] ?? '-'],
                'right' => ['label' => 'additional_notes', 'value' => $valueMap['additional_notes'] ?? ''],
            ],
        ];
    }

    private function display(?string $value, string $empty = '—'): string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : $empty;
    }

    private function firstNonEmpty(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }
            $trimmed = trim((string) $value);
            if ($trimmed !== '' && $trimmed !== '-') {
                return $trimmed;
            }
        }

        return null;
    }
}
