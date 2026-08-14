<?php

namespace App\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class PortalEnquiryFormInstanceSyncService
{
    private const TARGET_RECORD_TYPE = SampleSubmissionRequest::class;

    private const LSR_DOCUMENT_CODE = 'LSR-001';

    public function __construct(
        private readonly PortalSubmissionFormAccess $portalAccess,
    ) {}

    public function syncFromEnquiry(
        SampleSubmissionRequest $enquiry,
        bool $submit = true,
    ): ?SubmissionFormInstance
    {
        $instances = $this->syncAllSampleTypesFromEnquiry($enquiry, $submit);

        return $instances[0] ?? null;
    }

    /**
     * Create/update a single TRF instance for the enquiry (all sample lines as rows).
     *
     * @return list<SubmissionFormInstance>
     */
    public function syncAllSampleTypesFromEnquiry(
        SampleSubmissionRequest $enquiry,
        bool $submit = true,
    ): array {
        $enquiry->loadMissing(['customer', 'requestedAnalyses']);
        $lines = $this->normalizeSampleLines($enquiry->sample_lines ?? null);
        if ($lines === []) {
            $lines = [$this->flatLineFromEnquiry($enquiry)];
        }
        $lines = $this->assignSequentialRowIndices($lines);
        $lines = $this->applyWizardValuesToSampleLines($enquiry, $lines);

        $form = $this->resolveSubmissionFormForEnquiryLines($lines, $enquiry);
        if ($form === null) {
            return [];
        }

        $instance = $this->syncInstanceForForm($enquiry, $form, $lines, $submit, setAsPrimary: true);

        return $instance !== null ? [$instance] : [];
    }

    /**
     * @param  list<array<string, mixed>>  $sampleLines
     */
    private function syncInstanceForForm(
        SampleSubmissionRequest $enquiry,
        SubmissionForm $form,
        array $sampleLines,
        bool $submit,
        bool $setAsPrimary,
    ): ?SubmissionFormInstance {
        return DB::transaction(function () use ($enquiry, $form, $sampleLines, $submit, $setAsPrimary): SubmissionFormInstance {
            $enquiry->refresh();

            $instance = $this->resolveOrCreateInstanceForForm($enquiry, $form, $submit);
            $instance->loadMissing('submissionForm');
            $form = $instance->submissionForm ?? $form;
            $elementMap = $this->buildElementMap($form);

            // Walk-in TRFs already hold rich sample metadata. Process Enquiry / quotation
            // sync used to wipe those values and rewrite from thinner sample_lines.
            $preserved = $this->snapshotInstanceValues($instance);
            $sampleLines = $this->mergePreservedRowFieldsIntoLines($sampleLines, $preserved);

            $instance->values()->delete();

            $this->syncHeaderValues($instance, $elementMap, $enquiry);
            $this->restorePreservedScalarValues($instance, $elementMap, $preserved);
            $this->syncSampleLineValuesFromLines($instance, $elementMap, $sampleLines);
            $this->restorePreservedUnmappedValues($instance, $elementMap, $preserved);
            $this->updateInstanceMetadata($instance, $enquiry, $form, $submit);

            if ($setAsPrimary && $enquiry->submission_form_instance_id !== $instance->id) {
                $enquiry->submission_form_instance_id = $instance->id;
                $enquiry->save();
            }

            return $instance->fresh(['values']) ?? $instance;
        });
    }

