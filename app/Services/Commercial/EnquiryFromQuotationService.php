<?php

namespace App\Services\Commercial;

use App\Models\EnquiryQuotation;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormSection;
use App\QuotationHeader;
use App\ReportingUnit;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use App\Services\SubmissionForm\SubmissionFormInstanceDocumentAttachmentService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class EnquiryFromQuotationService
{
    /** @deprecated Use CommercialEnquirySyncService::SOURCE_WALK_IN — kept for call-site updates. */
    public const SOURCE_CHANNEL = CommercialEnquirySyncService::SOURCE_WALK_IN;

    public const INTENT_PREPARE = 'prepare';

    public const INTENT_ALREADY_SENT = 'already_sent';

    public const INTENT_ACCEPTED = 'accepted';

    /** Wizard / TRF supplemental field bucket when one unified TRF covers all sample lines. */
    public const UNIFIED_TRF_FIELD_KEY = '__unified__';

    /** @var list<string> */
    private const CUSTOMER_SECTION_TITLE_MARKERS = [
        'customer details',
        'client details',
        'customer information',
        'client information',
    ];

    /** @var list<string> */
    private const WIZARD_EXCLUDED_FIELD_NAMES = [
        'number_of_samples',
    ];

    /** @var list<string> */
    private const WIZARD_UNSUPPORTED_ELEMENT_TYPES = [
        'client_select',
        'pricelist_viewer',
        'sample_type_select',
        'analysis_type_select',
        'analysis_elements_select',
        'camera_photo',
        'file',
        'signature',
        'drawing',
        'html',
        'label',
        'heading',
        'divider',
    ];

    public function __construct(
        private readonly QuotationFromEnquiryService $quotationService,
        private readonly AcceptanceFormSampleConfigService $sampleConfigService,
        private readonly CommercialEnquirySampleLineSync $sampleLineSync,
        private readonly PortalEnquiryFormInstanceSyncService $formInstanceSync,
        private readonly EnquiryReceptionReadinessService $receptionReadinessService,
        private readonly CommercialEnquiryFieldMapper $fieldMapper,
        private readonly EnquiryQuotationService $enquiryQuotationService,
    ) {}

    /**
     * @param  array{
     *     number_of_samples?: int,
     *     reference_number?: string|null,
     *     sample_description?: string|null,
     *     enquiry_notes?: string|null,
     *     creation_intent?: string|null,
     *     client_po_number?: string|null,
     *     po_skipped?: bool|null,
     *     source_channel?: string|null,
     *     section_field_values?: array<string, mixed>,
     *     section_field_values_by_type?: array<string, array<string, mixed>>
     * }  $intake
     */
    public function create(
        QuotationHeader $sourceQuotation,
        array $intake,
        string $creationToken,
    ): SampleSubmissionRequest {
        $creationToken = trim($creationToken);
        if ($creationToken === '') {
            throw new RuntimeException('A quotation enquiry creation token is required.');
        }

        $existing = SampleSubmissionRequest::query()
            ->where('quotation_creation_token', $creationToken)
            ->first();
        if ($existing !== null) {
            return $existing->load(['currentQuotation.details', 'submissionFormInstance', 'contact']);
        }

        return DB::transaction(function () use ($sourceQuotation, $intake, $creationToken): SampleSubmissionRequest {
            $source = QuotationHeader::query()
                ->with('details')
                ->lockForUpdate()
                ->find($sourceQuotation->id);

            if ($source === null) {
                throw new RuntimeException('The selected quotation no longer exists.');
            }

            $existing = SampleSubmissionRequest::query()
                ->where('quotation_creation_token', $creationToken)
                ->first();
            if ($existing !== null) {
                return $existing->load(['currentQuotation.details', 'submissionFormInstance', 'contact']);
            }

            $lines = $this->eligibleQuotationLines($source);

            $numberOfSamples = max(
                1,
                (int) ($intake['number_of_samples'] ?? $this->inferPhysicalSampleCount($lines)),
            );
            $intent = $this->normalizeIntent($intake['creation_intent'] ?? null);
            $sourceChannel = $this->normalizeSourceChannel($intake['source_channel'] ?? null);

            $enquiry = SampleSubmissionRequest::query()->create([
                'crm_customer_id' => $source->crm_customer_id,
                'crm_contact_id' => $source->crm_customer_contact_id,
                'status' => SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND,
                'source_channel' => $sourceChannel,
                'created_from_quotation_header_id' => $source->id,
                'quotation_creation_token' => $creationToken,
                'pricing_source' => 'existing_quotation',
                'reference_number' => $this->nullableString($intake['reference_number'] ?? null),
                'sample_description' => $this->nullableString($intake['sample_description'] ?? null),
                'enquiry_notes' => $this->nullableString($intake['enquiry_notes'] ?? null),
                'request_date_of_service' => now()->toDateString(),
                'number_of_samples' => $numberOfSamples,
                'sample_type_id' => $lines[0]['sample_type_id'],
                'batch_sample_type_id' => $lines[0]['sample_type_id'],
            ]);

            $sectionValues = $this->mergeSectionFieldValues($intake);
            if ($sectionValues !== []) {
                $this->fieldMapper->applyHeaderFieldsFromFormData($enquiry, $sectionValues);
                $enquiry->save();
            }

            $trfSectionFieldValues = $this->normalizeTrfSectionFieldValues($intake);
            if ($trfSectionFieldValues !== []) {
                $enquiry->trf_section_field_values = $trfSectionFieldValues;
                $enquiry->save();
            }

            $configs = $this->buildSampleConfigs($lines, $numberOfSamples);
            $sampleDescription = $this->nullableString($intake['sample_description'] ?? null)
                ?? $this->nullableString($sectionValues['sample_description'] ?? null);
            if ($sampleDescription !== null) {
                foreach ($configs as $index => $config) {
                    $configs[$index]['sample_description'] = $sampleDescription;
                }
            }
            $configs = $this->applySupplementalRowFieldsToConfigs($configs, $sectionValues, $intake);
            $this->sampleConfigService->validateConfigs($configs);

            $enquiry->enquiry_sample_configuration = $configs;
            $enquiry->number_of_samples = count($configs);
            $enquiry->save();
            $this->sampleLineSync->syncFromSampleConfigs($enquiry, $configs);

            $this->enquiryQuotationService->linkEnquiryToQuotation(
                $enquiry->fresh() ?? $enquiry,
                $source,
                EnquiryQuotation::LINK_SOURCE_BILLING_WIZARD,
            );
            $enquiry->current_quotation_header_id = $source->id;
            $enquiry->save();

            $this->quotationService->seedEnquirySubcontractFlagsFromQuotation(
                $enquiry->fresh() ?? $enquiry,
                $source->fresh(['details']) ?? $source,
            );

            $this->formInstanceSync->syncAllSampleTypesFromEnquiry($enquiry->fresh([
                'customer',
                'requestedAnalyses',
            ]), submit: true);

            $this->attachTestRequestFormPdfs($enquiry->fresh() ?? $enquiry);

            $enquiry = $enquiry->fresh([
                'currentQuotation.details',
                'submissionFormInstance',
                'requestedAnalyses',
                'contact',
                'enquiryQuotations',
            ]) ?? $enquiry;

            return $this->applyCreationIntent(
                $enquiry,
                $enquiry->currentQuotation ?? $source,
                $intent,
                $intake,
            );
        }, attempts: 3);
    }

    /**
     * Re-apply wizard TRF row fields stored on the enquiry onto sample configs,
     * sample lines, and linked TRF instance values.
     */
    public function refreshSupplementalFieldsOnEnquiry(
        SampleSubmissionRequest $enquiry,
        bool $resyncTrf = true,
    ): SampleSubmissionRequest {
        $configs = is_array($enquiry->enquiry_sample_configuration)
            ? $enquiry->enquiry_sample_configuration
            : [];

        if ($configs === []) {
            return $enquiry;
        }

        $storedByType = is_array($enquiry->trf_section_field_values)
            ? $enquiry->trf_section_field_values
            : [];

        if ($storedByType === []) {
            return $enquiry;
        }

        $sectionValues = $this->mergeSectionFieldValuesFromStored($storedByType);
        $configs = $this->applySupplementalRowFieldsToConfigs($configs, $sectionValues, [
            'section_field_values_by_type' => $storedByType,
        ]);

        $enquiry->enquiry_sample_configuration = $configs;
        $enquiry->save();
        $this->sampleLineSync->syncFromSampleConfigs($enquiry, $configs);

        if ($resyncTrf && $enquiry->submission_form_instance_id) {
            $instance = \App\Models\SubmissionFormInstance::query()->find($enquiry->submission_form_instance_id);
            $submit = $instance !== null
                && in_array(strtolower((string) $instance->status), ['submitted'], true);

            $this->formInstanceSync->syncAllSampleTypesFromEnquiry(
                $enquiry->fresh(['customer', 'requestedAnalyses']) ?? $enquiry,
                submit: $submit,
            );
        }

        return $enquiry->fresh([
            'submissionFormInstance',
            'requestedAnalyses',
        ]) ?? $enquiry;
    }

    /**
     * Create enquiry + TRF, then send the owned quotation to the customer.
     *
     * @param  array<string, mixed>  $intake
     */
    public function createAndSend(
        QuotationHeader $sourceQuotation,
        array $intake,
        string $creationToken,
        bool $sendPortal = false,
        bool $sendEmail = true,
    ): SampleSubmissionRequest {
        $intake['creation_intent'] = self::INTENT_PREPARE;
        $intake['source_channel'] = $this->normalizeSourceChannel($intake['source_channel'] ?? null);

        $enquiry = $this->create($sourceQuotation, $intake, $creationToken);

        // Legacy rows created before origin normalization may still say "quotation".
        if ((string) $enquiry->source_channel === 'quotation') {
            $enquiry->source_channel = CommercialEnquirySyncService::SOURCE_WALK_IN;
            $enquiry->save();
        }

        $header = $enquiry->currentQuotation;
        if ($header === null) {
            throw new RuntimeException('The enquiry quotation could not be prepared for sending.');
        }

        if ($this->quotationService->quotationWasSentToCustomer($enquiry)) {
            return $this->quotationService
                ->ensureEnquiryReflectsSentQuotation($enquiry, $header)
                ->load(['currentQuotation.details', 'submissionFormInstance', 'requestedAnalyses', 'contact']);
        }

        try {
            return $this->quotationService->sendToCustomer(
                $enquiry->loadMissing(['contact', 'customer', 'submissionFormInstance']),
                $header,
                $sendPortal,
                $sendEmail,
            )->load(['currentQuotation.details', 'submissionFormInstance', 'requestedAnalyses', 'contact']);
        } catch (Throwable $exception) {
            // create() already committed READY_TO_SEND; still mark sent if the quote was delivered.
            $enquiry = $enquiry->fresh(['currentQuotation', 'contact', 'customer', 'submissionFormInstance']) ?? $enquiry;
            $header = $enquiry->currentQuotation ?? $header;

            if ($header !== null && $this->quotationService->quotationWasSentToCustomer($enquiry)) {
                return $this->quotationService
                    ->ensureEnquiryReflectsSentQuotation($enquiry, $header)
                    ->load(['currentQuotation.details', 'submissionFormInstance', 'requestedAnalyses', 'contact']);
            }

            throw $exception;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function eligibleQuotationLines(QuotationHeader $quotation): array
    {
        $quotation->loadMissing('details');

        if ((string) $quotation->status !== QuotationApprovalService::HEADER_STATUS_COMPLETE) {
            throw new RuntimeException('Only completed quotations can be used to create an enquiry.');
        }

        if (strcasecmp((string) ($quotation->quotation_type ?? ''), 'Analysis') !== 0) {
            throw new RuntimeException('Only analysis quotations can be converted into laboratory enquiries.');
        }

        if (trim((string) ($quotation->crm_customer_id ?? '')) === '') {
            throw new RuntimeException('The quotation must be linked to a CRM customer.');
        }

        if ($quotation->expiring_date === null
            || Carbon::parse($quotation->expiring_date)->startOfDay()->lt(now()->startOfDay())) {
            throw new RuntimeException('The quotation has expired and cannot create a new enquiry.');
        }

        if ($quotation->details->isEmpty()) {
            throw new RuntimeException('The quotation has no analysis lines.');
        }

        $lines = $this->quotationService->buildInlineLinesFromQuotationHeader($quotation);
        if ($lines === []) {
            throw new RuntimeException('The quotation has no laboratory analyses that can be mapped.');
        }

        foreach ($lines as $line) {
            if (trim((string) ($line['sample_type_id'] ?? '')) === '') {
                throw new RuntimeException('Every quotation line must have a sample type before creating an enquiry.');
            }

            $elementId = trim((string) ($line['analysis_element_id'] ?? ''));
            $packageElementIds = array_values(array_filter(
                is_array($line['package_element_ids'] ?? null) ? $line['package_element_ids'] : [],
                static fn (mixed $id): bool => trim((string) $id) !== '',
            ));

            if ($elementId === '' && $packageElementIds === []) {
                throw new RuntimeException(
                    'Every quotation line must contain explicitly selected parameters before creating an enquiry.'
                );
            }
        }

        return $lines;
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function inferPhysicalSampleCount(array $lines): int
    {
        $grouped = collect($lines)->groupBy(
            static fn (array $line): string => trim((string) ($line['sample_type_id'] ?? '')),
        );

        $total = 0;
        foreach ($grouped as $group) {
            $total += max(1, (int) $group->max(
                static fn (array $line): int => max(
                    1,
                    (int) ($line['physical_sample_count'] ?? $line['quantity'] ?? 1),
                )
            ));
        }

        return max(1, $total);
    }

    public function resolveTrfFormForQuotation(QuotationHeader $quotation): ?SubmissionForm
    {
        $groups = $this->fillableTrfGroups($quotation);

        if ($groups === []) {
            return null;
        }

        $formId = trim((string) ($groups[0]['form_id'] ?? ''));
        if ($formId === '') {
            return null;
        }

        return SubmissionForm::query()->find($formId);
    }

    /**
     * One TRF group per sample type on the quotation (for the wizard).
     *
     * @return list<array{
     *     sample_type_id: string,
     *     sample_type_name: string,
     *     form_id: string|null,
     *     form_name: string,
     *     sections: list<array{
     *         id: string,
     *         title: string,
     *         description: string,
     *         section_type: string,
     *         default_selected: bool,
     *         fields: list<array{name: string, label: string, element_type: string, is_required: bool, options: list<array{value: string, label: string}>}>
     *     }>
     * }>
     */
    public function fillableTrfGroups(QuotationHeader $quotation): array
    {
        $lines = $this->eligibleQuotationLines($quotation);
        $crmCustomerId = trim((string) ($quotation->crm_customer_id ?? ''));
        $crmCustomerId = $crmCustomerId !== '' ? $crmCustomerId : null;

        $form = $this->formInstanceSync->resolveSubmissionFormForEnquiryLines($lines, $crmCustomerId);
        $sections = $form !== null ? $this->fillableSectionsForForm($form) : [];

        $typeNames = \App\SampleType::query()
            ->whereIn('id', collect($lines)->pluck('sample_type_id')->filter()->unique()->all())
            ->pluck('name')
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        return [[
            'sample_type_id' => self::UNIFIED_TRF_FIELD_KEY,
            'sample_type_name' => $typeNames !== '' ? $typeNames : (string) ($form?->name ?? 'Test Request Form'),
            'form_id' => $form?->id !== null ? (string) $form->id : null,
            'form_name' => (string) ($form?->name ?? $form?->document_code ?? 'Test Request Form'),
            'sections' => $sections,
        ]];
    }

    /**
     * @deprecated Use fillableTrfGroups()
     *
     * @return list<array<string, mixed>>
     */
    public function fillableTrfSections(QuotationHeader $quotation): array
    {
        $groups = $this->fillableTrfGroups($quotation);

        return $groups[0]['sections'] ?? [];
    }

    /**
     * @return list<array{
     *     id: string,
     *     title: string,
     *     description: string,
     *     section_type: string,
     *     default_selected: bool,
     *     fields: list<array{name: string, label: string, element_type: string, is_required: bool, options: list<array{value: string, label: string}>}>
     * }>
     */
    private function fillableSectionsForForm(SubmissionForm $form): array
    {
        $form->loadMissing(['sections.elementHolders.elements']);

        $sections = [];
        foreach ($form->sections->sortBy('sort_order') as $section) {
            if (! $section instanceof SubmissionFormSection) {
                continue;
            }
            if ($section->isHidden()) {
                continue;
            }
            if ($this->isCustomerDetailsSection($section)) {
                continue;
            }

            $fields = $this->wizardFieldsForSection($section);
            $sections[] = [
                'id' => (string) $section->id,
                'title' => (string) ($section->title ?? 'Section'),
                'description' => (string) ($section->description ?? ''),
                'section_type' => (string) ($section->section_type ?? 'regular'),
                'default_selected' => $this->defaultSectionSelected($section),
                'fields' => $fields,
            ];
        }

        return $sections;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function isRowIndexedTrfSectionValues(array $values): bool
    {
        if ($values === [] || ! isset($values[0]) || ! is_array($values[0])) {
            return false;
        }

        $rowFieldNames = [
            'sample_description',
            'sample_quantity',
            'sample_quantity_unit',
            'location',
            'sampling_point',
            'production_date',
            'expiration_date',
            'batch_number',
            'test_category',
            'test_requirements',
            'parameter_category',
            'field_ph',
            'field_appearance',
            'field_residual_chlorine',
            'field_odor',
            'field_sample_temp',
            'sample_condition',
            'state_of_sample',
        ];

        foreach ($rowFieldNames as $name) {
            if (array_key_exists($name, $values[0])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string|int, mixed>  $typeValues
     * @return array<string, mixed>
     */
    public static function resolveTrfSectionRowFields(array $typeValues, int $rowIndex): array
    {
        if (self::isRowIndexedTrfSectionValues($typeValues)) {
            $row = $typeValues[$rowIndex] ?? $typeValues[0] ?? [];

            return is_array($row) ? $row : [];
        }

        return $typeValues;
    }

    /**
     * @param  array<string, mixed>  $intake
     * @return array<string, mixed>
     */
    private function mergeSectionFieldValues(array $intake): array
    {
        $merged = is_array($intake['section_field_values'] ?? null)
            ? $intake['section_field_values']
            : [];

        $byType = is_array($intake['section_field_values_by_type'] ?? null)
            ? $intake['section_field_values_by_type']
            : [];

        foreach ($byType as $values) {
            if (! is_array($values)) {
                continue;
            }

            if (self::isRowIndexedTrfSectionValues($values)) {
                $firstRow = $values[0] ?? [];
                if (is_array($firstRow)) {
                    foreach ($firstRow as $key => $value) {
                        if ($value === null || $value === '') {
                            continue;
                        }
                        $merged[$key] = $value;
                    }
                }

                continue;
            }

            foreach ($values as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $intake
     * @return array<string, array<string, mixed>>
     */
    private function normalizeTrfSectionFieldValues(array $intake): array
    {
        $byType = is_array($intake['section_field_values_by_type'] ?? null)
            ? $intake['section_field_values_by_type']
            : [];

        $normalized = [];

        foreach ($byType as $sampleTypeId => $values) {
            $sampleTypeId = trim((string) $sampleTypeId);
            if ($sampleTypeId === '' || ! is_array($values)) {
                continue;
            }

            if (self::isRowIndexedTrfSectionValues($values)) {
                $rows = [];
                foreach ($values as $rowIndex => $row) {
                    if (! is_int($rowIndex) && ! (is_string($rowIndex) && ctype_digit($rowIndex))) {
                        continue;
                    }
                    if (! is_array($row)) {
                        continue;
                    }

                    $normalizedRow = [];
                    foreach ($row as $key => $value) {
                        $key = trim((string) $key);
                        if ($key === '') {
                            continue;
                        }
                        if ($value === null || $value === '' || $value === []) {
                            continue;
                        }
                        $normalizedRow[$key] = $value;
                    }

                    if ($normalizedRow !== []) {
                        $rows[] = $normalizedRow;
                    }
                }

                if ($rows !== []) {
                    $normalized[$sampleTypeId] = $rows;
                }

                continue;
            }

            $row = [];
            foreach ($values as $key => $value) {
                $key = trim((string) $key);
                if ($key === '') {
                    continue;
                }
                if ($value === null || $value === '' || $value === []) {
                    continue;
                }
                $row[$key] = $value;
            }

            if ($row !== []) {
                $normalized[$sampleTypeId] = $row;
            }
        }

        return $normalized;
    }

    /**
     * @param  array<string, array<string, mixed>>  $byType
     * @return array<string, mixed>
     */
    private function mergeSectionFieldValuesFromStored(array $byType): array
    {
        $merged = [];

        foreach ($byType as $values) {
            if (! is_array($values)) {
                continue;
            }

            if (self::isRowIndexedTrfSectionValues($values)) {
                $firstRow = $values[0] ?? [];
                if (is_array($firstRow)) {
                    foreach ($firstRow as $key => $value) {
                        if ($value === null || $value === '' || $value === []) {
                            continue;
                        }
                        $merged[(string) $key] = $value;
                    }
                }

                continue;
            }

            foreach ($values as $key => $value) {
                if ($value === null || $value === '' || $value === []) {
                    continue;
                }
                $merged[(string) $key] = $value;
            }
        }

        return $merged;
    }

    /**
     * @return list<string>
     */
    public static function creationIntents(): array
    {
        return [
            self::INTENT_PREPARE,
            self::INTENT_ALREADY_SENT,
            self::INTENT_ACCEPTED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function allowedSourceChannels(): array
    {
        return [
            CommercialEnquirySyncService::SOURCE_WALK_IN,
            CommercialEnquirySyncService::SOURCE_PORTAL,
            CommercialEnquirySyncService::SOURCE_SCHEDULED,
            CommercialEnquirySyncService::SOURCE_OFFLINE,
        ];
    }

    private function normalizeIntent(?string $intent): string
    {
        $intent = trim((string) $intent);
        if ($intent === '' || ! in_array($intent, self::creationIntents(), true)) {
            return self::INTENT_PREPARE;
        }

        return $intent;
    }

    private function normalizeSourceChannel(?string $channel): string
    {
        $channel = strtolower(trim((string) $channel));
        // "quotation" / pricing source labels are not receiving origins.
        if (in_array($channel, ['quotation', 'existing_quotation', 'from_quotation'], true)) {
            return CommercialEnquirySyncService::SOURCE_WALK_IN;
        }
        if ($channel === '' || ! in_array($channel, self::allowedSourceChannels(), true)) {
            return CommercialEnquirySyncService::SOURCE_WALK_IN;
        }

        return $channel;
    }

    private function isCustomerDetailsSection(SubmissionFormSection $section): bool
    {
        $title = strtolower(trim((string) ($section->title ?? '')));
        foreach (self::CUSTOMER_SECTION_TITLE_MARKERS as $marker) {
            if ($title === $marker || str_contains($title, $marker)) {
                return true;
            }
        }

        foreach ($section->elementHolders as $holder) {
            foreach ($holder->elements as $element) {
                $name = strtolower(trim((string) ($element->name ?? '')));
                if (in_array($name, ['crm_customer_id'], true)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function defaultSectionSelected(SubmissionFormSection $section): bool
    {
        $title = strtolower(trim((string) ($section->title ?? '')));
        if (str_contains($title, 'request header') || str_contains($title, 'test & sample') || str_contains($title, 'declaration')) {
            return true;
        }

        return (string) ($section->section_type ?? '') === 'rows_section';
    }

    /**
     * @return list<array{
     *     name: string,
     *     label: string,
     *     element_type: string,
     *     is_required: bool,
     *     options: list<array{value: string, label: string}>,
     *     hint?: string,
     *     render_paired?: bool,
     *     unit_options?: list<array{value: string, label: string}>
     * }>
     */
    private function wizardFieldsForSection(SubmissionFormSection $section): array
    {
        $fields = [];
        $reportingUnitOptions = null;

        foreach ($section->elementHolders as $holder) {
            foreach ($holder->elements as $element) {
                if ((bool) ($element->is_hidden ?? false)) {
                    continue;
                }

                $type = strtolower(trim((string) ($element->element_type ?? 'text')));
                if (in_array($type, self::WIZARD_UNSUPPORTED_ELEMENT_TYPES, true)) {
                    continue;
                }

                $name = trim((string) ($element->name ?? ''));
                if ($name === '' || in_array($name, self::WIZARD_EXCLUDED_FIELD_NAMES, true)) {
                    continue;
                }

                $options = [];
                $rawOptions = is_array($element->options ?? null) ? $element->options : [];
                foreach ($rawOptions as $option) {
                    if (! is_array($option)) {
                        continue;
                    }
                    $value = trim((string) ($option['value'] ?? ''));
                    if ($value === '') {
                        continue;
                    }
                    $options[] = [
                        'value' => $value,
                        'label' => (string) ($option['label'] ?? $value),
                    ];
                }

                $field = [
                    'name' => $name,
                    'label' => (string) ($element->label ?? $name),
                    'element_type' => $type,
                    'is_required' => (bool) ($element->is_required ?? false),
                    'options' => $options,
                ];

                if ($name === 'sample_description') {
                    $field['element_type'] = 'rich_text';
                }

                if ($name === 'sample_quantity') {
                    $field['element_type'] = 'number';
                    $reportingUnitOptions ??= $this->reportingUnitOptions();
                    $field['unit_options'] = $reportingUnitOptions;
                }

                if ($name === 'sample_quantity_unit') {
                    $reportingUnitOptions ??= $this->reportingUnitOptions();
                    $field['element_type'] = 'select';
                    $field['options'] = $reportingUnitOptions;
                    $field['render_paired'] = true;
                }

                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function reportingUnitOptions(): array
    {
        return ReportingUnit::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get(['name'])
            ->map(static fn (ReportingUnit $unit): array => [
                'value' => (string) $unit->name,
                'label' => (string) $unit->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $intake
     */
    private function applyCreationIntent(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
        string $intent,
        array $intake,
    ): SampleSubmissionRequest {
        if ($intent === self::INTENT_ALREADY_SENT) {
            $sentAt = now();
            $this->enquiryQuotationService->recordSentToCustomer(
                $enquiry,
                $quotation,
                sentViaPortal: true,
                sentViaEmail: false,
                sentAt: $sentAt,
                linkSource: EnquiryQuotation::LINK_SOURCE_BILLING_WIZARD,
            );

            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_SENT;
            $enquiry->quotation_first_sent_to_customer_at = $sentAt;
            $enquiry->save();

            return $enquiry->fresh([
                'currentQuotation.details',
                'submissionFormInstance',
                'requestedAnalyses',
                'contact',
                'enquiryQuotations',
            ]) ?? $enquiry;
        }

        if ($intent === self::INTENT_ACCEPTED) {
            $sentAt = now();
            $this->enquiryQuotationService->recordSentToCustomer(
                $enquiry,
                $quotation,
                sentViaPortal: true,
                sentViaEmail: false,
                sentAt: $sentAt,
                linkSource: EnquiryQuotation::LINK_SOURCE_BILLING_WIZARD,
            );
            $this->enquiryQuotationService->recordAcceptance(
                $enquiry,
                $quotation,
                [
                    'channel' => QuotationHeader::ACCEPTANCE_CHANNEL_WALK_IN,
                ],
                acceptedAt: $sentAt,
                linkSource: EnquiryQuotation::LINK_SOURCE_BILLING_WIZARD,
            );

            $enquiry->quotation_first_sent_to_customer_at = $enquiry->quotation_first_sent_to_customer_at ?? $sentAt;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED;
            $enquiry->accepted_quotation_header_id = (string) $quotation->id;
            $enquiry->quotation_accepted_at = $sentAt;
            $enquiry->save();

            $poNumber = $this->nullableString($intake['client_po_number'] ?? null)
                ?? $this->nullableString($intake['reference_number'] ?? null);

            return $this->receptionReadinessService->markReadyForReception(
                $enquiry->fresh() ?? $enquiry,
                (string) $quotation->id,
                [
                    'client_po_number' => $poNumber,
                    'po_skipped' => filter_var($intake['po_skipped'] ?? ($poNumber === null), FILTER_VALIDATE_BOOLEAN),
                ],
            )->load(['currentQuotation.details', 'submissionFormInstance', 'requestedAnalyses', 'contact', 'enquiryQuotations']);
        }

        return $enquiry;
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    private function buildSampleConfigs(array $lines, int $numberOfSamples): array
    {
        $groups = collect($lines)->groupBy(
            static fn (array $line): string => trim((string) ($line['sample_type_id'] ?? '')),
        );
        $singleType = $groups->count() === 1;
        $prefill = [];
        $rowIndex = 0;

        foreach ($groups as $sampleTypeId => $groupLines) {
            $group = $groupLines->values();
            $elementIds = $group
                ->flatMap(function (array $line): array {
                    $ids = is_array($line['package_element_ids'] ?? null)
                        ? $line['package_element_ids']
                        : [];
                    $elementId = trim((string) ($line['analysis_element_id'] ?? ''));
                    if ($elementId !== '') {
                        array_unshift($ids, $elementId);
                    }

                    return $ids;
                })
                ->map(static fn (mixed $id): string => trim((string) $id))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $subcontractedElementIds = $group
                ->filter(static fn (array $line): bool => ! empty($line['subcontracted']))
                ->flatMap(function (array $line): array {
                    $ids = is_array($line['package_element_ids'] ?? null)
                        ? $line['package_element_ids']
                        : [];
                    $elementId = trim((string) ($line['analysis_element_id'] ?? ''));
                    if ($elementId !== '') {
                        array_unshift($ids, $elementId);
                    }

                    return $ids;
                })
                ->map(static fn (mixed $id): string => trim((string) $id))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $analysisTypeIds = $group
                ->pluck('analysis_type_id')
                ->map(static fn (mixed $id): string => trim((string) $id))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $groupSampleCount = $singleType
                ? $numberOfSamples
                : max(1, (int) $group->max(
                    static fn (array $line): int => max(
                        1,
                        (int) ($line['physical_sample_count'] ?? $line['quantity'] ?? 1),
                    )
                ));

            $first = $group->first();
            $prefill[] = [
                'row_index' => $rowIndex,
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeIds[0] ?? null,
                'analysis_element_id' => $elementIds[0] ?? null,
                'parameter_label' => (string) ($first['parameter_label'] ?? 'Parameter'),
                'number_of_samples' => $groupSampleCount,
                'attributes' => [
                    'analysis_element_ids' => $elementIds,
                    'analysis_type_ids' => $analysisTypeIds,
                    'subcontracted_parameter_keys' => $subcontractedElementIds,
                ],
            ];
            $rowIndex++;
        }

        $configs = $this->sampleConfigService->normalizeConfigsAnalysisTypeIds(
            $this->sampleConfigService->buildConfigsFromPrefill($prefill),
        );

        foreach ($configs as $index => $config) {
            $parameterKeys = array_values(array_map(
                'strval',
                is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : []
            ));
            $seedKeys = array_values(array_map(
                'strval',
                $prefill[$index]['attributes']['subcontracted_parameter_keys'] ?? []
            ));
            $configs[$index]['subcontracted_parameter_keys'] = $this->sampleConfigService
                ->normalizeSubcontractedParameterKeys($seedKeys, $parameterKeys);
        }

        return $configs;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }

    /**
     * Copy wizard supplemental row fields (Qty/Unit, test category, etc.) onto sample configs.
     * Never maps Qty into number_of_samples.
     *
     * @param  list<array<string, mixed>>  $configs
     * @param  array<string, mixed>  $sectionValues
     * @param  array<string, mixed>  $intake
     * @return list<array<string, mixed>>
     */
    private function applySupplementalRowFieldsToConfigs(array $configs, array $sectionValues, array $intake = []): array
    {
        $byType = is_array($intake['section_field_values_by_type'] ?? null)
            ? $intake['section_field_values_by_type']
            : [];

        $rowKeys = [
            'sample_description',
            'sample_quantity',
            'sample_quantity_unit',
            'location',
            'sampling_point',
            'production_date',
            'expiration_date',
            'batch_number',
            'test_category',
            'test_requirements',
            'parameter_category',
            'field_ph',
            'field_appearance',
            'field_residual_chlorine',
            'field_odor',
            'field_sample_temp',
            'sample_condition',
            'state_of_sample',
        ];

        $typeRowCounters = [];

        $unifiedRaw = is_array($byType[EnquiryFromQuotationService::UNIFIED_TRF_FIELD_KEY] ?? null)
            ? $byType[EnquiryFromQuotationService::UNIFIED_TRF_FIELD_KEY]
            : null;
        $globalRowIndex = 0;

        foreach ($configs as $index => $config) {
            $typeId = trim((string) ($config['sample_type_id'] ?? ''));
            $rowWithinType = $typeRowCounters[$typeId] ?? 0;
            $typeRowCounters[$typeId] = $rowWithinType + 1;

            if ($unifiedRaw !== null) {
                $typed = self::resolveTrfSectionRowFields($unifiedRaw, $globalRowIndex);
                $globalRowIndex++;
            } else {
                $typedRaw = ($typeId !== '' && is_array($byType[$typeId] ?? null))
                    ? $byType[$typeId]
                    : [];
                $typed = self::resolveTrfSectionRowFields($typedRaw, $rowWithinType);
            }
            $merged = array_merge($sectionValues, $typed);

            foreach ($rowKeys as $key) {
                if (! array_key_exists($key, $merged)) {
                    continue;
                }
                $value = $this->stringifySupplementalFieldValue($merged[$key]);
                if ($value === null) {
                    continue;
                }
                $configs[$index][$key] = $value;
            }
        }

        return $configs;
    }

    private function stringifySupplementalFieldValue(mixed $value): ?string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            $parts = [];
            $isAssocMap = array_keys($value) !== range(0, count($value) - 1);
            if ($isAssocMap) {
                foreach ($value as $option => $checked) {
                    if ($checked === true || $checked === 1 || $checked === '1' || $checked === 'true') {
                        $token = trim((string) $option);
                        if ($token !== '') {
                            $parts[] = $token;
                        }
                    }
                }
            } else {
                foreach ($value as $item) {
                    $token = trim((string) $item);
                    if ($token !== '') {
                        $parts[] = $token;
                    }
                }
            }

            return $parts !== [] ? implode(',', $parts) : null;
        }

        return $this->nullableString($value);
    }

    private function attachTestRequestFormPdfs(SampleSubmissionRequest $enquiry): void
    {
        $instances = SubmissionFormInstance::query()
            ->where('portal_request_id', (string) $enquiry->id)
            ->get();

        if ($instances->isEmpty() && $enquiry->submission_form_instance_id) {
            $primary = SubmissionFormInstance::query()->find($enquiry->submission_form_instance_id);
            if ($primary !== null) {
                $instances = collect([$primary]);
            }
        }

        if ($instances->isEmpty()) {
            return;
        }

        $uploaderId = Auth::id() !== null ? (string) Auth::id() : null;
        $attachmentService = app(SubmissionFormInstanceDocumentAttachmentService::class);

        foreach ($instances as $instance) {
            try {
                $attachmentService->attachTestRequestForm(
                    $instance->fresh(['values.element', 'submissionForm', 'crmCustomer']),
                    $uploaderId,
                    regenerate: true,
                );
            } catch (Throwable $exception) {
                Log::warning('Failed to attach test request form PDF after enquiry-from-quotation create.', [
                    'enquiry_id' => $enquiry->id,
                    'instance_id' => $instance->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }
}
