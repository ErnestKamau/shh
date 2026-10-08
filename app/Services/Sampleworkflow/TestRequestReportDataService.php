<?php

namespace App\Services\Sampleworkflow;

use App\BatchLabSectionApprover;
use App\CapturedResult;
use App\Country;
use App\Enums\CompanyCode;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\SamplePoint;
use App\Models\SubmissionFormInstance;
use App\SampleAnalysisDates;
use App\SampleDetails;
use App\SampleHeader;
use App\SamplesCategory;
use App\Services\Lab\UncertaintyBudgetResolver;
use App\Services\Sampleworkflow\StatementOfConformityService;
use App\User;
use Illuminate\Support\Facades\Storage;

class TestRequestReportDataService
{
    private readonly SplitJobReportGroupService $reportGroups;

    public function __construct(
        private readonly TestRequestFormReportDataBuilder $trfReportBuilder,
        private readonly TrfSampleFieldMapper $trfMapper,
        ?SplitJobReportGroupService $reportGroups = null,
    ) {
        $this->reportGroups = $reportGroups ?? new SplitJobReportGroupService();
    }

    /**
     * @param  array{
     *     logoPublicUrlFallback?: bool,
     *     lab_section_id?: string|null,
     *     lab_section_ids?: list<string>|string|null,
     *     sample_ids?: list<string>|string|null
     * }  $options
     * @return array<string, mixed>
     */
    public function build(SampleHeader $batch, string $reportNumber, array $options = []): array
    {
        $batch->loadMissing(['customer.country', 'sample_type', 'samples', 'receivingofficer']);

        app(StatementOfConformityService::class)->ensureForBatch($batch);

        $filterLabSectionIds = $this->normalizeLabSectionIds(
            $options['lab_section_ids'] ?? ($options['lab_section_id'] ?? null)
        );
        $filterLabSectionName = '';
        $filterSampleIds = $this->normalizeSampleIds($options['sample_ids'] ?? null);

        $sfi = $this->resolveSubmissionFormInstance($batch);
        $trfPayload = $sfi !== null ? $this->trfReportBuilder->buildFromSubmissionFormInstance($sfi) : null;
        $formData = $sfi !== null
            ? app(\App\Services\SubmissionForm\SubmissionFormValueNormalizer::class)->valuesMapFromInstance($sfi)
            : [];
        $trfRows = $this->trfMapper->sampleRowsFromFormData($formData);
        $trfRows = $this->enrichSampleRowsWithIndexedCollectionFields($trfRows, $formData);
        $normalizedRows = is_array($trfPayload['sampleRows'] ?? null) ? $trfPayload['sampleRows'] : [];

        $reportGroupIds = $this->reportGroups->reportGroupIds($batch);
        $isSplitGroup = count($reportGroupIds) > 1;
        $groupSamplesQuery = fn () => SamplesCategory::whereIn('sample_header_id', $reportGroupIds)
            ->when($isSplitGroup, fn ($query) => $query->orderBy('sample_code'))
            ->get();

        $samples = $groupSamplesQuery();
        if ($samples->isEmpty() && SampleDetails::whereIn('sample_header_id', $reportGroupIds)->exists()) {
            app(SamplesByCategoryViewService::class)->recreate();
            $samples = $groupSamplesQuery();
        }
        $firstDetail = SampleDetails::whereIn('sample_header_id', $reportGroupIds)
            ->when($isSplitGroup, fn ($query) => $query->orderBy('sample_code'))
            ->first();

        $analysisDate = SampleAnalysisDates::whereIn('sample_header_id', $reportGroupIds)
            ->orderBy('start_analysis_date', 'ASC')
            ->first();

        [$analysisStartDate, $analysisEndDate] = $this->resolveBatchAnalysisDateRange($reportGroupIds);

        $firstNormalizedRow = $normalizedRows[0] ?? [];
        $firstRawRow = $trfRows[0] ?? [];

        $sampleDescription = $this->firstNonEmptyFromMixed(
            strip_tags($this->scalarValue($batch->description ?? '')),
            $firstNormalizedRow['sample_description'] ?? null,
            $firstRawRow['sample_description'] ?? null,
        ) ?? '-';

        $collection = is_array($trfPayload) && is_array($trfPayload['collection'] ?? null)
            ? $trfPayload['collection']
            : [];

        $trfCollectionExtras = is_array($collection['collection_extras'] ?? null)
            ? $collection['collection_extras']
            : [
                'date_received' => $this->formatReportDate($formData['date_received'] ?? null),
                'packaging' => $this->scalarValue($formData['packaging'] ?? null),
                'sample_weight' => $this->scalarValue($formData['sample_weight'] ?? null),
                'sample_information' => $this->scalarValue($formData['sample_information'] ?? null),
                'ship_name' => $this->scalarValue($formData['ship_name'] ?? null),
                'port_of_loading' => $this->scalarValue($formData['port_of_loading'] ?? null),
                'port_of_discharge' => $this->scalarValue($formData['port_of_discharge'] ?? null),
                'seal_number' => $this->scalarValue($formData['seal_number'] ?? null),
            ];

        $trfCollectionExtras = [
            'date_received' => $this->firstNonEmptyFromMixed(
                $trfCollectionExtras['date_received'] ?? null,
                $this->formatReportDate($formData['date_received'] ?? null),
            ) ?? '-',
            'packaging' => $this->firstNonEmptyFromMixed($trfCollectionExtras['packaging'] ?? null) ?? '-',
            'sample_weight' => $this->firstNonEmptyFromMixed($trfCollectionExtras['sample_weight'] ?? null) ?? '-',
            'sample_information' => $this->firstNonEmptyFromMixed($trfCollectionExtras['sample_information'] ?? null) ?? '-',
            'ship_name' => $this->firstNonEmptyFromMixed($trfCollectionExtras['ship_name'] ?? null) ?? '-',
            'port_of_loading' => $this->firstNonEmptyFromMixed($trfCollectionExtras['port_of_loading'] ?? null) ?? '-',
            'port_of_discharge' => $this->firstNonEmptyFromMixed($trfCollectionExtras['port_of_discharge'] ?? null) ?? '-',
            'seal_number' => $this->firstNonEmptyFromMixed($trfCollectionExtras['seal_number'] ?? null) ?? '-',
        ];

        $dateReceived = $this->firstNonEmptyFromMixed(
            $trfCollectionExtras['date_received'] ?? null,
            $batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : null,
        ) ?? '-';

        $containerType = $this->firstNonEmptyFromMixed(
            $this->selectedCheckboxLabels($collection['sampling_apparatus'] ?? null),
            $formData['sampling_apparatus'] ?? null,
            $firstRawRow['container_type'] ?? null,
            $formData['container_type'] ?? null,
        ) ?? '-';

        $sampleWeight = $this->firstNonEmptyFromMixed(
            $firstDetail?->quantity ?? null,
            $this->trfMapper->formatRowQuantity($firstRawRow),
            $firstNormalizedRow['qty'] ?? null,
            $formData['weight'] ?? null,
        ) ?? '-';

        $sampleTemperature = $this->formatSampleTemperatureForReport(
            $this->firstNonEmptyFromMixed(
                $batch->condition_quality_sample ?? null,
                $firstRawRow['sample_temp'] ?? null,
                $firstNormalizedRow['sample_temp'] ?? null,
                $formData['sample_temperature'] ?? null,
            ) ?? 'NP',
        );

        $transportCondition = $this->firstNonEmptyFromMixed(
            $this->selectedCheckboxLabels($collection['transport_condition'] ?? null),
            $formData['transport_condition'] ?? null,
        ) ?? '-';

        $samplingMethod = $this->firstNonEmptyFromMixed(
            $this->selectedCheckboxLabels($collection['method_of_sampling'] ?? null),
            $formData['method_of_sampling'] ?? null,
        ) ?? '-';

        $samplingLocation = $this->firstResolvedSampleLocationLabel(
            $collection['sampling_location'] ?? null,
            $formData['sampling_location'] ?? null,
            $firstDetail?->sample_point_id ?? null,
            $batch->crm_unit_name ?? null,
            $batch->crm_unit_id ?? null,
        ) ?? '-';

        $originCountry = $this->firstNonEmptyFromMixed(
            $batch->customer?->country?->name ?? null,
            $sfi?->crmCustomer?->country?->name ?? null,
            $this->firstResolvedCountryLabel(
                $batch->customer?->country_id ?? null,
                $sfi?->crmCustomer?->country_id ?? null,
                $formData['origin_country'] ?? null,
                $formData['country_of_origin'] ?? null,
                $firstRawRow['origin_country'] ?? null,
                $firstRawRow['country_of_origin'] ?? null,
            ),
        ) ?? '-';

        $mfgDate = $this->formatReportDate(
            $firstDetail?->mfg_date,
            $firstNormalizedRow['production_date'] ?? null,
            $firstRawRow['production_date'] ?? null,
        );

        $expiryDate = $this->formatReportDate(
            $firstDetail?->expiry_date,
            $firstNormalizedRow['expiration_date'] ?? null,
            $firstRawRow['expiration_date'] ?? $firstRawRow['expiry_date'] ?? null,
        );

        $batchLotNo = $this->firstNonEmptyFromMixed(
            $firstDetail?->batch_lot_no ?? null,
            $firstNormalizedRow['batch_number'] ?? null,
            $firstRawRow['batch_number'] ?? null,
        ) ?? '-';

        $attention = $this->resolveAttention($batch, $formData, is_array($trfPayload) ? $trfPayload : null);

        $samplePointByIndex = [];
        foreach ($samples->values() as $index => $sample) {
            $samplePointByIndex[$index] = $this->firstResolvedSamplePointLabel(
                $sample->sample_point_name ?? null,
                $sample->sample_point_id ?? null,
                $normalizedRows[$index]['sampling_point'] ?? $normalizedRows[$index]['location'] ?? null,
                $trfRows[$index]['sampling_point'] ?? $trfRows[$index]['location'] ?? $trfRows[$index]['sampling_location'] ?? null,
            ) ?? '-';
        }

        $totalPages = max(1, $samples->count() + 1);
        $company = getActiveCompany();
        $customer = $this->resolveReportCustomer($batch, $sfi, $formData, is_array($trfPayload) ? $trfPayload : null);

        [$reportLogos, $reportLogo, $companyLogo] = $this->resolveLogos(
            $company,
            (bool) ($options['logoPublicUrlFallback'] ?? false),
        );

        [$approver, $approverUser, $approverRole, $approvalDate, $signatureSrc, $signatureWarning] = $this->resolveApproverSignature($batch);

        $capturedResults = CapturedResult::query()
            ->whereIn('sample_header_id', $reportGroupIds)
            ->with(['analysisElement:id,hod,lod', 'labSection:id,name', 'user:id,name,id_number'])
            ->get();

        if ($filterLabSectionIds !== []) {
            $capturedResults = $capturedResults
                ->filter(static fn (CapturedResult $result): bool => in_array((string) ($result->lab_section_id ?? ''), $filterLabSectionIds, true))
                ->values();

            $sampleIdsWithSectionResults = $capturedResults
                ->pluck('sample_detail_id')
                ->map(static fn ($id): string => (string) $id)
                ->unique()
                ->all();

            if ($filterSampleIds !== []) {
                $sampleIdsWithSectionResults = array_values(array_intersect($sampleIdsWithSectionResults, $filterSampleIds));
            }

            $capturedResults = $capturedResults
                ->filter(static fn (CapturedResult $result): bool => in_array((string) ($result->sample_detail_id ?? ''), $sampleIdsWithSectionResults, true))
                ->values();

            // Keep TRF row alignment by original index before dropping samples without this section.
            $alignedNormalizedRows = [];
            $alignedTrfRows = [];
            $alignedSamplePoints = [];
            foreach ($samples->values() as $index => $sample) {
                if (! in_array((string) $sample->id, $sampleIdsWithSectionResults, true)) {
                    continue;
                }
                $alignedNormalizedRows[] = $normalizedRows[$index] ?? [];
                $alignedTrfRows[] = $trfRows[$index] ?? [];
                $alignedSamplePoints[] = $samplePointByIndex[$index] ?? '-';
            }
            $normalizedRows = $alignedNormalizedRows;
            $trfRows = $alignedTrfRows;
            $samplePointByIndex = $alignedSamplePoints;

            $samples = $samples
                ->filter(static fn ($sample): bool => in_array((string) $sample->id, $sampleIdsWithSectionResults, true))
                ->values();

            $filterLabSectionName = \App\SampleAnalysisStage::query()
                ->whereIn('id', $filterLabSectionIds)
                ->orderBy('name')
                ->pluck('name')
                ->map(static fn ($name): string => trim((string) $name))
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');

            if ($filterLabSectionName === '') {
                $filterLabSectionName = $capturedResults
                    ->pluck('labSection.name')
                    ->map(static fn ($name): string => trim((string) $name))
                    ->filter()
                    ->unique()
                    ->values()
                    ->implode(', ');
            }
            if ($filterLabSectionName === '') {
                $filterLabSectionName = 'Laboratory';
            }
        } elseif ($filterSampleIds !== []) {
            $alignedNormalizedRows = [];
            $alignedTrfRows = [];
            $alignedSamplePoints = [];
            foreach ($samples->values() as $index => $sample) {
                if (! in_array((string) $sample->id, $filterSampleIds, true)) {
                    continue;
                }
                $alignedNormalizedRows[] = $normalizedRows[$index] ?? [];
                $alignedTrfRows[] = $trfRows[$index] ?? [];
                $alignedSamplePoints[] = $samplePointByIndex[$index] ?? '-';
            }
            $normalizedRows = $alignedNormalizedRows;
            $trfRows = $alignedTrfRows;
            $samplePointByIndex = $alignedSamplePoints;

            $samples = $samples
                ->filter(static fn ($sample): bool => in_array((string) $sample->id, $filterSampleIds, true))
                ->values();

            $capturedResults = $capturedResults
                ->filter(static fn (CapturedResult $result): bool => in_array((string) ($result->sample_detail_id ?? ''), $filterSampleIds, true))
                ->values();
        }

        $totalPages = max(1, $samples->count());
        $companyLetterhead = $this->buildCompanyLetterhead($company);

        $measureUncertaintyByCapturedResultId = app(UncertaintyBudgetResolver::class)
            ->buildMuPercentIndexForCapturedResults($capturedResults);

        $loqByCapturedResultId = $this->buildLoqIndex($capturedResults);

        $isBrazilExportationReport = $this->isBrazilExportationTrf($sfi);

        $sampleDetailContexts = $this->buildSampleDetailContexts(
            $batch,
            $samples,
            $reportNumber,
            $normalizedRows,
            $trfRows,
            $formData,
            $collection,
            $trfCollectionExtras,
            [
                'dateReceived' => $dateReceived,
                'analysisStartDate' => $analysisStartDate,
                'analysisEndDate' => $analysisEndDate,
                'containerType' => $containerType,
                'transportCondition' => $transportCondition,
                'samplingMethod' => $samplingMethod,
                'samplingLocation' => $samplingLocation,
                'originCountry' => $originCountry,
                'approvalDate' => $approvalDate,
            ],
            $capturedResults,
            $isBrazilExportationReport,
            $filterLabSectionName !== '' ? $filterLabSectionName : null,
        );

        return [
            'batch' => $batch,
            'samples' => $samples,
            'reportNumber' => $reportNumber,
            'filterLabSectionId' => $filterLabSectionIds[0] ?? null,
            'filterLabSectionIds' => $filterLabSectionIds,
            'filterLabSectionName' => $filterLabSectionName,
            'filterSampleIds' => $filterSampleIds,
            'approver' => $approver,
            'approverUser' => $approverUser,
            'approverRole' => $approverRole,
            'approvalDate' => $approvalDate,
            'analysisDate' => $analysisDate,
            'analysisStartDate' => $analysisStartDate,
            'analysisEndDate' => $analysisEndDate,
            'company' => $company,
            'companyLetterhead' => $companyLetterhead,
            'customer' => $customer,
            'reportLogo' => $reportLogo,
            'reportLogos' => $reportLogos,
            'companyLogo' => $companyLogo,
            'mfgDate' => $mfgDate,
            'expiryDate' => $expiryDate,
            'batchLotNo' => $batchLotNo,
            'sampleWeight' => $sampleWeight,
            'containerType' => $containerType,
            'sampleTemperature' => $sampleTemperature,
            'transportCondition' => $transportCondition,
            'samplingMethod' => $samplingMethod,
            'samplingLocation' => $samplingLocation,
            'originCountry' => $originCountry,
            'sampleDescription' => $sampleDescription,
            'dateReceived' => $dateReceived,
            'trfCollectionExtras' => $trfCollectionExtras,
            'attention' => $attention,
            'samplePointByIndex' => $samplePointByIndex,
            'sampleDetailContexts' => $sampleDetailContexts,
            'totalPages' => $totalPages,
            'signatureSrc' => $signatureSrc,
            'signatureWarning' => $signatureWarning,
            'measureUncertaintyByCapturedResultId' => $measureUncertaintyByCapturedResultId,
            'loqByCapturedResultId' => $loqByCapturedResultId,
            'isBrazilExportationReport' => $isBrazilExportationReport,
        ];
    }

