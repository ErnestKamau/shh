<?php

namespace App\Services\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\AnalysisElements;
use App\Services\SubmissionForm\RequestViewPagePresenter;
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
            ->with(['analyte:id,name,code', 'operator:id,name'])
            ->whereIn('id', $elementIds)
            ->get(['id', 'analyte_id', 'operator_id', 'lab_section_id'])
            ->keyBy(fn (AnalysisElements $element): string => (string) $element->id);
        $rows = [];

        foreach ($configs as $configIndex => $config) {
            $config = $this->configService->syncParameterLabSections($config);
            $configId = (string) ($config['id'] ?? Str::uuid());
            $sampleLabel = $this->sampleLabelForConfig($config, $configIndex);
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

                $rows[] = [
                    'row_key' => $configId.'|'.$elementId,
                    'config_id' => $configId,
                    'sample_label' => $sampleLabel,
                    'element_id' => $elementId,
                    'test_label' => $testLabel !== '' ? $testLabel : 'Parameter',
                    'lab_section_ids' => $sectionIds,
                    'analysts_by_lab_section' => $sectionAnalysts,
                    'default_operator_id' => $defaultOperatorId !== '' ? $defaultOperatorId : null,
                    'default_operator_name' => trim((string) ($element?->operator?->name ?? '')),
                    'subcontracted' => isset($configSubcontracted[$elementId]) || isset($subcontractedIds[$elementId]),
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
     * @param  array<string, mixed>  $config
     */
    private function sampleLabelForConfig(array $config, int $index): string
    {
        $customerSampleId = trim((string) ($config['customer_sample_id'] ?? ''));
        if ($customerSampleId !== '') {
            return $customerSampleId;
        }

        $marking = trim((string) ($config['sample_marking'] ?? ''));
        if ($marking !== '') {
            return $marking;
        }

        return 'S'.($index + 1);
    }
}
