<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\SampleAnalysisStage;
use App\SampleHeader;
use App\User;
use Illuminate\Support\Collection;

final class RequestTestExportDataService
{
    public const UNASSIGNED_SECTION_ID = '__unassigned__';

    public function __construct(
        private readonly SampleIntegrityCheckService $integrityCheckService,
    ) {}

    /**
     * Build export payload from Integrity Check rows (current UI state).
     *
     * @param  list<array<string, mixed>>  $testRows
     * @param  array<string, string>  $labSectionNames
     * @param  array<string, string>  $analystNamesById
     * @return array{
     *     title: string,
     *     reference: string,
     *     context: string,
     *     include_result_column: bool,
     *     request_info: array{fields: list<array{label: string, value: string, name: ?string}>, remarks: ?string},
     *     sections: list<array{lab_section_id: string, lab_section_name: string, samples: list<array<string, mixed>>}>,
     *     flat_rows: list<array<string, mixed>>
     * }
     */
    public function buildFromIntegrityRows(
        SubmissionFormInstance $instance,
        ?SampleSubmissionRequest $enquiry,
        array $testRows,
        array $labSectionNames = [],
        array $analystNamesById = [],
    ): array {
        $requestInfo = $this->integrityCheckService->requestInfoCard($instance, $enquiry);
        $catalog = $this->integrityCheckService->integrityPdfCatalog($instance, $enquiry);
        $reference = trim((string) ($enquiry?->formatted_number
            ?? $enquiry?->request_number
            ?? $instance->code
            ?? $instance->id));

        $flatRows = [];
        foreach ($testRows as $row) {
            $sectionIds = array_values(array_filter(array_map(
                'strval',
                is_array($row['lab_section_ids'] ?? null) ? $row['lab_section_ids'] : []
            )));
            if ($sectionIds === []) {
                $sectionIds = [self::UNASSIGNED_SECTION_ID];
            }

            $analystsBySection = is_array($row['analysts_by_lab_section'] ?? null)
                ? $row['analysts_by_lab_section']
                : [];

            foreach ($sectionIds as $sectionId) {
                $analystIds = array_values(array_filter(array_map(
                    'strval',
                    is_array($analystsBySection[$sectionId] ?? null) ? $analystsBySection[$sectionId] : []
                )));
                $analystNames = [];
                foreach ($analystIds as $analystId) {
                    $name = trim((string) ($analystNamesById[$analystId] ?? ''));
                    if ($name !== '') {
                        $analystNames[] = $name;
                    }
                }

                $flatRows[] = [
                    'captured_result_id' => null,
                    'batch_id' => null,
                    'batch_code' => '',
                    'row_key' => (string) ($row['row_key'] ?? ''),
                    'element_id' => (string) ($row['element_id'] ?? ''),
                    'lab_section_id' => $sectionId,
                    'lab_section_name' => $sectionId === self::UNASSIGNED_SECTION_ID
                        ? 'Unassigned'
                        : (string) ($labSectionNames[$sectionId] ?? 'Lab section'),
                    'sample_label' => (string) ($row['sample_label'] ?? 'Sample'),
                    'sample_code' => (string) ($row['sample_label'] ?? 'Sample'),
                    'test_label' => (string) ($row['test_label'] ?? 'Test'),
                    'analysts' => $analystNames !== [] ? implode(', ', $analystNames) : 'None assigned',
                    'subcontracted' => (bool) ($row['subcontracted'] ?? false),
                    'result' => '',
                ];
            }
        }

        return $this->assemblePayload(
            title: 'Sample Integrity & Acceptance Check',
            reference: $reference !== '' ? $reference : (string) $instance->id,
            context: 'integrity',
            includeResultColumn: false,
            requestInfo: $requestInfo,
            flatRows: $flatRows,
            catalog: $catalog,
        );
    }

