<?php

namespace App\Services\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\AnalysisElements;
use App\AnalysisType;
use App\SampleType;
use App\Services\SubmissionForm\RequestViewPagePresenter;
use App\Services\SubmissionForm\SubmissionFormSchemaHelper;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Support\Str;

final class SampleIntegrityCheckService
{
    public function __construct(
        private AcceptanceFormSampleConfigService $configService,
        private AcceptanceFormPricingService $pricingService,
        private SubcontractingAssignmentService $subcontractingAssignmentService,
        private SubmissionRequestSampleLineService $sampleLineService,
    ) {}

    /**
     * @return list<array{
     *     row_key: string,
     *     config_id: string,
     *     sample_label: string,
     *     element_id: string,
     *     test_label: string,
     *     lab_section_ids: list<string>,
     *     analysts_by_lab_section: array<string, list<string>>,
     *     subcontracted: bool
     * }>
     */
    public function buildTestRows(SampleSubmissionRequest $enquiry, ?SubmissionFormInstance $instance = null): array
    {
        $configs = $this->resolveConfigs($enquiry, $instance);
        $subcontractedIds = array_flip($this->subcontractingAssignmentService->resolveSubcontractedElementIds($enquiry));
        $elementIds = collect($configs)
            ->flatMap(fn (array $config): array => is_array($config['parameter_keys'] ?? null)
                ? $config['parameter_keys']
                : [])
            ->map(fn (mixed $id): string => (string) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $elementsById = AnalysisElements::query()
            ->with([
                'analyte:id,name,code',
                'operator:id,name',
                'mmethod',
                'ltmethod',
                'analysis_type.sample_type',
                'equipment:id,name,equipment_number',
                'formular:id,name',
                'methodSequence:id,name',
                'reportingUnit:id,name',
            ])
            ->whereIn('id', $elementIds)
            ->get([
                'id',
                'analyte_id',
                'operator_id',
                'lab_section_id',
                'analysis_type_id',
                'reporting_time',
                'method',
                'lod',
                'hod',
                'reporting_unit',
                'equipment_id',
                'formular_id',
                'has_method_sequence',
                'method_sequence_id',
                'measurement_uncertainty',
                'non_accredited',
            ])
            ->keyBy(fn (AnalysisElements $element): string => (string) $element->id);
        $matchedSamplesByConfigIndex = $this->matchedTrfSamplesByConfigIndex($instance, $enquiry, $configs);
        $rows = [];

        foreach ($configs as $configIndex => $config) {
            $config = $this->configService->syncParameterLabSections($config);
            $configId = (string) ($config['id'] ?? Str::uuid());
            $matchedSample = $matchedSamplesByConfigIndex[$configIndex] ?? null;
            $sampleDescription = $this->sampleDescriptionFromConfig(
                $config,
                is_array($matchedSample) ? $matchedSample : null,
            );
            $sampleLabel = $this->sampleExportLabel($configIndex, $sampleDescription);
            $sampleLabelDisplay = $this->sampleDisplayLabel($configIndex, $sampleDescription);
            $sampleTestCategory = $this->testCategoryFromConfig($config);
            if ($sampleTestCategory === '' && is_array($matchedSample)) {
                $sampleTestCategory = $this->pdfTestCategoryRequirement($matchedSample);
            }
            $sampleTypeName = $this->sampleTypeNameFromConfig($config);
            $analysisTypeName = $this->analysisTypeNameFromConfig($config);
            $parameterKeys = array_values(array_filter(array_map(
                'strval',
                is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : []
            )));
            $sectionMap = is_array($config['parameter_lab_sections'] ?? null)
                ? $config['parameter_lab_sections']
                : [];
            $analystsByElement = is_array($config['analysts_by_element'] ?? null)
                ? $config['analysts_by_element']
                : [];
            $configSubcontracted = array_flip($this->configService->normalizeSubcontractedParameterKeys(
                $config['subcontracted_parameter_keys'] ?? [],
                $parameterKeys,
            ));

            foreach ($parameterKeys as $elementId) {
                $sectionIds = $this->configService->normalizeLabSectionIds($sectionMap[$elementId] ?? null);
                $element = $elementsById->get($elementId);
                // Analysts are assigned to the test (element), not shared across a lab section.
                // Prefer per-element saves; do not fall back to config-level analysts_by_lab_section.
                $hasSavedElementAssignments = array_key_exists($elementId, $analystsByElement)
                    && is_array($analystsByElement[$elementId]);
                $sectionAnalysts = [];
                foreach ($sectionIds as $sectionId) {
                    $sectionAnalysts[$sectionId] = $hasSavedElementAssignments
                        ? array_values(array_map(
                            'strval',
                            is_array($analystsByElement[$elementId][$sectionId] ?? null)
                                ? $analystsByElement[$elementId][$sectionId]
                                : []
                        ))
                        : [];
                }

                $defaultOperatorId = trim((string) ($element?->operator_id ?? ''));
                $hasAnyAssignedAnalyst = false;
                foreach ($sectionAnalysts as $assigned) {
                    if ($assigned !== []) {
                        $hasAnyAssignedAnalyst = true;
                        break;
                    }
                }

                // Auto-pick the analysis-element operator onto the test when none are assigned yet.
                // Users can still add/remove analysts afterward.
                if (! $hasAnyAssignedAnalyst && $defaultOperatorId !== '' && $sectionIds !== []) {
                    $operatorSectionId = in_array((string) ($element?->lab_section_id ?? ''), $sectionIds, true)
                        ? (string) $element->lab_section_id
                        : $sectionIds[0];
                    $sectionAnalysts[$operatorSectionId] = [$defaultOperatorId];
                }

                $testLabel = trim((string) ($element?->analyte?->name ?? ''));
                if ($testLabel === '') {
                    $testLabel = trim((string) ($element?->analyte?->code ?? ''));
                }

                $elementAnalysisType = $element?->analysis_type;
                $rowSampleType = trim((string) ($elementAnalysisType?->sample_type?->name ?? ''));
                if ($rowSampleType === '') {
                    $rowSampleType = $sampleTypeName;
                }
                $rowAnalysisType = trim((string) ($elementAnalysisType?->name ?? ''));
                if ($rowAnalysisType === '') {
                    $rowAnalysisType = $analysisTypeName;
                }

                $rows[] = [
                    'row_key' => $configId.'|'.$elementId,
                    'config_id' => $configId,
                    'sample_index' => $configIndex + 1,
                    'sample_description' => $sampleDescription,
                    'sample_label' => $sampleLabel,
                    'sample_label_display' => $sampleLabelDisplay,
                    'customer_sample_id' => trim((string) ($config['customer_sample_id'] ?? '')),
                    'element_id' => $elementId,
                    'test_label' => $testLabel !== '' ? $testLabel : 'Parameter',
                    'tat' => trim((string) ($element?->reporting_time ?? '')),
                    'method' => $this->elementMethodName($element),
                    'sample_type' => $rowSampleType,
                    'analysis_type' => $rowAnalysisType,
                    'test_category' => $sampleTestCategory,
                    'lab_section_ids' => $sectionIds,
                    'analysts_by_lab_section' => $sectionAnalysts,
                    'default_operator_id' => $defaultOperatorId !== '' ? $defaultOperatorId : null,
                    'default_operator_name' => trim((string) ($element?->operator?->name ?? '')),
                    'subcontracted' => isset($configSubcontracted[$elementId]) || isset($subcontractedIds[$elementId]),
                    'test_info' => $this->testInfoFromElement($element, $rowSampleType, $rowAnalysisType, $sampleTestCategory),
                ];
            }
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function resolveConfigs(SampleSubmissionRequest $enquiry, ?SubmissionFormInstance $instance = null): array
    {
        if (is_array($enquiry->enquiry_sample_configuration) && $enquiry->enquiry_sample_configuration !== []) {
            $configs = $this->configService->flattenToPerSampleConfigs($enquiry->enquiry_sample_configuration);
            $configs = $this->configService->normalizeConfigsAnalysisTypeIds($configs);

            return $this->configService->syncParameterLabSectionsForConfigs($configs);
        }

        $prefill = $this->pricingService->buildPrefillFromSelection(
            (string) $enquiry->id,
            $instance?->id !== null ? (string) $instance->id : null,
        );
        $quotationLines = $this->pricingService->deduplicateRedundantAnalysisTypeLines($prefill['lines'] ?? []);

        if (! empty($prefill['quotation_locked']) && $quotationLines !== []) {
            return $this->configService->prepareAcceptanceConfigsFromQuotation(
                $enquiry,
                $quotationLines,
                $instance,
            );
        }

        return [];
    }

    /**
     * Persist Integrity grid edits into enquiry_sample_configuration (including subcontract flags).
     *
     * @param  list<array{
     *     config_id: string,
     *     element_id: string,
     *     lab_section_ids: list<string>,
     *     analysts_by_lab_section: array<string, list<string>>,
     *     subcontracted: bool
     * }>  $rows
     */
    public function persistIntegrityAssignments(SampleSubmissionRequest $enquiry, array $rows): SampleSubmissionRequest
    {
        $configs = $this->resolveConfigs($enquiry, $enquiry->submissionFormInstance);
        $configsById = [];
        foreach ($configs as $config) {
            $configsById[(string) ($config['id'] ?? '')] = $config;
        }

        /** @var array<string, true> $touchedConfigIds */
        $touchedConfigIds = [];
        /** @var array<string, array<string, array<string, list<string>>>> $analystsByElementByConfig */
        $analystsByElementByConfig = [];
        /** @var array<string, array<string, list<string>>> $analystsBySectionByConfig */
        $analystsBySectionByConfig = [];

        foreach ($rows as $row) {
            $configId = (string) ($row['config_id'] ?? '');
            $elementId = (string) ($row['element_id'] ?? '');
            if ($configId === '' || $elementId === '' || ! isset($configsById[$configId])) {
                continue;
            }

            $touchedConfigIds[$configId] = true;

            $sectionIds = $this->configService->normalizeLabSectionIds($row['lab_section_ids'] ?? []);
            $sections = is_array($configsById[$configId]['parameter_lab_sections'] ?? null)
                ? $configsById[$configId]['parameter_lab_sections']
                : [];
            $sections[$elementId] = $sectionIds;
            $configsById[$configId]['parameter_lab_sections'] = $sections;

            $incomingAnalysts = is_array($row['analysts_by_lab_section'] ?? null)
                ? $row['analysts_by_lab_section']
                : [];
            $elementAssignments = [];
            foreach ($sectionIds as $sectionId) {
                $incoming = array_values(array_unique(array_filter(array_map(
                    'strval',
                    is_array($incomingAnalysts[$sectionId] ?? null) ? $incomingAnalysts[$sectionId] : []
                ))));
                $elementAssignments[$sectionId] = $incoming;

                if (! empty($row['subcontracted'])) {
                    continue;
                }

                $analystsBySectionByConfig[$configId][$sectionId] = array_values(array_unique([
                    ...($analystsBySectionByConfig[$configId][$sectionId] ?? []),
                    ...$incoming,
                ]));
            }
            $analystsByElementByConfig[$configId][$elementId] = $elementAssignments;
        }

        foreach (array_keys($touchedConfigIds) as $configId) {
            $configsById[$configId]['analysts_by_element'] = $analystsByElementByConfig[$configId] ?? [];
            $configsById[$configId]['analysts_by_lab_section'] = $analystsBySectionByConfig[$configId] ?? [];
        }

        $normalized = $this->configService->normalizeConfigsForStorage(
            array_values($configsById),
            $enquiry->crm_customer_id !== null ? (string) $enquiry->crm_customer_id : null,
        );

        $enquiry->enquiry_sample_configuration = $normalized;
        $enquiry->save();

        $instanceId = trim((string) (
            $enquiry->submissionFormInstance?->id
            ?? $enquiry->submission_form_instance_id
            ?? ''
        ));

        if ($instanceId !== '' && $touchedConfigIds !== []) {
            app(SampleWorkflowEventRecorder::class)->record(
                subjectType: SampleSubmissionRequest::class,
                subjectId: (string) $enquiry->id,
                eventType: 'integrity_assignments_saved',
                what: 'Integrity check assignments saved',
                how: 'Assignment grid',
                where: 'Sample Integrity Check',
                instanceId: $instanceId,
                metadata: [
                    'samples_touched' => count($touchedConfigIds),
                    'tests_touched' => count($rows),
                ],
            );
        }

        return $this->persistSubcontractedAssignments($enquiry->fresh() ?? $enquiry, $rows);
    }

    /**
     * Persist Integrity subcontract flags onto enquiry_sample_configuration only
     * (quotations are not associated with subcontracting).
     *
     * @param  list<array{
     *     config_id?: string,
     *     element_id?: string,
     *     subcontracted?: bool
     * }>  $rows
     */
    public function persistSubcontractedAssignments(SampleSubmissionRequest $enquiry, array $rows): SampleSubmissionRequest
    {
        $configs = is_array($enquiry->enquiry_sample_configuration)
            ? $this->configService->flattenToPerSampleConfigs($enquiry->enquiry_sample_configuration)
            : [];

        if ($configs === []) {
            return $enquiry;
        }

        $configsById = [];
        foreach ($configs as $config) {
            $configsById[(string) ($config['id'] ?? '')] = $config;
        }

        /** @var array<string, list<string>> $subcontractedByConfig */
        $subcontractedByConfig = [];
        /** @var array<string, true> $touched */
        $touched = [];

        foreach ($rows as $row) {
            $configId = (string) ($row['config_id'] ?? '');
            $elementId = (string) ($row['element_id'] ?? '');
            if ($configId === '' || $elementId === '' || ! isset($configsById[$configId])) {
                continue;
            }

            $touched[$configId] = true;
            if (! empty($row['subcontracted'])) {
                $subcontractedByConfig[$configId][] = $elementId;
            }
        }

        if ($touched === []) {
            return $enquiry;
        }

        foreach (array_keys($touched) as $configId) {
            $parameterKeys = array_values(array_map(
                'strval',
                is_array($configsById[$configId]['parameter_keys'] ?? null)
                    ? $configsById[$configId]['parameter_keys']
                    : []
            ));
            $configsById[$configId]['subcontracted_parameter_keys'] = $this->configService
                ->normalizeSubcontractedParameterKeys(
                    $subcontractedByConfig[$configId] ?? [],
                    $parameterKeys,
                );
        }

        $normalized = $this->configService->normalizeConfigsForStorage(
            array_values($configsById),
            $enquiry->crm_customer_id !== null ? (string) $enquiry->crm_customer_id : null,
        );

        $enquiry->enquiry_sample_configuration = $normalized;
        $enquiry->save();

        return $enquiry->fresh() ?? $enquiry;
    }

    /**
     * Merge element-level subcontract flags into enquiry sample configuration.
     * Used when Process Enquiry / quotation lines carry a subcontracted toggle.
     *
     * @param  list<string>  $subcontractedElementIds
     */
    public function syncSubcontractedElementIdsOntoEnquiry(
        SampleSubmissionRequest $enquiry,
        array $subcontractedElementIds,
    ): SampleSubmissionRequest {
        $flagged = array_flip(array_values(array_filter(array_map(
            static fn (mixed $id): string => trim((string) $id),
            $subcontractedElementIds,
        ), static fn (string $id): bool => $id !== '')));

        $configs = is_array($enquiry->enquiry_sample_configuration)
            ? $this->configService->flattenToPerSampleConfigs($enquiry->enquiry_sample_configuration)
            : [];

        if ($configs === []) {
            return $enquiry;
        }

        foreach ($configs as $index => $config) {
            $parameterKeys = array_values(array_map(
                'strval',
                is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : []
            ));
            $configs[$index]['subcontracted_parameter_keys'] = $this->configService
                ->normalizeSubcontractedParameterKeys(
                    array_values(array_filter(
                        $parameterKeys,
                        static fn (string $id): bool => isset($flagged[$id])
                    )),
                    $parameterKeys,
                );
        }

        $enquiry->enquiry_sample_configuration = $this->configService->normalizeConfigsForStorage(
            $configs,
            $enquiry->crm_customer_id !== null ? (string) $enquiry->crm_customer_id : null,
        );
        $enquiry->save();

        return $enquiry->fresh() ?? $enquiry;
    }

    /**
     * Test & sample information keyed by enquiry sample config id (integrity rail sample key).
     *
     * @param  list<array<string, mixed>>  $testRows
     * @return array<string, array{
     *     label: string,
     *     sample_details: string,
     *     details: list<array{label: string, value: string}>,
     *     tests: list<string>
     * }>
     */
    public function sampleInfoByConfigKey(
        SubmissionFormInstance $instance,
        ?SampleSubmissionRequest $enquiry,
        array $testRows,
    ): array {
        if ($enquiry === null) {
            return [];
        }

        $configs = $this->resolveConfigs($enquiry, $instance);
        $matchedSamplesByConfigIndex = $this->matchedTrfSamplesByConfigIndex($instance, $enquiry, $configs);

        /** @var array<string, list<string>> $testsByConfig */
        $testsByConfig = [];
        foreach ($testRows as $row) {
            $configId = (string) ($row['config_id'] ?? '');
            $testLabel = trim((string) ($row['test_label'] ?? ''));
            if ($configId === '' || $testLabel === '') {
                continue;
            }

            $testsByConfig[$configId][] = $testLabel;
        }

        foreach ($testsByConfig as $configId => $tests) {
            $testsByConfig[$configId] = array_values(array_unique($tests));
        }

        $info = [];

        foreach ($configs as $configIndex => $config) {
            $configId = (string) ($config['id'] ?? '');
            if ($configId === '') {
                continue;
            }

            $matched = $matchedSamplesByConfigIndex[$configIndex] ?? null;

            $details = is_array($matched) && is_array($matched['details'] ?? null)
                ? $matched['details']
                : [];
            $sampleDetails = '';
            if (is_array($matched)) {
                $sampleDetails = trim((string) ($matched['sample_description'] ?? ''));
                if ($sampleDetails === '' || $sampleDetails === '—') {
                    $sampleDetails = trim(strip_tags((string) ($matched['sample_description_html'] ?? '')));
                }
            }

            $details = array_values(array_filter(
                $details,
                static fn (array $field): bool => ! in_array(
                    mb_strtolower(trim((string) ($field['label'] ?? ''))),
                    ['sample description', 'tests'],
                    true
                )
            ));

            $tests = $testsByConfig[$configId] ?? [];

            if ($tests === [] && is_array($matched) && is_array($matched['test_codes'] ?? null)) {
                $tests = array_values(array_filter(array_map(
                    static fn (mixed $code): string => trim((string) $code),
                    $matched['test_codes']
                ), static fn (string $code): bool => $code !== ''));
            }

            $info[$configId] = [
                'label' => $this->sampleDisplayLabel($configIndex, $sampleDetails),
                'export_label' => $this->sampleExportLabel($configIndex, $sampleDetails),
                'sample_details' => $sampleDetails,
                'details' => $details,
                'tests' => $tests,
            ];
        }

        return $info;
    }

    /**
     * Verify-first dossier for the integrity console middle pane.
     *
     * @param  list<array<string, mixed>>  $testRows
     * @return array{
     *     display_label: string,
     *     export_label: string,
     *     customer_sample_id: string,
     *     condition_name: string,
     *     condition_not_acceptable: bool,
     *     customer: list<array{label: string, value: string}>,
     *     collection: list<array{label: string, value: string}>,
     *     sample_info: list<array{label: string, value: string}>,
     *     sample_tests: list<string>,
     *     identity: list<array{label: string, value: string}>,
     *     tests: list<array{
     *         test: string,
     *         tat: string,
     *         method: string,
     *         sample_type: string,
     *         analysis_type: string,
     *         test_category: string,
     *         subcontracted: bool
     *     }>
     * }
     */
    public function sampleDossierForConfig(
        string $configId,
        SubmissionFormInstance $instance,
        ?SampleSubmissionRequest $enquiry,
        array $testRows,
    ): array {
        $empty = [
            'display_label' => '',
            'export_label' => '',
            'customer_sample_id' => '',
            'condition_name' => '',
            'condition_not_acceptable' => false,
            'client_title' => 'Client',
            'customer' => [],
            'customer_card' => [
                'client_name' => '',
                'contact_person' => '',
                'email' => '',
                'mobile' => '',
                'address' => '',
            ],
            'collection' => [],
            'sample_info' => [],
            'sample_tests' => [],
            'identity' => [],
            'tests' => [],
        ];

        if ($configId === '' || $enquiry === null) {
            return $empty;
        }

        $catalog = $this->integrityPdfCatalog($instance, $enquiry);
        $info = $this->sampleInfoByConfigKey($instance, $enquiry, $testRows)[$configId] ?? null;
        $configs = $this->resolveConfigs($enquiry, $instance);
        $configIndex = null;
        foreach ($configs as $index => $config) {
            if ((string) ($config['id'] ?? '') === $configId) {
                $configIndex = $index;
                break;
            }
        }

        $conditionByConfigId = TrfLabUseFieldsService::conditionNameByConfigId($configs);
        $conditionName = trim((string) ($conditionByConfigId[$configId] ?? ''));

        $sampleInfo = [];
        if ($configIndex !== null && isset($catalog['samples'][$configIndex])) {
            $catalogSample = $catalog['samples'][$configIndex];
            foreach (is_array($catalogSample['fields'] ?? null) ? $catalogSample['fields'] : [] as $field) {
                $sampleInfo[] = [
                    'label' => (string) ($field['label'] ?? ''),
                    'value' => (string) ($field['value'] ?? ''),
                ];
            }
            $customerSampleIdFromCatalog = trim((string) ($catalogSample['customer_sample_id'] ?? ''));
            if ($customerSampleIdFromCatalog !== '' && ! $this->identityHasLabel($sampleInfo, 'Customer sample ID')) {
                array_unshift($sampleInfo, [
                    'label' => 'Customer sample ID',
                    'value' => $customerSampleIdFromCatalog,
                ]);
            }
        }

        foreach (is_array($info['details'] ?? null) ? $info['details'] : [] as $field) {
            $label = trim((string) ($field['label'] ?? ''));
            $value = trim((string) ($field['value'] ?? ''));
            if ($label === '' || $value === '' || $value === '—') {
                continue;
            }
            if ($this->identityHasLabel($sampleInfo, $label)) {
                continue;
            }
            $sampleInfo[] = ['label' => $label, 'value' => $value];
        }

        $sampleInfo = $this->catalogFields($sampleInfo);
        $sampleTests = array_values(array_filter(array_unique(array_map(
            static fn (mixed $test): string => trim((string) $test),
            is_array($info['tests'] ?? null) ? $info['tests'] : []
        )), static fn (string $test): bool => $test !== ''));

        $identityLabels = [
            'customer sample id' => 'Customer sample ID',
            'sample type' => 'Sample type',
            'qty / unit' => 'Qty / unit',
            'sampling point' => 'Sampling point',
            'sampling point / location' => 'Sampling point',
            'state of sample' => 'State of sample',
            'batch number' => 'Batch number',
        ];
        $identity = [];
        foreach (is_array($info['details'] ?? null) ? $info['details'] : [] as $field) {
            $normalized = mb_strtolower(trim((string) ($field['label'] ?? '')));
            if (! isset($identityLabels[$normalized])) {
                continue;
            }
            $value = trim((string) ($field['value'] ?? ''));
            if ($value === '' || $value === '—') {
                continue;
            }
            $identity[] = [
                'label' => $identityLabels[$normalized],
                'value' => $value,
            ];
        }

        $customerSampleId = '';
        foreach ($testRows as $row) {
            if ((string) ($row['config_id'] ?? '') !== $configId) {
                continue;
            }
            $customerSampleId = trim((string) ($row['customer_sample_id'] ?? ''));
            break;
        }

        if ($customerSampleId !== '' && ! $this->identityHasLabel($identity, 'Customer sample ID')) {
            array_unshift($identity, [
                'label' => 'Customer sample ID',
                'value' => $customerSampleId,
            ]);
        }

        $tests = [];
        foreach ($testRows as $row) {
            if ((string) ($row['config_id'] ?? '') !== $configId) {
                continue;
            }

            $tests[] = [
                'test' => (string) ($row['test_label'] ?? '—'),
                'tat' => trim((string) ($row['tat'] ?? '')),
                'method' => trim((string) ($row['method'] ?? '')),
                'sample_type' => trim((string) ($row['sample_type'] ?? '')),
                'analysis_type' => trim((string) ($row['analysis_type'] ?? '')),
                'test_category' => trim((string) ($row['test_category'] ?? '')),
                'subcontracted' => ! empty($row['subcontracted']),
            ];
        }

        $description = trim((string) ($info['sample_details'] ?? ''));
        if ($description === '') {
            $description = trim((string) ($info['export_label'] ?? ''));
        }

        $clientTitle = 'Client';
        $clientFieldsByLabel = [];
        foreach ($catalog['client'] as $field) {
            $label = trim((string) ($field['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $clientFieldsByLabel[mb_strtolower($label)] = trim((string) ($field['value'] ?? ''));
            if (strcasecmp($label, 'Client name') === 0) {
                $title = trim((string) ($field['value'] ?? ''));
                if ($title !== '') {
                    $clientTitle = $title;
                }
            }
        }

        $sampleInfo = array_values(array_filter(
            $sampleInfo,
            static function (array $field): bool {
                $label = mb_strtolower(trim((string) ($field['label'] ?? '')));

                return $label !== ''
                    && ! str_contains($label, 'sampling point')
                    && ! str_contains($label, 'sampling location');
            }
        ));

        return [
            'display_label' => $description !== '' ? $description : 'Sample',
            'sample_description' => $description,
            'export_label' => (string) ($info['export_label'] ?? ($info['label'] ?? 'Sample')),
            'customer_sample_id' => $customerSampleId,
            'condition_name' => $conditionName,
            'condition_not_acceptable' => TrfLabUseFieldsService::isNotAcceptableConditionName($conditionName),
            'client_title' => $clientTitle,
            'customer' => $catalog['client'],
            'customer_card' => $this->customerCardForSampleInfoModal($instance, $enquiry, $clientFieldsByLabel, $clientTitle),
            'collection' => $catalog['collection'],
            'sample_info' => $sampleInfo,
            'sample_tests' => $sampleTests,
            'identity' => $identity,
            'tests' => $tests,
        ];
    }

    /**
     * Compact customer block for the sample info modal (Client | Contact person).
     *
     * @param  array<string, string>  $clientFieldsByLabel
     * @return array{
     *     client_name: string,
     *     contact_person: string,
     *     email: string,
     *     mobile: string,
     *     address: string
     * }
     */
    private function customerCardForSampleInfoModal(
        SubmissionFormInstance $instance,
        ?SampleSubmissionRequest $enquiry,
        array $clientFieldsByLabel,
        string $clientTitle,
    ): array {
        $instance->loadMissing(['crmCustomer.mainContact']);
        $crm = $instance->crmCustomer ?? $enquiry?->customer;
        if ($crm !== null) {
            $crm->loadMissing(['mainContact']);
        }

        $mainContact = $crm?->mainContact;
        $contactPerson = $this->firstNonEmptyString([
            $clientFieldsByLabel['customer contact name'] ?? '',
            (string) ($crm?->contact_person ?? ''),
            trim(implode(' ', array_filter([
                (string) ($mainContact?->first_name ?? ''),
                (string) ($mainContact?->middle_name ?? ''),
                (string) ($mainContact?->last_name ?? ''),
            ]))),
            (string) ($mainContact?->name ?? ''),
        ]);

        $email = $this->firstNonEmptyString([
            $clientFieldsByLabel['customer contact email'] ?? '',
            $clientFieldsByLabel['email'] ?? '',
            (string) ($mainContact?->email ?? ''),
            (string) ($crm?->email ?? ''),
        ]);

        $mobile = $this->firstNonEmptyString([
            $clientFieldsByLabel['customer contact phone'] ?? '',
            $clientFieldsByLabel['mobile number'] ?? '',
            $clientFieldsByLabel['mobile'] ?? '',
            (string) ($mainContact?->mobile ?? ''),
            (string) ($mainContact?->telephone ?? ''),
            (string) ($crm?->telephone1 ?? ''),
        ]);

        $address = $this->firstNonEmptyString([
            (string) ($crm?->physical_address ?? ''),
            (string) ($crm?->postal_address ?? ''),
            (string) ($crm?->billing_address ?? ''),
            $clientFieldsByLabel['address'] ?? '',
        ]);

        $clientName = $this->firstNonEmptyString([
            $clientFieldsByLabel['client name'] ?? '',
            $clientTitle !== 'Client' ? $clientTitle : '',
            (string) ($crm?->name ?? ''),
            (string) ($enquiry?->customer?->name ?? ''),
        ]);

        return [
            'client_name' => $clientName,
            'contact_person' => $contactPerson,
            'email' => $email,
            'mobile' => $mobile,
            'address' => $address,
        ];
    }

    /**
     * @param  list<string>  $candidates
     */
    private function firstNonEmptyString(array $candidates): string
    {
        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * @return array{fields: list<array{label: string, value: string, name: ?string}>, remarks: ?string}
     */
    public function requestInfoCard(SubmissionFormInstance $instance, ?SampleSubmissionRequest $enquiry): array
    {
        $instance->loadMissing(['submissionForm', 'crmCustomer', 'values.element']);
        $formData = $instance->getFormDataForDisplay();
        if (! is_array($formData)) {
            $formData = [];
        }

        $sampleLines = $this->sampleLineService->linesForInstance($instance);
        $presenter = new RequestViewPagePresenter(
            instance: $instance,
            submissionForm: $instance->submissionForm,
            commercialEnquiry: $enquiry,
            trfPdfUrl: null,
            canCreateSamples: false,
            linkedBatchesOutOfSyncWithForm: false,
            showSampleCollectionLabel: true,
            isTrfForm: false,
        );

        return $presenter->requestInfoCard($formData, is_array($sampleLines) ? $sampleLines : []);
    }

    /**
     * Categorized header sections for the integrity-check PDF.
     *
     * @return array{
     *     client: list<array{label: string, value: string}>,
     *     analysis: list<array{label: string, value: string}>,
     *     samples: list<array{label: string, customer_sample_id: string, fields: list<array{label: string, value: string}>}>,
     *     collection: list<array{label: string, value: string}>
     * }
     */
    public function integrityPdfCatalog(SubmissionFormInstance $instance, ?SampleSubmissionRequest $enquiry): array
    {
        $instance->loadMissing(['submissionForm', 'crmCustomer', 'values.element', 'crmCustomer']);
        $formData = $instance->getFormDataForDisplay();
        if (! is_array($formData)) {
            $formData = [];
        }

        $sampleLines = $this->sampleLineService->linesForInstance($instance);
        $presenter = new RequestViewPagePresenter(
            instance: $instance,
            submissionForm: $instance->submissionForm,
            commercialEnquiry: $enquiry,
            trfPdfUrl: null,
            canCreateSamples: false,
            linkedBatchesOutOfSyncWithForm: false,
            showSampleCollectionLabel: false,
            isTrfForm: false,
        );

        $requestInfo = $presenter->requestInfoCard($formData, is_array($sampleLines) ? $sampleLines : []);
        $testSamples = $presenter->testSamplesCard($sampleLines);
        $fieldsByName = [];
        foreach ($requestInfo['fields'] as $field) {
            $name = (string) ($field['name'] ?? '');
            if ($name !== '') {
                $fieldsByName[$name] = (string) ($field['value'] ?? '');
            }
        }

        $client = $this->catalogFields([
            ['label' => 'Client name', 'value' => $fieldsByName['client_name'] ?? (string) ($instance->crmCustomer?->name ?? $enquiry?->customer?->name ?? '')],
            ['label' => 'Address', 'value' => $fieldsByName['address'] ?? ''],
            ['label' => 'Tel / Fax no.', 'value' => $fieldsByName['tel_fax'] ?? ''],
            ['label' => 'Mobile number', 'value' => $fieldsByName['mobile'] ?? ''],
            ['label' => 'Email', 'value' => $fieldsByName['email'] ?? ''],
            ['label' => 'Customer contact name', 'value' => $fieldsByName['contact_name'] ?? ''],
            ['label' => 'Customer contact email', 'value' => $fieldsByName['contact_email'] ?? ''],
            ['label' => 'Customer contact phone', 'value' => $fieldsByName['contact_phone'] ?? ''],
        ]);

        $samples = [];
        foreach ($testSamples['samples'] as $index => $sample) {
            $customerSampleId = trim((string) ($sample['customer_sample_id'] ?? ''));
            if ($customerSampleId === '—') {
                $customerSampleId = '';
            }

            $analysisTypesForSample = is_array($sample['analysis_types'] ?? null)
                ? array_values(array_filter(array_map('strval', $sample['analysis_types'])))
                : [];
            $analysisType = $analysisTypesForSample !== []
                ? implode(', ', $analysisTypesForSample)
                : (string) ($sample['analysis_type'] ?? '');

            $samples[] = [
                'label' => $this->sampleExportLabel($index, $this->plainSampleDescription($sample)),
                'customer_sample_id' => $customerSampleId,
                'fields' => $this->catalogFields([
                    ['label' => 'Sample type', 'value' => (string) ($sample['sample_type'] ?? '')],
                    ['label' => 'Analysis type', 'value' => $analysisType],
                    ['label' => 'Test category / requirement', 'value' => $this->pdfTestCategoryRequirement($sample)],
                    ['label' => 'Qty / unit', 'value' => (string) ($sample['sample_quantity'] ?? '')],
                    ['label' => 'Batch number', 'value' => (string) ($sample['batch_number'] ?? '')],
                    ['label' => 'Production date', 'value' => (string) ($sample['production_date'] ?? '')],
                    ['label' => 'Expiration date', 'value' => (string) ($sample['expiry_date'] ?? '')],
                    ['label' => 'Sampling point', 'value' => (string) ($sample['sampling_point'] ?? '')],
                    ['label' => 'State of sample', 'value' => $this->stateOfSampleFromDetails($sample)],
                ]),
            ];
        }

        $collectionData = is_array($enquiry?->collection_data) ? $enquiry->collection_data : [];
        $collection = $this->catalogFields([
            ['label' => 'Sampling date', 'value' => $this->firstDisplayValue($instance, ['sampling_date', 'date_of_sampling', 'collection_date'], $collectionData['sampling_date'] ?? '')],
            ['label' => 'Sampling time', 'value' => $this->firstDisplayValue($instance, ['sampling_time', 'collection_time', 'time_of_collection'], $collectionData['sampling_time'] ?? '')],
            ['label' => 'Sampling location', 'value' => $this->firstDisplayValue($instance, ['sampling_location', 'sampling_point', 'location'], $collectionData['sampling_location'] ?? '')],
            ['label' => 'Method of sampling', 'value' => $this->stringifyMixed($collectionData['method_of_sampling'] ?? $this->firstDisplayValue($instance, ['method_of_sampling']))],
            ['label' => 'Reason of collection', 'value' => $this->stringifyMixed($collectionData['reason_of_collection'] ?? $this->firstDisplayValue($instance, ['reason_of_collection']))],
            ['label' => 'Transport condition', 'value' => $this->stringifyMixed($collectionData['transport_condition'] ?? $this->firstDisplayValue($instance, ['transport_condition']))],
            ['label' => 'Sampling apparatus', 'value' => $this->stringifyMixed($collectionData['sampling_apparatus'] ?? $this->firstDisplayValue($instance, ['sampling_apparatus']))],
            ['label' => 'Collected by', 'value' => $this->firstDisplayValue($instance, ['collected_by', 'sampled_by'])],
            ['label' => 'Received by', 'value' => $fieldsByName['received_by'] ?? (string) ($enquiry?->received_by_full_name ?? '')],
        ]);

        return [
            'client' => $client,
            'analysis' => [],
            'samples' => $samples,
            'collection' => $collection,
        ];
    }

    /**
     * Categories/requirements only — never analyte or parameter names.
     *
     * @param  array<string, mixed>  $sample
     */
    private function pdfTestCategoryRequirement(array $sample): string
    {
        $allowed = ['chemistry', 'microbiology', 'legionella'];
        $tokens = array_values(array_filter(
            SubmissionFormSchemaHelper::testCategoryTokens($sample['test_category'] ?? null),
            static fn (string $token): bool => in_array($token, $allowed, true),
        ));

        return SubmissionFormSchemaHelper::testCategoryLabel(implode(',', $tokens));
    }

    /**
     * @return array<string, string>
     */
    private function testInfoFromElement(
        ?AnalysisElements $element,
        string $sampleType,
        string $analysisType,
        string $testCategory,
    ): array {
        if ($element === null) {
            return [];
        }

        $equipmentName = trim((string) ($element->equipment?->name ?? ''));
        if ($equipmentName === '') {
            $equipmentName = trim((string) ($element->equipment?->equipment_number ?? ''));
        }

        $unit = trim((string) ($element->reporting_unit ?? ''));
        if ($unit === '' && $element->relationLoaded('reportingUnit')) {
            $unit = trim((string) ($element->reportingUnit?->name ?? ''));
        }

        $hasFormula = trim((string) ($element->formular_id ?? '')) !== '';
        $hasMethodSequence = (bool) ($element->has_method_sequence ?? false)
            || trim((string) ($element->method_sequence_id ?? '')) !== '';

        return [
            'TAT' => trim((string) ($element->reporting_time ?? '')),
            'Method' => $this->elementMethodName($element),
            'Sample type' => $sampleType,
            'Analysis type' => $analysisType,
            'Test category' => $testCategory,
            'LOD' => $this->formatTestInfoNumber($element->lod),
            'LOQ' => $this->formatTestInfoNumber($element->hod),
            'Equipment' => $equipmentName,
            'Unit' => $unit,
            'Has formula' => $hasFormula ? 'Yes' : 'No',
            'Has method sequence' => $hasMethodSequence ? 'Yes' : 'No',
            'MU' => $this->formatTestInfoNumber($element->measurement_uncertainty),
            'Accredited' => ! (bool) ($element->non_accredited ?? false) ? 'Yes' : 'No',
        ];
    }

    private function formatTestInfoNumber(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (! is_numeric($value)) {
            return trim((string) $value);
        }

        $formatted = rtrim(rtrim(number_format((float) $value, 6, '.', ''), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }

    /**
     * @param  list<array{label: string, value: string}>  $fields
     * @return list<array{label: string, value: string}>
     */
    private function catalogFields(array $fields): array
    {
        $out = [];
        foreach ($fields as $field) {
            $value = trim((string) ($field['value'] ?? ''));
            if ($value === '' || strcasecmp($value, '—') === 0 || strcasecmp($value, 'N/A') === 0) {
                continue;
            }
            if (Str::isUuid($value)) {
                continue;
            }
            $out[] = [
                'label' => (string) $field['label'],
                'value' => $value,
            ];
        }

        return $out;
    }

    /**
     * @param  list<string>  $elementNames
     */
    private function firstDisplayValue(SubmissionFormInstance $instance, array $elementNames, mixed $fallback = ''): string
    {
        foreach ($elementNames as $elementName) {
            $display = $instance->resolveDisplayValueByName($elementName);
            $plain = $this->stringifyMixed($display);
            if ($plain !== '' && ! Str::isUuid($plain)) {
                return $plain;
            }
        }

        return $this->stringifyMixed($fallback);
    }

    private function stringifyMixed(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            $labels = [];
            foreach ($value as $key => $item) {
                if (is_bool($item)) {
                    if ($item) {
                        $labels[] = ucwords(str_replace('_', ' ', (string) $key));
                    }
                    continue;
                }
                $text = trim((string) (is_scalar($item) ? $item : ''));
                if ($text !== '' && ! Str::isUuid($text)) {
                    $labels[] = $text;
                }
            }

            return implode(', ', array_values(array_unique($labels)));
        }

        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)) ?? '');

        return Str::isUuid($plain) ? '' : $plain;
    }

    /**
     * @param  array<string, mixed>  $sample
     */
    private function stateOfSampleFromDetails(array $sample): string
    {
        foreach ((is_array($sample['details'] ?? null) ? $sample['details'] : []) as $field) {
            if (mb_strtolower(trim((string) ($field['label'] ?? ''))) === 'state of sample') {
                return trim((string) ($field['value'] ?? ''));
            }
        }

        return '';
    }

    /**
     * UI label — quotes around description when present.
     */
    public function sampleDisplayLabel(int $index, ?string $description = null): string
    {
        $number = $index + 1;
        $description = trim((string) ($description ?? ''));
        if ($description === '') {
            return 'Sample '.$number;
        }

        return 'Sample '.$number.' - "'.$description.'"';
    }

    /**
     * PDF / Excel label — no decorative quotes.
     */
    public function sampleExportLabel(int $index, ?string $description = null): string
    {
        $number = $index + 1;
        $description = trim((string) ($description ?? ''));
        if ($description === '') {
            return 'Sample '.$number;
        }

        return 'Sample '.$number.' - '.$description;
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @return array<int, array<string, mixed>>
     */
    private function matchedTrfSamplesByConfigIndex(
        ?SubmissionFormInstance $instance,
        ?SampleSubmissionRequest $enquiry,
        array $configs,
    ): array {
        if ($instance === null || $enquiry === null || $configs === []) {
            return [];
        }

        $instance->loadMissing(['submissionForm', 'crmCustomer']);
        $sampleLines = $this->sampleLineService->linesForInstance($instance);

        $presenter = new RequestViewPagePresenter(
            instance: $instance,
            submissionForm: $instance->submissionForm,
            commercialEnquiry: $enquiry,
            trfPdfUrl: null,
            canCreateSamples: false,
            linkedBatchesOutOfSyncWithForm: false,
            showSampleCollectionLabel: false,
            isTrfForm: false,
        );

        $testSamplesCard = $presenter->testSamplesCard($sampleLines);
        $samplesByCustomerId = [];
        $samplesByIndex = [];

        foreach ($testSamplesCard['samples'] as $index => $sample) {
            $samplesByIndex[$index] = $sample;
            $customerSampleId = trim((string) ($sample['customer_sample_id'] ?? ''));
            if ($customerSampleId !== '' && $customerSampleId !== '—') {
                $samplesByCustomerId[mb_strtolower($customerSampleId)] = $sample;
            }
        }

        $matched = [];
        foreach ($configs as $configIndex => $config) {
            $customerSampleId = trim((string) ($config['customer_sample_id'] ?? ''));
            $sampleMarking = trim((string) ($config['sample_marking'] ?? ''));

            if ($customerSampleId !== '' && isset($samplesByCustomerId[mb_strtolower($customerSampleId)])) {
                $matched[$configIndex] = $samplesByCustomerId[mb_strtolower($customerSampleId)];
            } elseif ($sampleMarking !== '' && isset($samplesByCustomerId[mb_strtolower($sampleMarking)])) {
                $matched[$configIndex] = $samplesByCustomerId[mb_strtolower($sampleMarking)];
            } elseif (isset($samplesByIndex[$configIndex])) {
                $matched[$configIndex] = $samplesByIndex[$configIndex];
            }
        }

        return $matched;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function sampleDescriptionFromConfig(array $config, ?array $matchedSample = null): string
    {
        $fromConfig = trim((string) ($config['sample_marking'] ?? ''));
        if ($fromConfig !== '') {
            return $fromConfig;
        }

        if (is_array($matchedSample)) {
            return $this->plainSampleDescription($matchedSample);
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $sample
     */
    private function plainSampleDescription(array $sample): string
    {
        $description = trim((string) ($sample['sample_description'] ?? ''));
        if ($description !== '' && $description !== '—') {
            return $description;
        }

        return trim(strip_tags((string) ($sample['sample_description_html'] ?? '')));
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function testCategoryFromConfig(array $config): string
    {
        $raw = $config['test_category'] ?? null;
        if ($raw === null || $raw === '') {
            return '';
        }

        if (is_array($raw)) {
            $tokens = SubmissionFormSchemaHelper::testCategoryTokens($raw);

            return SubmissionFormSchemaHelper::testCategoryLabel(implode(',', $tokens));
        }

        return SubmissionFormSchemaHelper::testCategoryLabel((string) $raw);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function sampleTypeNameFromConfig(array $config): string
    {
        $name = trim((string) ($config['sample_type_name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $ids = is_array($config['sample_type_ids'] ?? null) ? $config['sample_type_ids'] : [];
        if ($ids === [] && filled($config['sample_type_id'] ?? null)) {
            $ids = [(string) $config['sample_type_id']];
        }

        if ($ids === []) {
            return '';
        }

        return SampleType::query()
            ->whereIn('id', array_map('strval', $ids))
            ->orderBy('name')
            ->pluck('name')
            ->filter()
            ->unique()
            ->implode(', ');
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function analysisTypeNameFromConfig(array $config): string
    {
        $ids = is_array($config['analysis_type_ids'] ?? null) ? $config['analysis_type_ids'] : [];
        if ($ids === [] && filled($config['analysis_type_id'] ?? null)) {
            $ids = [(string) $config['analysis_type_id']];
        }

        if ($ids === []) {
            return '';
        }

        return AnalysisType::query()
            ->whereIn('id', array_map('strval', $ids))
            ->orderBy('name')
            ->pluck('name')
            ->filter()
            ->unique()
            ->implode(', ');
    }

    private function elementMethodName(?AnalysisElements $element): string
    {
        if ($element === null) {
            return '';
        }

        $method = trim((string) ($element->mmethod?->name ?? $element->ltmethod?->name ?? ''));
        if ($method !== '') {
            return $method;
        }

        return trim((string) ($element->method ?? ''));
    }

    /**
     * @param  list<array{label: string, value: string}>  $identity
     */
    private function identityHasLabel(array $identity, string $label): bool
    {
        foreach ($identity as $field) {
            if (($field['label'] ?? '') === $label) {
                return true;
            }
        }

        return false;
    }

    /**
     * Export / legacy row label (no UI quotes).
     *
     * @param  array<string, mixed>  $config
     */
    private function sampleLabelForConfig(array $config, int $index): string
    {
        return $this->sampleExportLabel($index, $this->sampleDescriptionFromConfig($config));
    }
}