    public function isBrazilExportationBatch(SampleHeader $batch): bool
    {
        return $this->isBrazilExportationTrf($this->resolveSubmissionFormInstance($batch));
    }

    public function normalizeLabSectionId(mixed $labSectionId): ?string
    {
        $ids = $this->normalizeLabSectionIds($labSectionId);

        return $ids[0] ?? null;
    }

    /**
     * @param  list<string>|string|null  $labSectionIds
     * @return list<string>
     */
    public function normalizeLabSectionIds(mixed $labSectionIds): array
    {
        if (is_string($labSectionIds)) {
            $labSectionIds = explode(',', $labSectionIds);
        }

        if (! is_array($labSectionIds)) {
            return [];
        }

        return collect($labSectionIds)
            ->map(static fn ($id): string => trim((string) $id))
            ->filter(static fn (string $id): bool => $id !== '' && \Illuminate\Support\Str::isUuid($id))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Group result rows into one section per lab section (no mixing under a shared header).
     *
     * @param  list<array{lab_section_id?: string|null, lab_section_name?: string|null}>  $rows
     * @return list<array{id: string, name: string, rows: list<array<string, mixed>>}>
     */
    public function groupResultRowsByLabSection(array $rows, string $fallbackSectionName = 'Lab Section'): array
    {
        if ($rows === []) {
            return [];
        }

        return collect($rows)
            ->map(static function (array $row) use ($fallbackSectionName): array {
                $sectionId = trim((string) ($row['lab_section_id'] ?? ''));
                $sectionName = trim((string) ($row['lab_section_name'] ?? ''));

                $row['lab_section_id'] = $sectionId !== '' ? $sectionId : '__unassigned__';
                $row['lab_section_name'] = $sectionName !== '' ? $sectionName : $fallbackSectionName;

                return $row;
            })
            ->groupBy('lab_section_id')
            ->map(static function ($groupedRows): array {
                $first = $groupedRows->first();

                return [
                    'id' => (string) ($first['lab_section_id'] ?? '__unassigned__'),
                    'name' => (string) ($first['lab_section_name'] ?? 'Lab Section'),
                    'rows' => $groupedRows->values()->all(),
                ];
            })
            ->sortBy(static fn (array $section): string => mb_strtolower($section['name']))
            ->values()
            ->all();
    }

    /**
     * @param  list<string>|string|null  $sampleIds
     * @return list<string>
     */
    public function normalizeSampleIds(mixed $sampleIds): array
    {
        if (is_string($sampleIds)) {
            $sampleIds = explode(',', $sampleIds);
        }

        if (! is_array($sampleIds)) {
            return [];
        }

        return collect($sampleIds)
            ->map(static fn ($id): string => trim((string) $id))
            ->filter(static fn (string $id): bool => $id !== '' && \Illuminate\Support\Str::isUuid($id))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Exportation TRF misc fields for Sample Verification (Brazil only).
     *
     * @return array{fields: array<string, string>, form_name: string}|null
     */
    public function exportationSampleInfoForBatch(SampleHeader $batch): ?array
    {
        $sfi = $this->resolveSubmissionFormInstance($batch);
        if (! $this->isBrazilExportationTrf($sfi)) {
            return null;
        }

        $formData = $sfi !== null
            ? app(\App\Services\SubmissionForm\SubmissionFormValueNormalizer::class)->valuesMapFromInstance($sfi)
            : [];
        $trfPayload = $sfi !== null ? $this->trfReportBuilder->buildFromSubmissionFormInstance($sfi) : null;
        $collection = is_array($trfPayload) && is_array($trfPayload['collection'] ?? null)
            ? $trfPayload['collection']
            : [];
        $extras = is_array($collection['collection_extras'] ?? null)
            ? $collection['collection_extras']
            : [];

        $sampleDescription = $this->firstNonEmptyFromMixed(
            $collection['sample_description'] ?? null,
            $formData['sample_description'] ?? null,
            $formData['sample_product'] ?? null,
            $formData['product'] ?? null,
            $batch->description ?? null,
        ) ?? '';

        $fields = [
            'sample' => $sampleDescription,
            'date_received' => $this->firstNonEmptyFromMixed(
                $extras['date_received'] ?? null,
                $this->formatReportDate($formData['date_received'] ?? null),
                $batch->receipt_date ? date('d/m/Y', strtotime((string) $batch->receipt_date)) : null,
            ) ?? '',
            'packaging' => $this->firstNonEmptyFromMixed(
                $extras['packaging'] ?? null,
                $formData['packaging'] ?? null,
            ) ?? '',
            'sample_weight' => $this->firstNonEmptyFromMixed(
                $extras['sample_weight'] ?? null,
                $formData['sample_weight'] ?? null,
            ) ?? '',
            'sample_information' => $this->firstNonEmptyFromMixed(
                $extras['sample_information'] ?? null,
                $formData['sample_information'] ?? null,
            ) ?? '',
            'ship_name' => $this->firstNonEmptyFromMixed(
                $extras['ship_name'] ?? null,
                $formData['ship_name'] ?? null,
            ) ?? '',
            'port_of_loading' => $this->firstNonEmptyFromMixed(
                $extras['port_of_loading'] ?? null,
                $formData['port_of_loading'] ?? null,
            ) ?? '',
            'port_of_discharge' => $this->firstNonEmptyFromMixed(
                $extras['port_of_discharge'] ?? null,
                $formData['port_of_discharge'] ?? null,
            ) ?? '',
            'seal_number' => $this->firstNonEmptyFromMixed(
                $extras['seal_number'] ?? null,
                $formData['seal_number'] ?? null,
            ) ?? '',
        ];

        return [
            'fields' => $fields,
            'form_name' => (string) ($sfi?->submissionForm?->name ?? 'Exportation'),
        ];
    }

    private function isBrazilExportationTrf(?SubmissionFormInstance $sfi): bool
    {
        if (! companyHasCode(CompanyCode::Brl) || $sfi === null) {
            return false;
        }

        $form = $sfi->submissionForm;
        if ($form === null) {
            $sfi->loadMissing('submissionForm');
            $form = $sfi->submissionForm;
        }

        if ($form === null) {
            return false;
        }

        $haystack = mb_strtolower(trim(
            (string) ($form->document_code ?? '').' '.(string) ($form->name ?? '')
        ));

        return str_contains($haystack, 'export');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, SamplesCategory>  $samples
     * @param  list<array<string, mixed>>  $normalizedRows
     * @param  list<array<string, mixed>>  $trfRows
     * @param  array<string, mixed>  $formData
     * @param  array<string, mixed>  $collection
     * @param  array<string, mixed>  $trfCollectionExtras
     * @param  array<string, string|null>  $shared
     * @param  \Illuminate\Support\Collection<int, CapturedResult>  $capturedResults
     * @return list<array{rows: list<array{left: array{label: string, value: string, emphasize?: bool}, right: array{label: string, value: string, emphasize?: bool}}>, lab_section: string, conducted_by: string, sample_photo_data_uri: string}>
     */
    private function buildSampleDetailContexts(
        SampleHeader $batch,
        $samples,
        string $reportNumber,
        array $normalizedRows,
        array $trfRows,
        array $formData,
        array $collection,
        array $trfCollectionExtras,
        array $shared,
        $capturedResults,
        bool $isBrazilExportationReport = false,
        ?string $forcedLabSectionName = null,
    ): array {
        $contexts = [];
        $usersById = $this->analystUsersById($capturedResults);
        $sampleDetailsById = SampleDetails::query()
            ->whereIn('id', $samples->pluck('id')->filter()->values()->all())
            ->get(['id', 'photo_url', 'include_photo_in_report'])
            ->keyBy(fn (SampleDetails $detail): string => (string) $detail->id);

        foreach ($samples->values() as $index => $sample) {
            $normalizedRow = $normalizedRows[$index] ?? [];
            $rawRow = $trfRows[$index] ?? [];

            $sampleCode = format_sample_code($sample->sample_code ?? '');
            $sampleDescription = $this->firstNonEmptyFromMixed(
                strip_tags((string) ($sample->comments ?? '')),
                $normalizedRow['sample_description'] ?? null,
                $rawRow['sample_description'] ?? null,
            ) ?? '-';

            $sampleType = $this->firstNonEmptyFromMixed(
                $batch->sample_type?->name ?? null,
                $normalizedRow['sample_type'] ?? null,
                $rawRow['sample_type'] ?? null,
                $formData['sample_type'] ?? null,
            ) ?? '-';

            $productionDate = $this->formatReportDate(
                $sample->mfg_date ?? null,
                $normalizedRow['production_date'] ?? null,
                $rawRow['production_date'] ?? null,
            );

            $lotNo = $this->firstNonEmptyFromMixed(
                $sample->batch_lot_no ?? null,
                $normalizedRow['batch_number'] ?? null,
                $rawRow['batch_number'] ?? null,
            ) ?? '-';

            $expiry = $this->formatReportDate(
                $sample->expiry_date ?? null,
                $normalizedRow['expiration_date'] ?? null,
                $rawRow['expiration_date'] ?? $rawRow['expiry_date'] ?? null,
            );

            $quantity = $this->firstNonEmptyFromMixed(
                $sample->quantity ?? null,
                $this->trfMapper->formatRowQuantity($rawRow),
                $normalizedRow['qty'] ?? null,
            ) ?? '-';

            $sampleTemperature = $this->formatSampleTemperatureForReport(
                $this->trfValueOrNp(
                    $rawRow['sample_temp'] ?? null,
                    $rawRow['field_sample_temp'] ?? null,
                    $normalizedRow['sample_temp'] ?? null,
                ),
            );

            $samplingPoint = $this->trfValueOrNp(
                $normalizedRow['sampling_point'] ?? null,
                $rawRow['sampling_point_manual'] ?? null,
                $rawRow['sampling_point'] ?? null,
                $sample->sample_point_name ?? null,
            );

            $sampleCondition = $this->trfValueOrNp(
                $normalizedRow['sample_condition'] ?? null,
                $rawRow['sample_condition'] ?? null,
                $sample->sample_condition_name ?? null,
            );

            $dateReceived = $this->trfDateOrNp($rawRow['date_received'] ?? null);

            $transportCondition = $this->trfValueOrNp(
                $this->selectedCheckboxLabels($rawRow['transport_condition'] ?? null),
            );

            $samplingMethod = $this->trfValueOrNp(
                $this->selectedCheckboxLabels($rawRow['method_of_sampling'] ?? null),
            );

            $samplingLocation = $this->trfValueOrNp(
                $this->firstResolvedSampleLocationLabel(
                    $rawRow['sampling_location'] ?? null,
                    $normalizedRow['sampling_location'] ?? $normalizedRow['location'] ?? null,
                ),
            );

            $containerPackaging = $this->trfValueOrNp(
                $this->selectedCheckboxLabels($rawRow['sampling_apparatus'] ?? null),
                $rawRow['packaging'] ?? null,
                $rawRow['container_type'] ?? null,
            );

            $sampledBy = $this->trfValueOrNp(
                $rawRow['sampled_by'] ?? null,
            );
            if ($sampledBy !== 'N/P' && $sampledBy !== '-') {
                $resolvedSampledBy = \App\Services\Sampleworkflow\SampledByParty::displayLabel($sampledBy);
                if ($resolvedSampledBy !== '') {
                    $sampledBy = $resolvedSampledBy;
                }
            }

            $additionalDetails = $this->normalizeReportAdditionalDetails(
                $rawRow['additional_details'] ?? null,
                $normalizedRow['additional_details'] ?? null,
            );

            $labSectionNames = filled(trim((string) ($forcedLabSectionName ?? '')))
                ? trim((string) $forcedLabSectionName)
                : $capturedResults
                    ->where('sample_detail_id', $sample->id)
                    ->pluck('labSection.name')
                    ->filter(static fn ($name) => filled(trim((string) $name)))
                    ->unique()
                    ->values()
                    ->implode(', ');

            if ($labSectionNames === '') {
                $labSectionNames = $batch->getLabSectionsNames() ?: 'Laboratory';
            }

            $sampleResults = $capturedResults->where('sample_detail_id', $sample->id);

            $samplePhotoDataUri = '';
            $sampleDetail = $sampleDetailsById->get((string) $sample->id);
            if ($sampleDetail !== null && (bool) ($sampleDetail->include_photo_in_report ?? false)) {
                $photoPath = trim((string) ($sampleDetail->photo_url ?? ''));
                if ($photoPath !== '') {
                    $samplePhotoDataUri = $this->samplePhotoToDataUri($photoPath);
                }
            }

            if ($isBrazilExportationReport) {
                $rows = $this->brazilExportationSampleDetailRows(
                    $sampleDescription,
                    $quantity,
                    $normalizedRow,
                    $rawRow,
                    $trfCollectionExtras,
                    $shared,
                );
            } else {
                $rows = [
                    [
                        'left' => ['label' => 'job_no', 'value' => (string) $batch->batch_code],
                        'right' => ['label' => 'sample_no', 'value' => $sampleCode !== '' ? $sampleCode : '-'],
                    ],
                    [
                        'left' => ['label' => 'sample_description', 'value' => $sampleDescription],
                        'right' => ['label' => 'report_no', 'value' => $this->perSampleReportNumber($sampleCode, $reportNumber), 'emphasize' => true],
                    ],
                    [
                        'left' => ['label' => 'sample_type', 'value' => $sampleType],
                        'right' => ['label' => 'production_date', 'value' => $productionDate],
                    ],
                    [
                        'left' => ['label' => 'lot_no', 'value' => $lotNo],
                        'right' => ['label' => 'expiry_date', 'value' => $expiry],
                    ],
                    [
                        'left' => ['label' => 'weight', 'value' => $quantity],
                        'right' => ['label' => 'date_received', 'value' => $dateReceived],
                    ],
                    [
                        'left' => ['label' => 'sampled_by', 'value' => $sampledBy],
                        'right' => ['label' => 'analysis_start_date', 'value' => (string) ($shared['analysisStartDate'] ?? '-')],
                    ],
                    [
                        'left' => ['label' => 'sample_point', 'value' => $samplingPoint],
                        'right' => ['label' => 'analysis_end_date', 'value' => (string) ($shared['analysisEndDate'] ?? '-')],
                    ],
                    [
                        'left' => ['label' => 'sampling_location', 'value' => $samplingLocation],
                        'right' => ['label' => 'reporting_date', 'value' => (string) ($shared['approvalDate'] ?? date('d/m/Y'))],
                    ],
                    [
                        'left' => ['label' => 'sample_condition', 'value' => $sampleCondition],
                        'right' => ['label' => 'container_type', 'value' => $containerPackaging],
                    ],
                    [
                        'left' => ['label' => 'sample_temperature', 'value' => $sampleTemperature],
                        'right' => ['label' => 'origin_country', 'value' => (string) ($shared['originCountry'] ?? '-')],
                    ],
                    [
                        'left' => ['label' => 'transport_condition', 'value' => $transportCondition],
                        'right' => ['label' => 'sampling_method', 'value' => $samplingMethod],
                    ],
                ];
            }

            $normalizedRows = $this->normalizeSampleDetailRows($rows);
            $contexts[] = [
                'rows' => $isBrazilExportationReport
                    ? $normalizedRows
                    : $this->appendAdditionalDetailRows($normalizedRows, $additionalDetails),
                'lab_section' => $labSectionNames,
                'conducted_by' => $this->conductedByNames($sampleResults, $usersById),
                'sample_photo_data_uri' => $samplePhotoDataUri,
            ];
        }

        return $contexts;
    }

    /**
     * SAMPLE INFORMATION rows for Brazil Exportation TRF only (p2 product + p3 misc).
     * Single-column rows for the bilingual label/value table.
     *
     * Exportation info (packaging, ship, ports, seal, …) is captured per sample row.
     * Older instances only have it as a job-level value ($trfCollectionExtras); that's
     * kept as a fallback so historical reports keep rendering.
     *
     * @param  array<string, mixed>  $normalizedRow
     * @param  array<string, mixed>  $rawRow
     * @param  array<string, mixed>  $trfCollectionExtras
     * @param  array<string, string|null>  $shared
     * @return list<array{left: array{label: string, value: string, emphasize?: bool}, right: null}>
     */
    private function brazilExportationSampleDetailRows(
        string $sampleDescription,
        string $quantity,
        array $normalizedRow,
        array $rawRow,
        array $trfCollectionExtras,
        array $shared,
    ): array {
        $sampleWeight = $this->firstNonEmptyFromMixed(
            $rawRow['sample_weight'] ?? null,
            $normalizedRow['sample_weight'] ?? null,
            $trfCollectionExtras['sample_weight'] ?? null,
            $quantity,
        ) ?? '-';

        $dateReceived = $this->firstNonEmptyFromMixed(
            $rawRow['date_received'] ?? null,
            $normalizedRow['date_received'] ?? null,
            $shared['dateReceived'] ?? null,
            $trfCollectionExtras['date_received'] ?? null,
        ) ?? '-';

        $fields = [
            'sample' => $sampleDescription,
            'date_received' => $dateReceived,
            'packaging' => $this->firstNonEmptyFromMixed(
                $rawRow['packaging'] ?? null,
                $normalizedRow['packaging'] ?? null,
                $trfCollectionExtras['packaging'] ?? null,
            ) ?? '-',
            'sample_weight' => $sampleWeight,
            'sample_information' => $this->firstNonEmptyFromMixed(
                $rawRow['sample_information'] ?? null,
                $normalizedRow['sample_information'] ?? null,
                $trfCollectionExtras['sample_information'] ?? null,
            ) ?? '-',
            'ship_name' => $this->firstNonEmptyFromMixed(
                $rawRow['ship_name'] ?? null,
                $normalizedRow['ship_name'] ?? null,
                $trfCollectionExtras['ship_name'] ?? null,
            ) ?? '-',
            'port_of_loading' => $this->firstNonEmptyFromMixed(
                $rawRow['port_of_loading'] ?? null,
                $normalizedRow['port_of_loading'] ?? null,
                $trfCollectionExtras['port_of_loading'] ?? null,
            ) ?? '-',
            'port_of_discharge' => $this->firstNonEmptyFromMixed(
                $rawRow['port_of_discharge'] ?? null,
                $normalizedRow['port_of_discharge'] ?? null,
                $trfCollectionExtras['port_of_discharge'] ?? null,
            ) ?? '-',
            'seal_number' => $this->firstNonEmptyFromMixed(
                $rawRow['seal_number'] ?? null,
                $normalizedRow['seal_number'] ?? null,
                $trfCollectionExtras['seal_number'] ?? null,
            ) ?? '-',
        ];

        $rows = [];
        foreach ($fields as $label => $value) {
            $rows[] = [
                'left' => ['label' => $label, 'value' => $value, 'emphasize' => true],
                'right' => null,
            ];
        }

        return $rows;
    }

    public function perSampleReportNumber(string $sampleCode, string $batchReportNumber): string
    {
        $formattedCode = format_sample_code($sampleCode);

        if ($formattedCode === '') {
            return $batchReportNumber;
        }

        if (preg_match('/(-R\d+.*)$/i', $batchReportNumber, $matches)) {
            return $formattedCode.$matches[1];
        }

        return $formattedCode;
    }

    /**
     * Keep the designed field layout and show empty / placeholder values as NP.
     *
     * @param  list<array{left?: array{label: string, value: string, emphasize?: bool}|null, right?: array{label: string, value: string, emphasize?: bool}|null}>  $rows
     * @return list<array{left: array{label: string, value: string, emphasize?: bool}, right: array{label: string, value: string, emphasize?: bool}|null}>
     */
    private function normalizeSampleDetailRows(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $pair) {
            $left = is_array($pair['left'] ?? null)
                ? $this->normalizeSampleDetailCell($pair['left'])
                : null;
            $right = is_array($pair['right'] ?? null)
                ? $this->normalizeSampleDetailCell($pair['right'])
                : null;

            if ($left === null && $right === null) {
                continue;
            }

            if ($left === null) {
                $left = $right;
                $right = null;
            }

            $normalized[] = [
                'left' => $left,
                'right' => $right,
            ];
        }

        return $normalized;
    }

    /**
     * @param  array{label: string, value: string, emphasize?: bool}  $cell
     * @return array{label: string, value: string, emphasize?: bool}
     */
    private function normalizeSampleDetailCell(array $cell): array
    {
        $label = (string) ($cell['label'] ?? '');
        $value = trim((string) ($cell['value'] ?? ''));
        if (! $this->isReportValuePresent($value)) {
            $value = 'NP';
        } elseif ($this->isOptionListDetailLabel($label)) {
            $value = $this->formatReportOptionListDisplay($value);
        }

        $normalized = [
            'label' => $label,
            'value' => $value,
        ];

        if (! empty($cell['emphasize'])) {
            $normalized['emphasize'] = true;
        }

        return $normalized;
    }

    /**
     * TRF multi-select option keys stored as slugs (e.g. sterile_bag,apha).
     */
    private function isOptionListDetailLabel(string $label): bool
    {
        return in_array($label, [
            'container_type',
            'sampling_method',
            'transport_condition',
            'sample_condition',
        ], true);
    }

    /**
     * Humanize comma-separated TRF option values for report display.
     * Example: "sterile_bag,sterile_bottle" → "sterile bag, sterile bottle".
     */
    private function formatReportOptionListDisplay(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '' || ! $this->isReportValuePresent($trimmed)) {
            return $trimmed;
        }

        $parts = preg_split('/\s*,\s*/', $trimmed) ?: [];
        $formatted = [];
        foreach ($parts as $part) {
            $part = trim(str_replace('_', ' ', (string) $part));
            if ($part !== '') {
                $formatted[] = $part;
            }
        }

        return $formatted === [] ? $trimmed : implode(', ', $formatted);
    }

    private function isReportValuePresent(mixed $value): bool
    {
        if (is_array($value)) {
            return $value !== [];
        }

        $trimmed = trim((string) ($value ?? ''));

        if ($trimmed === '') {
            return false;
        }

        return ! in_array($trimmed, ['-', '—', '–', 'N/A', 'n/a', 'NA'], true);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CapturedResult>  $capturedResults
     * @return array<string, string>
     */
    private function buildLoqIndex($capturedResults): array
    {
        $index = [];

        foreach ($capturedResults as $capturedResult) {
            $element = $capturedResult->analysisElement;
            $loq = $element?->hod ?? $element?->lod ?? null;

            $index[(string) $capturedResult->id] = ($loq !== null && $loq !== '')
                ? (string) $loq
                : '-';
        }

        return $index;
    }

    private function resolveSubmissionFormInstance(SampleHeader $batch): ?SubmissionFormInstance
    {
        if (! $batch->submission_form_instance_id) {
            return null;
        }

        return SubmissionFormInstance::query()
            ->with(['values.element', 'submissionForm', 'crmCustomer.country'])
            ->find($batch->submission_form_instance_id);
    }

    /**
     * @param  array<string, mixed>|null  $trfPayload
     */
    private function resolveAttention(SampleHeader $batch, array $formData, ?array $trfPayload): string
    {
        $attention = trim($batch->getContactPersonDetail());
        if ($this->isUsableAttention($attention)) {
            return $attention;
        }

        foreach ([
            $formData['contact_person'] ?? null,
            is_array($trfPayload) ? ($trfPayload['signatures']['customer_rep_name'] ?? null) : null,
            $formData['customer_representative_name'] ?? null,
        ] as $candidate) {
            $resolved = $this->resolveContactLabel($candidate);
            if ($this->isUsableAttention($resolved)) {
                return $resolved;
            }
        }

        return '-';
    }

    private function isUsableAttention(string $value): bool
    {
        $normalized = trim($value);

        return $normalized !== ''
            && $normalized !== '-'
            && ! $this->looksLikeUuid($normalized);
    }

    private function resolveContactLabel(mixed $candidate): string
    {
        $value = trim($this->scalarValue($candidate));
        if ($value === '') {
            return '';
        }

        if ($this->looksLikeUuid($value)) {
            $contact = getCrmCustomerContactById($value);
            if ($contact !== null) {
                return trim(implode(' ', array_filter([
                    $contact->first_name ?? null,
                    $contact->middle_name ?? null,
                    $contact->last_name ?? null,
                ])));
            }

            return '';
        }

        return $value;
    }

    private function looksLikeUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value);
    }

