<?php

namespace App\Services\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\QuotationDetails;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\SubmissionForm\RequestViewPagePresenter;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Support\Str;

final class SampleIntegrityCheckService
{
    public function __construct(
        private AcceptanceFormSampleConfigService $configService,
        private AcceptanceFormPricingService $pricingService,
        private EnquiryReceptionReadinessService $readinessService,
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
            $analystsBySection = is_array($config['analysts_by_lab_section'] ?? null)
                ? $config['analysts_by_lab_section']
                : [];

            $labelsByElement = $this->parameterLabelsByElementId($config);

            foreach ($parameterKeys as $elementId) {
                $sectionIds = $this->configService->normalizeLabSectionIds($sectionMap[$elementId] ?? null);
                $sectionAnalysts = [];
                foreach ($sectionIds as $sectionId) {
                    $sectionAnalysts[$sectionId] = array_values(array_map(
                        'strval',
                        is_array($analystsBySection[$sectionId] ?? null) ? $analystsBySection[$sectionId] : []
                    ));
                }

                $rows[] = [
                    'row_key' => $configId.'|'.$elementId,
                    'config_id' => $configId,
                    'sample_label' => $sampleLabel,
                    'element_id' => $elementId,
                    'test_label' => $labelsByElement[$elementId] ?? 'Parameter',
                    'lab_section_ids' => $sectionIds,
                    'analysts_by_lab_section' => $sectionAnalysts,
                    'subcontracted' => isset($subcontractedIds[$elementId]),
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
     * Persist Integrity grid edits into enquiry_sample_configuration and quotation subcontract flags.
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

        $subcontractedElementIds = [];
        /** @var array<string, true> $touchedConfigIds */
        $touchedConfigIds = [];
        /** @var array<string, array<string, list<string>>> $analystsByConfig */
        $analystsByConfig = [];

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

            if (! empty($row['subcontracted'])) {
                $subcontractedElementIds[] = $elementId;

                // Analysts are sample-level (keyed by lab section). Subcontracted
                // rows intentionally have empty analyst picks — never let them wipe
                // shared section assignments from in-house tests on the same sample.
                continue;
            }

            $incomingAnalysts = is_array($row['analysts_by_lab_section'] ?? null)
                ? $row['analysts_by_lab_section']
                : [];

            foreach ($sectionIds as $sectionId) {
                $incoming = array_values(array_unique(array_filter(array_map(
                    'strval',
                    is_array($incomingAnalysts[$sectionId] ?? null) ? $incomingAnalysts[$sectionId] : []
                ))));

                // Prefer non-empty picks when multiple in-house tests share a section.
                if ($incoming === [] && isset($analystsByConfig[$configId][$sectionId])) {
                    continue;
                }

                $analystsByConfig[$configId][$sectionId] = $incoming;
            }
        }

        foreach (array_keys($touchedConfigIds) as $configId) {
            $configsById[$configId]['analysts_by_lab_section'] = $analystsByConfig[$configId] ?? [];
        }

        $normalized = $this->configService->normalizeConfigsForStorage(
            array_values($configsById),
            $enquiry->crm_customer_id !== null ? (string) $enquiry->crm_customer_id : null,
        );

        $enquiry->enquiry_sample_configuration = $normalized;
        $enquiry->save();

        $this->syncQuotationSubcontractFlags($enquiry, array_values(array_unique($subcontractedElementIds)));

        return $enquiry->fresh() ?? $enquiry;
    }

    /**
     * @param  list<string>  $subcontractedElementIds
     */
    public function syncQuotationSubcontractFlags(SampleSubmissionRequest $enquiry, array $subcontractedElementIds): void
    {
        $quotation = $this->readinessService->resolveAcceptedQuotation($enquiry)
            ?? $enquiry->currentQuotation
            ?? $enquiry->acceptedQuotation;

        if ($quotation === null) {
            return;
        }

        $quotation->loadMissing('details');
        $flagged = array_flip($subcontractedElementIds);

        foreach ($quotation->details as $detail) {
            /** @var QuotationDetails $detail */
            $elementId = trim((string) ($detail->accredited_analytes ?? $detail->default_analytes ?? ''));
            if ($elementId === '') {
                continue;
            }

            $detail->subcontracted_analytes = isset($flagged[$elementId]) ? $elementId : '';
            $detail->save();
        }
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

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, string>
     */
    private function parameterLabelsByElementId(array $config): array
    {
        $parameterKeys = array_values(array_filter(array_map(
            'strval',
            is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : []
        )));

        if ($parameterKeys === []) {
            return [];
        }

        return \App\AnalysisElements::query()
            ->with('analyte:id,name,code')
            ->whereIn('id', $parameterKeys)
            ->get(['id', 'analyte_id'])
            ->mapWithKeys(function ($element): array {
                $label = trim((string) ($element->analyte?->name ?? ''));
                if ($label === '') {
                    $label = trim((string) ($element->analyte?->code ?? ''));
                }

                return [(string) $element->id => $label !== '' ? $label : 'Parameter'];
            })
            ->all();
    }
}