    /**
     * Build export payload from a Samples In Lab (or later) batch.
     *
     * @return array{
     *     title: string,
     *     reference: string,
     *     context: string,
     *     include_result_column: bool,
     *     request_info: array{fields: list<array{label: string, value: string, name: ?string}>, remarks: ?string},
     *     sections: list<array{lab_section_id: string, lab_section_name: string, samples: list<array<string, mixed>>}>,
     *     flat_rows: list<array<string, mixed>>
     * }
     */
    public function buildFromBatch(SampleHeader $batch, bool $includeResultColumn = true): array
    {
        $batch->loadMissing([
            'client',
            'sample_type',
            'sampleSubmissionRequest.customer',
            'sampleSubmissionRequest.contact',
            'submissionFormInstance.submissionForm',
            'submissionFormInstance.crmCustomer',
            'submissionFormInstance.values.element',
        ]);

        $requestInfo = $this->requestInfoForBatch($batch);

        $captured = CapturedResult::query()
            ->with([
                'sample:id,sample_code,customer_sample_id,file_no,barcode',
                'labSection:id,name',
                'my_analyte:id,name,code',
                'analysis_type:id,name',
                'operator:id,name',
            ])
            ->where('sample_header_id', $batch->id)
            ->orderBy('lab_section_id')
            ->orderBy('sample_detail_id')
            ->get();

        $analystNames = $this->resolveAnalystNames($captured);

        $flatRows = [];
        foreach ($captured as $row) {
            $sectionId = trim((string) ($row->lab_section_id ?? ''));
            if ($sectionId === '') {
                $sectionId = self::UNASSIGNED_SECTION_ID;
            }

            $sampleLabel = trim((string) ($row->sample?->customer_sample_id
                ?? $row->sample?->file_no
                ?? $row->sample?->barcode
                ?? $row->sample?->sample_code
                ?? 'Sample'));
            $sampleCode = trim((string) ($row->sample?->sample_code ?? $sampleLabel));

            $testLabel = trim((string) ($row->my_analyte?->name ?? ''));
            if ($testLabel === '') {
                $testLabel = trim((string) ($row->analyte_code ?? ''));
            }
            if ($testLabel === '') {
                $testLabel = trim((string) ($row->analysis_type?->name ?? 'Test'));
            }

            $assignedIds = is_array($row->assigned_analyst_ids)
                ? array_values(array_filter(array_map('strval', $row->assigned_analyst_ids)))
                : [];
            if ($assignedIds === [] && trim((string) ($row->user_id ?? '')) !== '') {
                $assignedIds = [(string) $row->user_id];
            }
            $names = [];
            foreach ($assignedIds as $id) {
                $name = trim((string) ($analystNames[$id] ?? ''));
                if ($name !== '') {
                    $names[] = $name;
                }
            }
            if ($names === [] && trim((string) ($row->operator?->name ?? '')) !== '') {
                $names[] = (string) $row->operator->name;
            }

                $flatRows[] = [
                    'captured_result_id' => (string) $row->id,
                    'batch_id' => (string) $batch->id,
                    'batch_code' => (string) ($batch->batch_code ?? $batch->id),
                    'row_key' => null,
                    'element_id' => trim((string) ($row->analysis_element_id ?? '')),
                    'lab_section_id' => $sectionId,
                    'lab_section_name' => $sectionId === self::UNASSIGNED_SECTION_ID
                        ? 'Unassigned'
                        : (string) ($row->labSection?->name ?? 'Lab section'),
                    'sample_label' => $sampleLabel !== '' ? $sampleLabel : $sampleCode,
                    'sample_code' => $sampleCode,
                    'test_label' => $testLabel,
                    'analysts' => $names !== [] ? implode(', ', $names) : 'None assigned',
                    'subcontracted' => (bool) ($row->analyte_status_contracted ?? false),
                    'result' => $includeResultColumn ? (string) ($row->result ?? '') : '',
                ];
        }

        $reference = trim((string) ($batch->batch_code ?? $batch->id));

        return $this->assemblePayload(
            title: 'Request Tests Worksheet',
            reference: $reference,
            context: 'batch',
            includeResultColumn: $includeResultColumn,
            requestInfo: $requestInfo,
            flatRows: $flatRows,
        );
    }