    private function firstResolvedSamplePointLabel(mixed ...$candidates): ?string
    {
        return $this->firstResolvedReferenceLabel(
            fn (string $id): string => $this->resolveSamplePointName($id),
            ...$candidates,
        );
    }

    private function firstResolvedSampleLocationLabel(mixed ...$candidates): ?string
    {
        return $this->firstResolvedReferenceLabel(
            fn (string $id): string => $this->resolveSamplingLocationName($id),
            ...$candidates,
        );
    }

    private function firstResolvedCountryLabel(mixed ...$candidates): ?string
    {
        return $this->firstResolvedReferenceLabel(
            fn (string $id): string => trim((string) (Country::query()->whereKey($id)->value('name') ?? '')),
            ...$candidates,
        );
    }

    private function firstResolvedReferenceLabel(callable $uuidResolver, mixed ...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $value = $this->scalarValue($candidate);
            if ($value === '' || $value === '-') {
                continue;
            }

            if ($this->looksLikeUuid($value) || ctype_digit($value)) {
                $resolved = trim((string) $uuidResolver($value));
                if ($resolved !== '' && ! $this->looksLikeUuid($resolved)) {
                    return $resolved;
                }

                continue;
            }

            return $value;
        }

        return null;
    }

    private function resolveSamplePointName(string $id): string
    {
        $point = SamplePoint::query()->with('unit')->find($id);
        if ($point !== null) {
            $name = $this->humanSamplePointName($point);
            if ($name !== '') {
                return $name;
            }

            $unitName = trim((string) ($point->unit?->name ?? ''));
            if ($unitName !== '') {
                return $unitName;
            }
        }

        return trim((string) (CRMCompanyUnit::query()->whereKey($id)->value('name') ?? ''));
    }

    private function resolveSamplingLocationName(string $id): string
    {
        $point = SamplePoint::query()->with('unit')->find($id);
        if ($point !== null) {
            $unitName = trim((string) ($point->unit?->name ?? ''));
            $pointName = $this->humanSamplePointName($point);
            if ($unitName !== '' && $pointName !== '') {
                return $unitName.', '.$pointName;
            }
            if ($unitName !== '') {
                return $unitName;
            }
            if ($pointName !== '') {
                return $pointName;
            }
        }

        return trim((string) (CRMCompanyUnit::query()->whereKey($id)->value('name') ?? ''));
    }

    private function humanSamplePointName(SamplePoint $point): string
    {
        $name = trim((string) ($point->getAttributes()['name'] ?? ''));
        if ($name !== '' && ! $this->looksLikeUuid($name)) {
            return $name;
        }

        $code = trim((string) ($point->code ?? ''));
        if ($code !== '' && ! $this->looksLikeUuid($code)) {
            return $code;
        }

        return '';
    }

    /**
     * @param  array<string, mixed>|null  $trfPayload
     */
    private function resolveReportCustomer(
        SampleHeader $batch,
        ?SubmissionFormInstance $sfi,
        array $formData,
        ?array $trfPayload,
    ): object {
        if ($batch->customer !== null) {
            return $batch->customer;
        }

        $customerBlock = is_array($trfPayload['customer'] ?? null) ? $trfPayload['customer'] : [];

        $name = $this->firstNonEmptyFromMixed(
            $formData['customer_name'] ?? null,
            $customerBlock['name'] ?? null,
            $sfi?->crmCustomer?->name ?? null,
        ) ?? '-';

        $physicalAddress = $this->firstNonEmptyFromMixed(
            $formData['physical_address'] ?? null,
            $customerBlock['physical_address'] ?? null,
            $sfi?->crmCustomer?->physical_address ?? null,
        );

        $postalAddress = $this->firstNonEmptyFromMixed(
            $formData['postal_address'] ?? null,
            $customerBlock['postal_address'] ?? null,
            $sfi?->crmCustomer?->postal_address ?? null,
        );

        return (object) [
            'name' => $name,
            'physical_address' => $physicalAddress,
            'postal_address' => $postalAddress,
        ];
    }

    /**
     * @return array{0: ?BatchLabSectionApprover, 1: ?User, 2: string, 3: string, 4: string, 5: ?string}
     */
    private function resolveApproverSignature(SampleHeader $batch): array
    {
        $approver = BatchLabSectionApprover::query()
            ->where('batch_id', $batch->id)
            ->where('batch_status', 'Sample Approval')
            ->where('show_report', 1)
            ->where('status', 1)
            ->orderByDesc('approval_date')
            ->first();

        if ($approver === null) {
            $approver = BatchLabSectionApprover::query()
                ->where('batch_id', $batch->id)
                ->where('batch_status', 'Sample Approval')
                ->where('status', 1)
                ->orderByDesc('approval_date')
                ->first();
        }

        if ($approver === null) {
            $approver = BatchLabSectionApprover::query()
                ->where('batch_id', $batch->id)
                ->where('batch_status', 'Sample Verification')
                ->where('status', 1)
                ->orderByDesc('approval_date')
                ->first();
        }

        $approverUser = $approver ? User::find($approver->user_id) : null;
        $positionLabel = $approver ? $approver->getApproverPositionDetails() : '-';
        $approverRole = ($positionLabel !== '' && $positionLabel !== '-')
            ? $positionLabel
            : 'Laboratory Manager';
        $approvalDate = $approver && $approver->approval_date
            ? date('d/m/Y', strtotime((string) $approver->approval_date))
            : date('d/m/Y');

        $signatureSrc = $this->resolveUserSignatureDataUri($approverUser);

        // Keep primary approver identity; fall back only for the signature image.
        if ($signatureSrc === '') {
            foreach ([$batch->approve_user_id ?? null, $batch->verify_user_id ?? null] as $fallbackUserId) {
                if ($fallbackUserId === null || $fallbackUserId === '' || ($approverUser && (string) $fallbackUserId === (string) $approverUser->id)) {
                    continue;
                }

                $fallbackUser = User::find($fallbackUserId);
                $signatureSrc = $this->resolveUserSignatureDataUri($fallbackUser);
                if ($signatureSrc !== '') {
                    break;
                }
            }
        }

        $signatureWarning = ($approverUser !== null && $signatureSrc === '')
            ? 'Approver has no electronic signature on file.'
            : null;

        return [$approver, $approverUser, $approverRole, $approvalDate, $signatureSrc, $signatureWarning];
    }

    private function resolveUserSignatureDataUri(?User $user): string
    {
        if ($user === null) {
            return '';
        }

        $fromElectronicSig = $this->resolveSignatureDataUri($user->electronic_sig ?? null);
        if ($fromElectronicSig !== '') {
            return $fromElectronicSig;
        }

        if (method_exists($user, 'getSignaturePath')) {
            $path = $user->getSignaturePath();
            if (is_string($path) && $path !== '') {
                if (str_starts_with($path, 'data:')) {
                    return $path;
                }

                if (is_readable($path)) {
                    $contents = @file_get_contents($path);
                    if ($contents !== false && $contents !== '') {
                        $mime = @mime_content_type($path) ?: 'image/png';

                        return 'data:'.$mime.';base64,'.base64_encode($contents);
                    }
                }

                $fromPath = $this->resolveSignatureDataUri($path);
                if ($fromPath !== '') {
                    return $fromPath;
                }
            }
        }

        return '';
    }

    private function resolveSignatureDataUri(?string $electronicSig): string
    {
        if ($electronicSig === null || trim($electronicSig) === '') {
            return '';
        }

        if (function_exists('signatureToDataUri')) {
            $dataUri = signatureToDataUri($electronicSig);
            if ($dataUri !== '') {
                return $dataUri;
            }
        }

        $electronicSig = trim($electronicSig);

        if (str_starts_with($electronicSig, 'data:')) {
            return $electronicSig;
        }

        if (str_starts_with($electronicSig, 'http://') || str_starts_with($electronicSig, 'https://')) {
            return $electronicSig;
        }

        // Direct filesystem candidates for personnel-signature uploads/pads
        $decoded = urldecode($electronicSig);
        $relative = ltrim((string) preg_replace('#^.*/storage/#', '', $decoded), '/');
        if ($relative !== '') {
            foreach ([
                storage_path('app/public/'.$relative),
                storage_path('app/'.$relative),
                public_path('storage/'.$relative),
                public_path($relative),
            ] as $candidate) {
                if (! is_readable($candidate)) {
                    continue;
                }

                $contents = @file_get_contents($candidate);
                if ($contents === false || $contents === '') {
                    continue;
                }

                $mime = @mime_content_type($candidate) ?: 'image/png';

                return 'data:'.$mime.';base64,'.base64_encode($contents);
            }
        }

        if ($this->signatureFileIsReadable($electronicSig) && function_exists('imageTobase64')) {
            $encoded = imageTobase64($electronicSig);
            if (is_string($encoded) && str_starts_with($encoded, 'data:')) {
                return $encoded;
            }
        }

        return '';
    }

    private function signatureFileIsReadable(string $electronicSig): bool
    {
        if (function_exists('getCoaApproverSignature')) {
            $path = getCoaApproverSignature($electronicSig);
            if (is_string($path) && $path !== '' && ! str_starts_with($path, 'data:') && is_readable($path)) {
                return true;
            }
        }

        $decoded = urldecode($electronicSig);
        $relative = ltrim((string) preg_replace('#^.*/storage/#', '', $decoded), '/');

        foreach ([
            storage_path('app/public/' . $relative),
            storage_path('app/' . $relative),
            public_path('storage/' . $relative),
        ] as $candidate) {
            if (is_readable($candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve logos configured for Test Report in System Settings → Companies.
     *
     * @return array{0: array<string, array{src: string, show_on_every_page: bool}>, 1: string, 2: string}
     */
    private function resolveLogos(?object $company, bool $allowPublicUrlFallback = false): array
    {
        $reportLogos = [];
        $reportLogo = '';
        $companyLogo = '';

        if ($company === null) {
            return [$reportLogos, $reportLogo, $companyLogo];
        }

        if (! empty($company->logo)) {
            $companyLogo = $this->resolveLogoSrc((string) $company->logo, $allowPublicUrlFallback);
        }

        if (method_exists($company, 'reportLogos')) {
            $assignedLogos = $company->reportLogos()
                ->where(function ($query) {
                    $query->where('report_type', 'test_request_report')
                        ->orWhereNull('report_type')
                        ->orWhere('report_type', '');
                })
                ->orderByRaw("CASE WHEN report_type = 'test_request_report' THEN 0 ELSE 1 END")
                ->get();

            foreach ($assignedLogos as $logo) {
                if (empty($logo->logo_path)) {
                    continue;
                }

                $key = ($logo->position_vertical ?? 'top').'_'.($logo->position_horizontal ?? 'left');
                $isExplicit = ($logo->report_type ?? '') === 'test_request_report';
                if (! $isExplicit && isset($reportLogos[$key])) {
                    continue;
                }

                $src = $this->resolveLogoSrc((string) $logo->logo_path, $allowPublicUrlFallback);
                if ($src === '') {
                    continue;
                }

                $reportLogos[$key] = [
                    'src' => $src,
                    'show_on_every_page' => (bool) ($logo->show_on_every_page ?? true),
                ];

                if ($reportLogo === '' || $isExplicit) {
                    $reportLogo = $src;
                }
            }
        }

        // Prefer company-uploaded report logo, then company logo (no hardcoded Brazil asset).
        if ($reportLogo === '' && ! empty($company->report_logo)) {
            $src = $this->resolveLogoSrc((string) $company->report_logo, $allowPublicUrlFallback);
            if ($src !== '') {
                $reportLogo = $src;
                $reportLogos['top_left'] = $reportLogos['top_left'] ?? [
                    'src' => $src,
                    'show_on_every_page' => true,
                ];
            }
        }

        if ($reportLogo === '') {
            $fallbackCandidates = array_filter([
                $company->logo ?? null,
                'images/logo-report.png',
                'images/company_logo.png',
            ]);
            foreach ($fallbackCandidates as $candidate) {
                $src = $this->resolveLogoSrc((string) $candidate, $allowPublicUrlFallback);
                if ($src !== '') {
                    $reportLogo = $src;
                    $reportLogos['top_left'] = $reportLogos['top_left'] ?? [
                        'src' => $src,
                        'show_on_every_page' => true,
                    ];
                    if ($companyLogo === '') {
                        $companyLogo = $src;
                    }
                    break;
                }
            }
        }

        if ($companyLogo === '' && $reportLogo !== '') {
            $companyLogo = $reportLogo;
        }

        return [$reportLogos, $reportLogo, $companyLogo];
    }

    private function resolveLogoSrc(string $path, bool $allowPublicUrlFallback = false): string
    {
        $dataUri = $this->pathToDataUri($path);
        if ($dataUri !== '') {
            return $dataUri;
        }

        if (! $allowPublicUrlFallback) {
            return '';
        }

        return $this->pathToPublicUrl($path);
    }

    private function pathToPublicUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'data:') || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/')) {
            return url('/'.ltrim($path, '/'));
        }

        if (str_starts_with($path, 'images/') || str_starts_with($path, '/images/')) {
            return url('/'.ltrim($path, '/'));
        }

        $relative = ltrim(str_replace('\\', '/', $path), '/');
        if ($relative !== '') {
            return url('/storage/'.$relative);
        }

        return '';
    }

    /**
     * @return array{0: string, 1: string}
     */
    /**
     * @param  list<string>|int|string  $batchIds
     */
    private function resolveBatchAnalysisDateRange(array|int|string $batchIds): array
    {
        $records = SampleAnalysisDates::whereIn('sample_header_id', (array) $batchIds)
            ->get(['start_analysis_date', 'analysis_dates']);

        $startDates = [];
        $endDates = [];

        foreach ($records as $record) {
            $recordStart = $this->normalizeDateValue($record->start_analysis_date ?? null);
            if ($recordStart !== null) {
                $startDates[] = $recordStart;
                $endDates[] = $recordStart;
            }

            $decoded = json_decode((string) $record->analysis_dates, true);
            if (! is_array($decoded)) {
                continue;
            }

            foreach ($decoded as $sectionRange) {
                if (is_array($sectionRange)) {
                    $startDate = $this->normalizeDateValue($sectionRange['start_date'] ?? $sectionRange['start'] ?? null);
                    $endDate = $this->normalizeDateValue($sectionRange['end_date'] ?? $sectionRange['end'] ?? null);

                    if ($startDate !== null) {
                        $startDates[] = $startDate;
                        $endDates[] = $startDate;
                    }
                    if ($endDate !== null) {
                        $endDates[] = $endDate;
                    }

                    continue;
                }

                $singleDate = $this->normalizeDateValue($sectionRange);
                if ($singleDate !== null) {
                    $startDates[] = $singleDate;
                    $endDates[] = $singleDate;
                }
            }
        }

        $startDate = ! empty($startDates) ? min($startDates) : null;
        $endDate = ! empty($endDates) ? max($endDates) : $startDate;

        return [
            $this->formatReportDate($startDate),
            $this->formatReportDate($endDate),
        ];
    }

    private function pathToDataUri(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        $absolute = $this->resolveReadableLogoPath($path);
        if ($absolute === null) {
            return '';
        }

        $contents = @file_get_contents($absolute);
        if ($contents === false || $contents === '') {
            return '';
        }

        $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    /**
     * Letterbox a sample photo onto a fixed 1800x1200 (3:2) white canvas so every
     * Test Report photo occupies the same 150mm x 100mm frame.
     */
    private function samplePhotoToDataUri(string $path): string
    {
        $fallback = $this->pathToDataUri($path);
        if ($fallback === '' || ! function_exists('imagecreatetruecolor') || ! function_exists('imagecreatefromstring')) {
            return $fallback;
        }

        $absolute = $this->resolveReadableLogoPath($path);
        if ($absolute === null) {
            return $fallback;
        }

        $contents = @file_get_contents($absolute);
        if ($contents === false || $contents === '') {
            return $fallback;
        }

        $source = @imagecreatefromstring($contents);
        if ($source === false) {
            return $fallback;
        }

        if (function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($source);
        }

        $canvasWidth = 1800;
        $canvasHeight = 1200;
        $srcWidth = imagesx($source);
        $srcHeight = imagesy($source);
        if ($srcWidth < 1 || $srcHeight < 1) {
            imagedestroy($source);

            return $fallback;
        }

        $scale = min($canvasWidth / $srcWidth, $canvasHeight / $srcHeight);
        $destWidth = max(1, (int) round($srcWidth * $scale));
        $destHeight = max(1, (int) round($srcHeight * $scale));
        $offsetX = (int) round(($canvasWidth - $destWidth) / 2);
        $offsetY = (int) round(($canvasHeight - $destHeight) / 2);

        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopyresampled(
            $canvas,
            $source,
            $offsetX,
            $offsetY,
            0,
            0,
            $destWidth,
            $destHeight,
            $srcWidth,
            $srcHeight
        );

        ob_start();
        imagejpeg($canvas, null, 85);
        $jpeg = ob_get_clean();
        imagedestroy($source);
        imagedestroy($canvas);

        if ($jpeg === false || $jpeg === '') {
            return $fallback;
        }

        return 'data:image/jpeg;base64,'.base64_encode($jpeg);
    }

    private function resolveReadableLogoPath(string $path): ?string
    {
        $path = trim($path);
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return null;
        }

        if (is_readable($path)) {
            return $path;
        }

        $relative = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($relative, 'storage/')) {
            $relative = substr($relative, strlen('storage/'));
        }

        $candidates = [
            public_path($path),
            public_path(ltrim($path, '/')),
            public_path('storage/'.$relative),
            storage_path('app/public/'.$relative),
            storage_path('app/'.$relative),
            base_path('public/'.$relative),
        ];

        try {
            if ($relative !== '' && Storage::disk('public')->exists($relative)) {
                $candidates[] = Storage::disk('public')->path($relative);
            }
        } catch (\Throwable) {
            // continue with filesystem candidates
        }

        foreach (array_unique(array_filter($candidates)) as $candidate) {
            if (is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function formatReportDate(mixed ...$candidates): string
    {
        foreach ($candidates as $candidate) {
            $value = $this->scalarValue($candidate);
            if ($value === '') {
                continue;
            }

            try {
                return date('d/m/Y', strtotime($value));
            } catch (\Throwable) {
                continue;
            }
        }

        return '-';
    }

    private function normalizeDateValue(mixed $candidate): ?string
    {
        $value = $this->scalarValue($candidate);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    /**
     * TRF collection/sample fields: prefer the sample's TRF value, otherwise "NP".
     * Does not fall back to batch/shared values.
     */
    private function trfValueOrNp(mixed ...$candidates): string
    {
        return $this->firstNonEmptyFromMixed(...$candidates) ?? 'NP';
    }

    /**
     * Append °C when a TRF temperature value is present and does not already include it.
     */
    private function formatSampleTemperatureForReport(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '' || ! $this->isReportValuePresent($trimmed) || strcasecmp($trimmed, 'NP') === 0) {
            return $trimmed === '' ? 'NP' : $trimmed;
        }

        if (preg_match('/(?:°\s*C|℃)\s*$/iu', $trimmed) === 1) {
            return preg_replace('/\s*(?:°\s*C|℃)\s*$/iu', ' °C', $trimmed) ?? ($trimmed.' °C');
        }

        if (preg_match('/\bC\s*$/u', $trimmed) === 1 && preg_match('/°/u', $trimmed) !== 1) {
            return preg_replace('/\s*C\s*$/u', ' °C', $trimmed) ?? ($trimmed.' °C');
        }

        return $trimmed.' °C';
    }

    private function trfDateOrNp(mixed ...$candidates): string
    {
        $formatted = $this->formatReportDate(...$candidates);

        return $formatted === '-' ? 'NP' : $formatted;
    }

    /**
     * Ensure per-sample collection fields from indexed TRF values are present on each sample row.
     *
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, mixed>  $formData
     * @return list<array<string, mixed>>
     */
    private function enrichSampleRowsWithIndexedCollectionFields(array $rows, array $formData): array
    {
        $collectionFields = [
            'date_received',
            'sampling_date',
            'sampling_time',
            'sampling_location',
            'transport_condition',
            'method_of_sampling',
            'sampling_apparatus',
            'reason_of_collection',
            'thermometer_id',
            'sample_temp',
            'field_sample_temp',
            'sampling_point_manual',
            'packaging',
            'additional_details',
            'sampled_by',
        ];

        $rowCount = max(count($rows), 1);
        foreach ($collectionFields as $field) {
            if (! array_key_exists($field, $formData)) {
                continue;
            }

            $indexed = $formData[$field];
            if (! is_array($indexed)) {
                if ($rows === []) {
                    $rows[0] = [];
                }
                if (! $this->isReportValuePresent($rows[0][$field] ?? null)) {
                    $rows[0][$field] = $indexed;
                }

                continue;
            }

            $values = $indexed;
            $maxIndex = max(array_keys($values) ?: [0]);
            $rowCount = max($rowCount, ((int) $maxIndex) + 1);

            for ($index = 0; $index < $rowCount; $index++) {
                if (! isset($rows[$index]) || ! is_array($rows[$index])) {
                    $rows[$index] = [];
                }

                if ($this->isReportValuePresent($rows[$index][$field] ?? null)) {
                    continue;
                }

                if (array_key_exists($index, $values)) {
                    $rows[$index][$field] = $values[$index];

                    continue;
                }

                $sequential = array_values($values);
                if (array_key_exists($index, $sequential)) {
                    $rows[$index][$field] = $sequential[$index];
                }
            }
        }

        return array_values($rows);
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function normalizeReportAdditionalDetails(mixed ...$candidates): array
    {
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                $decoded = json_decode($candidate, true);
                $candidate = is_array($decoded) ? $decoded : [];
            }

            if (! is_array($candidate) || $candidate === []) {
                continue;
            }

            $details = [];
            foreach ($candidate as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $label = trim((string) ($row['label'] ?? ''));
                $value = trim((string) ($row['value'] ?? ''));
                if ($label === '' || $value === '') {
                    continue;
                }

                $details[] = [
                    'label' => $label,
                    'value' => $value,
                ];
            }

            if ($details !== []) {
                return $details;
            }
        }

        return [];
    }

    /**
     * @param  list<array{left: array{label: string, value: string, emphasize?: bool}, right: array{label: string, value: string, emphasize?: bool}|null}>  $rows
     * @param  list<array{label: string, value: string}>  $additionalDetails
     * @return list<array{left: array{label: string, value: string, emphasize?: bool}, right: array{label: string, value: string, emphasize?: bool}|null}>
     */
    private function appendAdditionalDetailRows(array $rows, array $additionalDetails): array
    {
        $pending = [];
        foreach ($additionalDetails as $detail) {
            $label = trim((string) ($detail['label'] ?? ''));
            $value = trim((string) ($detail['value'] ?? ''));
            if ($label === '' || $value === '') {
                continue;
            }

            $pending[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        for ($index = 0, $count = count($pending); $index < $count; $index += 2) {
            $rows[] = [
                'left' => $pending[$index],
                'right' => $pending[$index + 1] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * @param  mixed  $checkboxGroup  Normalized checkbox map or raw TRF value
     */
    private function selectedCheckboxLabels(mixed $checkboxGroup): string
    {
        if (! is_array($checkboxGroup)) {
            return $this->formatReportOptionListDisplay($this->scalarValue($checkboxGroup));
        }

        if (array_keys($checkboxGroup) !== range(0, count($checkboxGroup) - 1)) {
            $selected = [];
            foreach ($checkboxGroup as $label => $checked) {
                if ($checked) {
                    $selected[] = (string) $label;
                }
            }

            return $this->formatReportOptionListDisplay(implode(', ', $selected));
        }

        return $this->formatReportOptionListDisplay($this->scalarValue($checkboxGroup));
    }

    private function scalarValue(mixed $value): string
    {
        if ($value === null || $value === false) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : '';
        }

        if (is_array($value)) {
            $selected = TestRequestFormReportDataBuilder::normalizeToSelectedList($value);

            return implode(', ', $selected);
        }

        return trim((string) $value);
    }

    private function firstNonEmptyFromMixed(mixed ...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $value = $this->scalarValue($candidate);
            if ($value !== '' && $value !== '-') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Test Request Report letterhead: company name (from Companies), then postal address, T and W.
     *
     * @return array{name: string, lines: list<string>}
     */
    private function buildCompanyLetterhead(?object $company): array
    {
        $name = trim((string) ($company->name ?? ''));
        $lines = [];

        $address = trim((string) ($company->address ?? ''));
        if ($address !== '') {
            array_push($lines, ...$this->formatReportLetterheadAddressLines($address));
        }

        $telephone = trim((string) ($company->telephone ?? $company->telephone1 ?? ''));
        if ($telephone !== '') {
            $lines[] = 'T: '.$telephone;
        }

        $website = trim((string) ($company->website ?? ''));
        if ($website !== '') {
            $lines[] = 'W: '.$website;
        }

        if ($name !== '') {
            $lines = array_values(array_filter(
                $lines,
                static fn (string $line): bool => strcasecmp($line, $name) !== 0,
            ));
        }

        return [
            'name' => $name !== '' ? $name : 'AmSpec',
            'lines' => $lines,
        ];
    }

    /**
     * @return list<string>
     */
    private function formatReportLetterheadAddressLines(string $address): array
    {
        $address = trim($address);
        if ($address === '') {
            return [];
        }

        $parts = array_values(array_filter(
            array_map('trim', explode(',', $address)),
            static fn (string $part): bool => $part !== '',
        ));

        if (count($parts) === 6) {
            return [
                $parts[0].', '.$parts[1].',',
                $parts[2].', '.$parts[3].',',
                $parts[4].', '.$parts[5],
            ];
        }

        if (count($parts) === 3) {
            return $parts;
        }

        return [$address];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CapturedResult>  $capturedResults
     * @return \Illuminate\Support\Collection<string, User>
     */
    private function analystUsersById($capturedResults)
    {
        $userIds = [];

        foreach ($capturedResults as $result) {
            $userId = trim((string) ($result->user_id ?? ''));
            if ($userId !== '') {
                $userIds[$userId] = true;
            }

            $assigned = $result->assigned_analyst_ids ?? [];
            if (! is_array($assigned)) {
                continue;
            }

            foreach ($assigned as $assignedId) {
                $assignedId = trim((string) $assignedId);
                if ($assignedId !== '') {
                    $userIds[$assignedId] = true;
                }
            }
        }

        if ($userIds === []) {
            return collect();
        }

        return User::query()
            ->whereIn('id', array_keys($userIds))
            ->get(['id', 'name', 'id_number'])
            ->keyBy('id');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CapturedResult>  $sampleResults
     * @param  \Illuminate\Support\Collection<string, User>  $usersById
     */
    private function conductedByNames($sampleResults, $usersById): string
    {
        $ids = [];

        foreach ($sampleResults as $result) {
            $userId = trim((string) ($result->user_id ?? ''));
            if ($userId !== '') {
                $ids[$userId] = true;
            }

            $assigned = $result->assigned_analyst_ids ?? [];
            if (! is_array($assigned)) {
                continue;
            }

            foreach ($assigned as $assignedId) {
                $assignedId = trim((string) $assignedId);
                if ($assignedId !== '') {
                    $ids[$assignedId] = true;
                }
            }
        }

        $names = [];
        foreach (array_keys($ids) as $userId) {
            $user = $usersById->get($userId) ?? $sampleResults->firstWhere('user_id', $userId)?->user;
            if ($user === null) {
                continue;
            }

            $name = trim((string) ($user->name ?? ''));
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return implode(', ', array_values(array_unique($names)));
    }
}