    public function resolveSubmissionFormForEnquiry(SampleSubmissionRequest $enquiry): ?SubmissionForm
    {
        $lines = $this->normalizeSampleLines($enquiry->sample_lines ?? null);
        if ($lines === []) {
            $lines = [$this->flatLineFromEnquiry($enquiry)];
        }

        return $this->resolveSubmissionFormForEnquiryLines($lines, $enquiry);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function resolveSubmissionFormForEnquiryLines(
        array $lines,
        ?SampleSubmissionRequest $enquiry = null,
        ?string $crmCustomerId = null,
    ): ?SubmissionForm {
        if ($enquiry !== null) {
            $chosenForm = $this->resolveChosenSubmissionFormFromEnquiry($enquiry);
            if ($chosenForm !== null) {
                return $chosenForm;
            }
        }

        $crmCustomerId = trim((string) ($crmCustomerId ?? $enquiry?->crm_customer_id ?? ''));
        $crmCustomerId = $crmCustomerId !== '' ? $crmCustomerId : null;

        $categoryIds = $this->portalAccess->categoryIdsFromSampleLines($lines);
        if ($categoryIds !== []) {
            $form = $this->portalAccess->testRequestFormForCategoryIds($categoryIds, $crmCustomerId);
            if ($form !== null) {
                return $form;
            }
        }

        $sampleTypeId = trim((string) ($enquiry?->sample_type_id ?? $enquiry?->batch_sample_type_id ?? ''));
        if ($sampleTypeId === '' && $lines !== []) {
            $sampleTypeId = trim((string) ($lines[0]['sample_type_id'] ?? ''));
        }

        if ($sampleTypeId !== '') {
            $form = $this->portalAccess->testRequestFormForSampleType($sampleTypeId, $crmCustomerId);
            if ($form !== null) {
                return $form;
            }
        }

        return $this->resolveLegacyLaboratoryServiceRequestForm();
    }

    /**
     * @deprecated Use resolveSubmissionFormForEnquiryLines() — resolves via sample type category only.
     */
    public function resolveSubmissionFormForSampleType(?string $sampleTypeId, ?string $crmCustomerId = null): ?SubmissionForm
    {
        $sampleTypeId = trim((string) ($sampleTypeId ?? ''));

        if ($sampleTypeId !== '') {
            $form = $this->portalAccess->testRequestFormForSampleType($sampleTypeId, $crmCustomerId);
            if ($form !== null) {
                return $form;
            }
        }

        return $this->resolveLegacyLaboratoryServiceRequestForm();
    }

    private function resolveLegacyLaboratoryServiceRequestForm(): ?SubmissionForm
    {
        return SubmissionForm::query()
            ->where('document_code', self::LSR_DOCUMENT_CODE)
            ->where('is_active', true)
            ->first();
    }

    /**
     * When an enquiry already has a linked TRF instance (walk-in, portal, etc.),
     * keep that instance's submission form — do not re-resolve from sample line categories.
     */
    private function resolveChosenSubmissionFormFromEnquiry(SampleSubmissionRequest $enquiry): ?SubmissionForm
    {
        $enquiry->loadMissing('submissionFormInstance.submissionForm');

        $instance = $enquiry->submissionFormInstance;

        if ($instance === null && filled($enquiry->submission_form_instance_id)) {
            $instance = SubmissionFormInstance::query()->find($enquiry->submission_form_instance_id);
        }

        if ($instance === null) {
            $instance = SubmissionFormInstance::query()
                ->where('portal_request_id', (string) $enquiry->id)
                ->orderByDesc('created_at')
                ->first();
        }

        if ($instance === null) {
            return null;
        }

        $instance->loadMissing('submissionForm');

        return $instance->submissionForm;
    }

    private function resolveOrCreateInstanceForForm(
        SampleSubmissionRequest $enquiry,
        SubmissionForm $form,
        bool $submit,
    ): SubmissionFormInstance {
        $linked = SubmissionFormInstance::query()
            ->where('portal_request_id', (string) $enquiry->id)
            ->orderByDesc('created_at')
            ->first();

        if ($linked !== null) {
            return $this->ensureInstanceLinkedToEnquiry($linked, $enquiry);
        }

        if ($enquiry->submission_form_instance_id) {
            $existing = SubmissionFormInstance::query()->find($enquiry->submission_form_instance_id);
            if ($existing !== null) {
                $portalId = trim((string) ($existing->portal_request_id ?? ''));
                if ($portalId === '' || $portalId === (string) $enquiry->id) {
                    return $this->ensureInstanceLinkedToEnquiry($existing, $enquiry);
                }
            }
        }

        return SubmissionFormInstance::query()->create([
            'submission_form_id' => $form->id,
            'crm_customer_id' => $enquiry->crm_customer_id,
            'portal_request_id' => (string) $enquiry->id,
            'target_record_type' => self::TARGET_RECORD_TYPE,
            'status' => $submit ? 'submitted' : 'draft',
            'submitted_at' => $submit ? now() : null,
            'title' => $this->instanceTitle($enquiry, $form),
            'priority' => $this->instancePriority($enquiry),
        ]);
    }

    private function ensureInstanceLinkedToEnquiry(
        SubmissionFormInstance $instance,
        SampleSubmissionRequest $enquiry,
    ): SubmissionFormInstance {
        $dirty = false;

        if (trim((string) ($instance->portal_request_id ?? '')) !== (string) $enquiry->id) {
            $instance->portal_request_id = (string) $enquiry->id;
            $dirty = true;
        }

        if ($instance->target_record_type !== self::TARGET_RECORD_TYPE) {
            $instance->target_record_type = self::TARGET_RECORD_TYPE;
            $dirty = true;
        }

        if ($dirty) {
            $instance->save();
        }

        return $instance;
    }

    /**
     * @return array{
     *     scalars: array<string, string>,
     *     rows: array<int, array<string, string>>
     * }
     */
    private function snapshotInstanceValues(SubmissionFormInstance $instance): array
    {
        $instance->loadMissing(['values.element']);

        $scalars = [];
        $rows = [];

        foreach ($instance->values as $value) {
            $name = trim((string) ($value->element?->name ?? ''));
            if ($name === '') {
                continue;
            }

            $raw = trim((string) ($value->value ?? ''));
            if ($raw === '' || strcasecmp($raw, 'null') === 0) {
                continue;
            }

            if ($value->array_index === null) {
                $scalars[$name] = $raw;

                continue;
            }

            $rowIndex = (int) $value->array_index;
            $rows[$rowIndex][$name] = $raw;
        }

        ksort($rows);

        return [
            'scalars' => $scalars,
            'rows' => $rows,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @param  array{scalars: array<string, string>, rows: array<int, array<string, string>>}  $preserved
     * @return list<array<string, mixed>>
     */
    private function mergePreservedRowFieldsIntoLines(array $lines, array $preserved): array
    {
        $rowKeys = [
            'sample_description',
            'sample_id',
            'customer_sample_id',
            'sample_type_id',
            'analysis_type_id',
            'parameters',
            'parameter_category',
            'sampling_point',
            'sampling_point_manual',
            'location',
            'sampling_location',
            'sample_quantity',
            'sample_quantity_unit',
            'production_date',
            'expiration_date',
            'batch_number',
            'test_category',
            'test_requirements',
            'sample_condition',
            'state_of_sample',
            'number_of_samples',
        ];

        foreach ($lines as $index => &$line) {
            $rowIndex = (int) ($line['row_index'] ?? $index);
            $source = $preserved['rows'][$rowIndex] ?? [];
            if ($source === []) {
                continue;
            }

            foreach ($rowKeys as $key) {
                $existing = trim((string) ($line[$key] ?? ''));
                if ($existing !== '') {
                    continue;
                }

                $candidate = trim((string) ($source[$key] ?? ''));
                if ($candidate === '') {
                    continue;
                }

                if ($key === 'parameters') {
                    $line['analysis_element_ids'] = array_values(array_filter(array_map(
                        static fn (string $token): string => trim($token),
                        explode(',', $candidate),
                    )));

                    continue;
                }

                if ($key === 'sample_id') {
                    $line['customer_sample_id'] = $candidate;

                    continue;
                }

                $line[$key] = $candidate;
            }
        }
        unset($line);

        return $lines;
    }

    /**
     * @param  array<string, array{id: string, is_row: bool}>  $elementMap
     * @param  array{scalars: array<string, string>, rows: array<int, array<string, string>>}  $preserved
     */
    private function restorePreservedScalarValues(
        SubmissionFormInstance $instance,
        array $elementMap,
        array $preserved,
    ): void {
        foreach ($preserved['scalars'] as $name => $value) {
            if (! isset($elementMap[$name]) || ($elementMap[$name]['is_row'] ?? false)) {
                continue;
            }

            $alreadyStored = $instance->values()
                ->where('submission_form_element_id', $elementMap[$name]['id'])
                ->whereNull('array_index')
                ->exists();

            if ($alreadyStored) {
                continue;
            }

            $this->storeValue($instance, $elementMap, $name, $value);
        }
    }

    /**
     * Keep row fields captured on the TRF that enquiry sync does not own
     * (e.g. sampling_point_manual) so Process Enquiry cannot drop them.
     *
     * @param  array<string, array{id: string, is_row: bool}>  $elementMap
     * @param  array{scalars: array<string, string>, rows: array<int, array<string, string>>}  $preserved
     */
    private function restorePreservedUnmappedValues(
        SubmissionFormInstance $instance,
        array $elementMap,
        array $preserved,
    ): void {
        $ownedRowFields = [
            'sample_description',
            'sample_id',
            'sample_type_id',
            'analysis_type_id',
            'parameters',
            'parameter_category',
            'sampling_point',
            'location',
            'sample_quantity',
            'sample_quantity_unit',
            'production_date',
            'expiration_date',
            'batch_number',
            'test_category',
            'test_requirements',
            'sample_condition',
            'state_of_sample',
            'number_of_samples',
        ];

        foreach ($preserved['rows'] as $rowIndex => $fields) {
            foreach ($fields as $name => $value) {
                if (in_array($name, $ownedRowFields, true)) {
                    continue;
                }

                if (! isset($elementMap[$name])) {
                    continue;
                }

                $alreadyStored = $instance->values()
                    ->where('submission_form_element_id', $elementMap[$name]['id'])
                    ->where('array_index', (int) $rowIndex)
                    ->exists();

                if ($alreadyStored) {
                    continue;
                }

                $this->storeValue($instance, $elementMap, $name, $value, (int) $rowIndex);
            }
        }
    }

    /**
     * @return array<string, array{id: string, is_row: bool}>
     */
    private function buildElementMap(SubmissionForm $form): array
    {
        $form->loadMissing(['sections.elementHolders.elements']);

        $map = [];

        foreach ($form->sections as $section) {
            $isRow = (string) ($section->section_type ?? '') === 'rows_section';

            foreach ($section->elementHolders as $holder) {
                foreach ($holder->elements as $element) {
                    $name = (string) ($element->name ?? '');
                    if ($name === '') {
                        continue;
                    }

                    $map[$name] = [
                        'id' => (string) $element->id,
                        'is_row' => $isRow,
                    ];
                }
            }
        }

        return $map;
    }

    /**
     * @param  array<string, array{id: string, is_row: bool}>  $elementMap
     */
    private function syncHeaderValues(
        SubmissionFormInstance $instance,
        array $elementMap,
        SampleSubmissionRequest $enquiry,
    ): void {
        $enquiry->loadMissing('customer');

        $this->storeValue($instance, $elementMap, 'customer_name', $enquiry->customer?->name);
        $this->storeValue($instance, $elementMap, 'customer_address', $enquiry->customer?->physical_address ?? $enquiry->customer?->postal_address);
        $this->storeValue($instance, $elementMap, 'customer_phone', $enquiry->customer?->telephone1);
        $this->storeValue($instance, $elementMap, 'customer_tel_fax', $enquiry->customer?->telephone1);
        $this->storeValue($instance, $elementMap, 'mobile_number', $enquiry->customer?->telephone2);
        $this->storeValue($instance, $elementMap, 'customer_mobile', $enquiry->customer?->telephone2);
        $this->storeValue($instance, $elementMap, 'crm_customer_id', $enquiry->crm_customer_id);
        $this->storeValue($instance, $elementMap, 'reporting_language', $enquiry->reporting_language);
        $this->storeValue($instance, $elementMap, 'request_date_of_service', $this->formatDate($enquiry->request_date_of_service));
        $this->storeValue($instance, $elementMap, 'lab_zone_location', $enquiry->zone_id);
        $this->storeValue($instance, $elementMap, 'request_for_sampling', $enquiry->request_for_sampling ? '1' : '0');
        $this->storeValue($instance, $elementMap, 'mode_of_service', $enquiry->mode_of_service_priority);
        $this->storeValue($instance, $elementMap, 'mode_of_payment', $enquiry->mode_of_payment);
        $this->storeValue($instance, $elementMap, 'statement_of_conformity', $enquiry->statement_of_conformity);
        $this->storeValue($instance, $elementMap, 'purpose', $enquiry->purpose);
        $this->storeValue($instance, $elementMap, 'submitted_by_full_name', $enquiry->submitted_by_full_name);
        $this->storeValue($instance, $elementMap, 'submitted_by_title', $enquiry->submitted_by_title);
        $this->storeValue($instance, $elementMap, 'submitted_by_signature', $enquiry->submitted_by_signature);
        $this->storeValue($instance, $elementMap, 'submitted_by_date', $this->formatDate($enquiry->submitted_by_date));

        $physicalSampleCount = max(
            1,
            (int) ($enquiry->number_of_samples ?? 0),
            count(is_array($enquiry->enquiry_sample_configuration) ? $enquiry->enquiry_sample_configuration : []),
        );
        $this->storeValue($instance, $elementMap, 'number_of_samples', (string) $physicalSampleCount);
        $this->storeValue($instance, $elementMap, 'no_of_samples', (string) $physicalSampleCount);
        $this->storeValue($instance, $elementMap, 'sample_count', (string) $physicalSampleCount);

        if (! $enquiry->request_for_sampling) {
            return;
        }

        $collection = is_array($enquiry->collection_data) ? $enquiry->collection_data : [];

        foreach ([
            'sampling_date',
            'sampling_time',
            'sampling_location',
            'sampling_apparatus',
            'method_of_sampling',
            'reason_of_collection',
            'transport_condition',
                'date_received',
                'packaging',
                'sample_weight',
                'sample_information',
                'ship_name',
                'port_of_loading',
                'port_of_discharge',
                'seal_number',
        ] as $field) {
            $value = $collection[$field] ?? null;
                if (in_array($field, ['sampling_date', 'date_received'], true)) {
                $value = $this->formatDate($value);
            }
            $this->storeValue($instance, $elementMap, $field, $value);
        }
    }

    /**
     * @param  array<string, array{id: string, is_row: bool}>  $elementMap
     */
    private function syncSampleLineValues(
        SubmissionFormInstance $instance,
        array $elementMap,
        SampleSubmissionRequest $enquiry,
    ): void {
        $lines = $this->normalizeSampleLines($enquiry->sample_lines ?? null);

        if ($lines === []) {
            $lines = [$this->flatLineFromEnquiry($enquiry)];
        }

        $this->syncSampleLineValuesFromLines($instance, $elementMap, $lines);
    }

    /**
     * @param  array<string, array{id: string, is_row: bool}>  $elementMap
     * @param  list<array<string, mixed>>  $lines
     */
    private function syncSampleLineValuesFromLines(
        SubmissionFormInstance $instance,
        array $elementMap,
        array $lines,
    ): void {
        foreach ($lines as $line) {
            $rowIndex = (int) ($line['row_index'] ?? 0);
            $parameterIds = $line['analysis_element_ids'] ?? [];

            $this->storeValue($instance, $elementMap, 'sample_description', $line['sample_description'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'sample_id', $line['customer_sample_id'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'sample_type_id', $line['sample_type_id'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'analysis_type_id', $line['analysis_type_id'] ?? null, $rowIndex);
            $this->storeValue(
                $instance,
                $elementMap,
                'parameters',
                $parameterIds !== [] ? implode(',', $parameterIds) : null,
                $rowIndex,
            );
            $this->storeValue($instance, $elementMap, 'parameter_category', $line['parameter_category'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'sampling_point', $line['sampling_point'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'sampling_point_manual', $line['sampling_point_manual'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'location', $line['location'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'sampling_location', $line['sampling_location'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'sample_quantity', $line['sample_quantity'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'sample_quantity_unit', $line['sample_quantity_unit'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'production_date', $this->formatDate($line['production_date'] ?? null), $rowIndex);
            $this->storeValue($instance, $elementMap, 'expiration_date', $this->formatDate($line['expiration_date'] ?? null), $rowIndex);
            $this->storeValue($instance, $elementMap, 'batch_number', $line['batch_number'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'test_category', $line['test_category'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'test_requirements', $line['test_requirements'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'sample_condition', $line['sample_condition'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'state_of_sample', $line['state_of_sample'] ?? null, $rowIndex);
            $this->storeValue(
                $instance,
                $elementMap,
                'number_of_samples',
                (string) max(1, (int) ($line['number_of_samples'] ?? 1)),
                $rowIndex,
            );
        }
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    private function assignSequentialRowIndices(array $lines): array
    {
        foreach ($lines as $index => &$line) {
            $line['row_index'] = $index;
        }
        unset($line);

        return $lines;
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    private function applyWizardValuesToSampleLines(SampleSubmissionRequest $enquiry, array $lines): array
    {
        $byType = is_array($enquiry->trf_section_field_values)
            ? $enquiry->trf_section_field_values
            : [];

        if ($byType === [] || $lines === []) {
            return $lines;
        }

        $unifiedBucket = is_array($byType[EnquiryFromQuotationService::UNIFIED_TRF_FIELD_KEY] ?? null)
            ? $byType[EnquiryFromQuotationService::UNIFIED_TRF_FIELD_KEY]
            : null;

        $keys = [
            'sample_description',
            'sample_quantity',
            'sample_quantity_unit',
            'sampling_point',
            'location',
            'production_date',
            'expiration_date',
            'batch_number',
            'test_category',
            'test_requirements',
            'parameter_category',
            'sample_condition',
            'state_of_sample',
            'field_ph',
            'field_appearance',
            'field_residual_chlorine',
            'field_odor',
            'field_sample_temp',
        ];

        $typeRowCounters = [];

        foreach ($lines as $rowIndex => &$line) {
            if ($unifiedBucket !== null) {
                $source = EnquiryFromQuotationService::resolveTrfSectionRowFields($unifiedBucket, $rowIndex);
            } else {
                $typeId = trim((string) ($line['sample_type_id'] ?? ''));
                if ($typeId === '' || ! is_array($byType[$typeId] ?? null)) {
                    continue;
                }

                $rowWithinType = $typeRowCounters[$typeId] ?? 0;
                $typeRowCounters[$typeId] = $rowWithinType + 1;

                $source = EnquiryFromQuotationService::resolveTrfSectionRowFields(
                    $byType[$typeId],
                    $rowWithinType,
                );
            }

            foreach ($keys as $key) {
                $existing = trim((string) ($line[$key] ?? ''));
                if ($existing !== '') {
                    continue;
                }

                $candidate = $source[$key] ?? null;
                if ($candidate === null || $candidate === '' || $candidate === []) {
                    continue;
                }

                $line[$key] = is_array($candidate) ? implode(',', array_filter(array_map('strval', $candidate))) : (string) $candidate;
            }

            if (trim((string) ($line['parameter_category'] ?? '')) === '') {
                $category = $source['parameter_category']
                    ?? $source['test_category']
                    ?? $source['test_requirements']
                    ?? null;
                if ($category !== null && $category !== '') {
                    $line['parameter_category'] = is_array($category)
                        ? implode(',', $category)
                        : (string) $category;
                }
            }
        }
        unset($line);

        return $lines;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function normalizeSampleLines(mixed $rawLines): array
    {
        if (! is_array($rawLines) || $rawLines === []) {
            return [];
        }

        $lines = [];

        foreach (array_values($rawLines) as $index => $line) {
            if (! is_array($line)) {
                continue;
            }

            $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
            $parameterIds = $line['analysis_element_ids']
                ?? $line['parameter_ids']
                ?? $attributes['analysis_element_ids']
                ?? [];
            if (! is_array($parameterIds)) {
                $parameterIds = [];
            }

            $parameterIds = array_values(array_filter(array_map(
                static fn (mixed $id): string => trim((string) $id),
                $parameterIds,
            )));

            $normalized = [
                'row_index' => (int) ($line['row_index'] ?? $index),
                'customer_sample_id' => trim((string) ($line['customer_sample_id'] ?? $line['sample_id'] ?? '')),
                'sample_description' => trim((string) ($line['sample_description'] ?? '')),
                'parameter_category' => trim((string) ($line['parameter_category'] ?? '')),
                'sample_type_id' => trim((string) ($line['sample_type_id'] ?? '')),
                'analysis_type_id' => trim((string) ($line['analysis_type_id'] ?? $line['matrix_id'] ?? '')),
                'analysis_element_ids' => $parameterIds,
                'number_of_samples' => max(1, (int) ($line['number_of_samples'] ?? 1)),
                'sampling_point' => trim((string) ($line['sampling_point'] ?? '')),
                'location' => trim((string) ($line['location'] ?? '')),
                'sample_quantity' => trim((string) ($line['sample_quantity'] ?? '')),
                'sample_quantity_unit' => trim((string) ($line['sample_quantity_unit'] ?? '')),
                'production_date' => $line['production_date'] ?? null,
                'expiration_date' => $line['expiration_date'] ?? null,
                'batch_number' => trim((string) ($line['batch_number'] ?? '')),
                'test_category' => trim((string) ($line['test_category'] ?? '')),
                'test_requirements' => trim((string) ($line['test_requirements'] ?? '')),
                'sample_condition' => trim((string) ($line['sample_condition'] ?? '')),
                'state_of_sample' => trim((string) ($line['state_of_sample'] ?? '')),
            ];

            if ($normalized['customer_sample_id'] === '' && $normalized['sample_type_id'] === '' && $parameterIds === []) {
                continue;
            }

            $lines[] = $normalized;
        }

        usort($lines, static fn (array $a, array $b): int => $a['row_index'] <=> $b['row_index']);

        return $lines;
    }

    /**
     * @return array<string, mixed>
     */
    private function flatLineFromEnquiry(SampleSubmissionRequest $enquiry): array
    {
        $parameterIds = is_array($enquiry->parameter_ids) ? $enquiry->parameter_ids : [];

        if ($parameterIds === []) {
            $enquiry->loadMissing('requestedAnalyses');
            $parameterIds = $enquiry->requestedAnalyses
                ->pluck('analysis_element_id')
                ->filter()
                ->map(static fn (mixed $id): string => trim((string) $id))
                ->unique()
                ->values()
                ->all();
        }

        return [
            'row_index' => 0,
            'customer_sample_id' => $enquiry->sample_id,
            'sample_description' => $enquiry->sample_description,
            'sample_type_id' => $enquiry->sample_type_id,
            'analysis_type_id' => $enquiry->matrix_id,
            'analysis_element_ids' => array_values($parameterIds),
            'number_of_samples' => max(1, (int) ($enquiry->number_of_samples ?? 1)),
        ];
    }

    private function updateInstanceMetadata(
        SubmissionFormInstance $instance,
        SampleSubmissionRequest $enquiry,
        SubmissionForm $form,
        bool $submit,
    ): void {
        $instance->crm_customer_id = $enquiry->crm_customer_id;
        $instance->zone_id = $enquiry->zone_id;
        $instance->source_channel = $this->normalizeInstanceSourceChannel($enquiry->source_channel);
        $instance->portal_request_id = (string) $enquiry->id;
        $instance->target_record_type = self::TARGET_RECORD_TYPE;
        $instance->status = $submit ? 'submitted' : 'draft';
        $instance->title = $this->instanceTitle($enquiry, $form);
        $instance->priority = $this->instancePriority($enquiry);

        if ($submit && $instance->submitted_at === null) {
            $instance->submitted_at = now();
        } elseif (! $submit) {
            $instance->submitted_at = null;
        }

        $instance->save();
    }

    /**
     * @param  array<string, array{id: string, is_row: bool}>  $elementMap
     */
    private function storeValue(
        SubmissionFormInstance $instance,
        array $elementMap,
        string $name,
        mixed $value,
        ?int $arrayIndex = null,
    ): void {
        if ($value === null || $value === '') {
            return;
        }

        if (! isset($elementMap[$name])) {
            return;
        }

        $meta = $elementMap[$name];

        // Row-scoped elements must be written with an explicit row index. Header sync
        // used to default missing indexes to 0, which collided with sample line row 0
        // (unique on instance + element + array_index).
        if ($meta['is_row'] && $arrayIndex === null) {
            return;
        }

        $resolvedIndex = $meta['is_row'] ? $arrayIndex : null;

        SubmissionFormInstanceValue::query()->updateOrCreate(
            [
                'submission_form_instance_id' => $instance->id,
                'submission_form_element_id' => $meta['id'],
                'array_index' => $resolvedIndex,
            ],
            [
                'value' => $this->stringifyValue($value),
            ],
        );
    }

    private function stringifyValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            $parts = [];
            foreach ($value as $item) {
                $token = trim((string) $item);
                if ($token !== '') {
                    $parts[] = $token;
                }
            }

            return implode(',', $parts);
        }

        return trim((string) $value);
    }

    private function normalizeInstanceSourceChannel(mixed $channel): string
    {
        $channel = strtolower(trim((string) ($channel ?? '')));
        if (in_array($channel, ['quotation', 'existing_quotation', 'from_quotation'], true)) {
            return CommercialEnquirySyncService::SOURCE_WALK_IN;
        }
        if (in_array($channel, [
            CommercialEnquirySyncService::SOURCE_WALK_IN,
            CommercialEnquirySyncService::SOURCE_PORTAL,
            CommercialEnquirySyncService::SOURCE_SCHEDULED,
        ], true)) {
            return $channel;
        }

        return CommercialEnquirySyncService::SOURCE_WALK_IN;
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->toDateString();
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return trim((string) $value) !== '' ? trim((string) $value) : null;
        }
    }

    private function instanceTitle(SampleSubmissionRequest $enquiry, SubmissionForm $form): string
    {
        $label = trim((string) ($enquiry->unique_identification ?? $enquiry->reference_number ?? ''));

        if ($label === '') {
            $label = $enquiry->formatted_number;
        }

        $formLabel = str_starts_with(strtoupper((string) $form->document_code), 'TRF-')
            ? 'Test Request Form'
            : 'Laboratory Service Request';

        return $formLabel.' — '.$label;
    }

    private function instancePriority(SampleSubmissionRequest $enquiry): string
    {
        return (string) ($enquiry->mode_of_service_priority ?? '') === 'express' ? 'high' : 'normal';
    }
}