    /**
     * @param  list<array<string, mixed>>  $flatRows
     * @param  array{fields: list<array{label: string, value: string, name: ?string}>, remarks: ?string}  $requestInfo
     * @param  array<string, mixed>|null  $catalog
     * @return array{
     *     title: string,
     *     reference: string,
     *     context: string,
     *     include_result_column: bool,
     *     request_info: array{fields: list<array{label: string, value: string, name: ?string}>, remarks: ?string},
     *     catalog: array<string, mixed>|null,
     *     sections: list<array{lab_section_id: string, lab_section_name: string, samples: list<array<string, mixed>>}>,
     *     flat_rows: list<array<string, mixed>>
     * }
     */
    private function assemblePayload(
        string $title,
        string $reference,
        string $context,
        bool $includeResultColumn,
        array $requestInfo,
        array $flatRows,
        ?array $catalog = null,
    ): array {
        $sectionsMap = [];

        foreach ($flatRows as $row) {
            $sectionId = (string) ($row['lab_section_id'] ?? self::UNASSIGNED_SECTION_ID);
            $sectionName = (string) ($row['lab_section_name'] ?? 'Lab section');
            $sampleKey = (string) ($row['sample_code'] ?? $row['sample_label'] ?? 'Sample');

            if (! isset($sectionsMap[$sectionId])) {
                $sectionsMap[$sectionId] = [
                    'lab_section_id' => $sectionId,
                    'lab_section_name' => $sectionName,
                    'samples' => [],
                ];
            }

            if (! isset($sectionsMap[$sectionId]['samples'][$sampleKey])) {
                $sectionsMap[$sectionId]['samples'][$sampleKey] = [
                    'sample_label' => (string) ($row['sample_label'] ?? $sampleKey),
                    'sample_code' => (string) ($row['sample_code'] ?? $sampleKey),
                    'tests' => [],
                ];
            }

            $sectionsMap[$sectionId]['samples'][$sampleKey]['tests'][] = [
                'captured_result_id' => $row['captured_result_id'] ?? null,
                'batch_id' => $row['batch_id'] ?? null,
                'row_key' => $row['row_key'] ?? null,
                'element_id' => $row['element_id'] ?? null,
                'test_label' => (string) ($row['test_label'] ?? 'Test'),
                'analysts' => (string) ($row['analysts'] ?? ''),
                'subcontracted' => (bool) ($row['subcontracted'] ?? false),
                'result' => (string) ($row['result'] ?? ''),
            ];
        }

        $sections = [];
        foreach ($sectionsMap as $section) {
            $samples = array_values($section['samples']);
            usort($samples, static fn (array $a, array $b): int => strcasecmp(
                (string) ($a['sample_label'] ?? ''),
                (string) ($b['sample_label'] ?? '')
            ));
            $section['samples'] = $samples;
            $sections[] = $section;
        }

        usort($sections, static function (array $a, array $b): int {
            $aUnassigned = ($a['lab_section_id'] ?? '') === self::UNASSIGNED_SECTION_ID;
            $bUnassigned = ($b['lab_section_id'] ?? '') === self::UNASSIGNED_SECTION_ID;
            if ($aUnassigned !== $bUnassigned) {
                return $aUnassigned ? 1 : -1;
            }

            return strcasecmp((string) ($a['lab_section_name'] ?? ''), (string) ($b['lab_section_name'] ?? ''));
        });

        return [
            'title' => $title,
            'reference' => $reference,
            'context' => $context,
            'include_result_column' => $includeResultColumn,
            'request_info' => $requestInfo,
            'catalog' => $catalog,
            'sections' => $sections,
            'flat_rows' => array_values($flatRows),
        ];
    }

    /**
     * @return array{fields: list<array{label: string, value: string, name: ?string}>, remarks: ?string}
     */
    private function requestInfoForBatch(SampleHeader $batch): array
    {
        $instance = $batch->submissionFormInstance;
        $enquiry = $batch->sampleSubmissionRequest;

        if ($instance !== null) {
            return $this->integrityCheckService->requestInfoCard($instance, $enquiry);
        }

        $customer = $enquiry?->customer ?: $batch->client;
        $fields = [
            ['label' => 'Job / Batch', 'value' => (string) ($batch->batch_code ?? ''), 'name' => 'batch_code'],
            ['label' => 'Customer', 'value' => (string) ($customer?->name ?? ''), 'name' => 'customer'],
            ['label' => 'Sample type', 'value' => (string) ($batch->sample_type?->name ?? ''), 'name' => 'sample_type'],
            ['label' => 'Status', 'value' => (string) ($batch->status ?? ''), 'name' => 'status'],
            ['label' => 'Priority', 'value' => (string) ($batch->priority ?? ''), 'name' => 'priority'],
        ];

        return [
            'fields' => array_values(array_filter(
                $fields,
                static fn (array $field): bool => trim((string) ($field['value'] ?? '')) !== ''
            )),
            'remarks' => null,
        ];
    }

    /**
     * @param  Collection<int, CapturedResult>  $captured
     * @return array<string, string>
     */
    private function resolveAnalystNames(Collection $captured): array
    {
        $ids = [];
        foreach ($captured as $row) {
            if (is_array($row->assigned_analyst_ids)) {
                foreach ($row->assigned_analyst_ids as $id) {
                    $id = trim((string) $id);
                    if ($id !== '') {
                        $ids[$id] = true;
                    }
                }
            }
            $userId = trim((string) ($row->user_id ?? ''));
            if ($userId !== '') {
                $ids[$userId] = true;
            }
        }

        if ($ids === []) {
            return [];
        }

        return User::query()
            ->whereIn('id', array_keys($ids))
            ->get(['id', 'name'])
            ->mapWithKeys(static fn (User $user): array => [(string) $user->id => (string) $user->name])
            ->all();
    }

    /**
     * Ensure section names exist for integrity exports when only IDs are known.
     *
     * @param  list<string>  $sectionIds
     * @return array<string, string>
     */
    public function labSectionNamesForIds(array $sectionIds): array
    {
        $ids = array_values(array_filter(array_map('strval', $sectionIds), static fn (string $id): bool => $id !== '' && $id !== self::UNASSIGNED_SECTION_ID));
        if ($ids === []) {
            return [];
        }

        return SampleAnalysisStage::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name'])
            ->mapWithKeys(static fn (SampleAnalysisStage $section): array => [
                (string) $section->id => (string) $section->name,
            ])
            ->all();
    }
}
