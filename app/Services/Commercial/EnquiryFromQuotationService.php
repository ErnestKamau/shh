<?php

namespace App\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormSection;
use App\QuotationDetailAnalysisSplit;
use App\QuotationHeader;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final class EnquiryFromQuotationService
{
    /** @deprecated Use CommercialEnquirySyncService::SOURCE_WALK_IN — kept for call-site updates. */
    public const SOURCE_CHANNEL = CommercialEnquirySyncService::SOURCE_WALK_IN;

    public const INTENT_PREPARE = 'prepare';

    public const INTENT_ALREADY_SENT = 'already_sent';

    public const INTENT_ACCEPTED = 'accepted';

    /** @var list<string> */
    private const CUSTOMER_SECTION_TITLE_MARKERS = [
        'customer details',
        'client details',
        'customer information',
        'client information',
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

            $ownedQuotation = $this->cloneQuotationForEnquiry($source, $enquiry);
            $enquiry->current_quotation_header_id = $ownedQuotation->id;
            $enquiry->save();

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

            $this->quotationService->seedEnquirySubcontractFlagsFromQuotation(
                $enquiry->fresh() ?? $enquiry,
                $ownedQuotation->fresh(['details']) ?? $ownedQuotation,
            );

            $this->formInstanceSync->syncAllSampleTypesFromEnquiry($enquiry->fresh([
                'customer',
                'requestedAnalyses',
            ]), submit: false);

            $enquiry = $enquiry->fresh([
                'currentQuotation.details',
                'submissionFormInstance',
                'requestedAnalyses',
                'contact',
            ]) ?? $enquiry;

            return $this->applyCreationIntent(
                $enquiry,
                $enquiry->currentQuotation ?? $ownedQuotation,
                $intent,
                $intake,
            );
        }, attempts: 3);
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

            if ($header !== null && $header->sent_to_customer_at !== null) {
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
        $typeIds = collect($lines)
            ->map(static fn (array $line): string => trim((string) ($line['sample_type_id'] ?? '')))
            ->filter()
            ->unique()
            ->values();

        $typeNames = \App\SampleType::query()
            ->whereIn('id', $typeIds->all())
            ->pluck('name', 'id');

        $groups = [];
        foreach ($typeIds as $sampleTypeId) {
            $form = $this->formInstanceSync->resolveSubmissionFormForSampleType($sampleTypeId);
            $sections = $form !== null ? $this->fillableSectionsForForm($form) : [];

            $groups[] = [
                'sample_type_id' => $sampleTypeId,
                'sample_type_name' => (string) ($typeNames[$sampleTypeId] ?? 'Sample type'),
                'form_id' => $form?->id !== null ? (string) $form->id : null,
                'form_name' => (string) ($form?->name ?? $form?->document_code ?? 'Test Request Form'),
                'sections' => $sections,
            ];
        }

        return $groups;
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
     * @return list<array{name: string, label: string, element_type: string, is_required: bool, options: list<array{value: string, label: string}>}>
     */
    private function wizardFieldsForSection(SubmissionFormSection $section): array
    {
        $fields = [];

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
                if ($name === '') {
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

                $fields[] = [
                    'name' => $name,
                    'label' => (string) ($element->label ?? $name),
                    'element_type' => $type,
                    'is_required' => (bool) ($element->is_required ?? false),
                    'options' => $options,
                ];
            }
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $intake
     */
    private function applyCreationIntent(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $ownedQuotation,
        string $intent,
        array $intake,
    ): SampleSubmissionRequest {
        if ($intent === self::INTENT_ALREADY_SENT) {
            $sentAt = now();
            $ownedQuotation->sent_to_customer_at = $sentAt;
            $ownedQuotation->save();

            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_SENT;
            $enquiry->quotation_first_sent_to_customer_at = $sentAt;
            $enquiry->save();

            return $enquiry->fresh([
                'currentQuotation.details',
                'submissionFormInstance',
                'requestedAnalyses',
                'contact',
            ]) ?? $enquiry;
        }

        if ($intent === self::INTENT_ACCEPTED) {
            $sentAt = $ownedQuotation->sent_to_customer_at ?? now();
            $ownedQuotation->sent_to_customer_at = $sentAt;
            $ownedQuotation->is_approved = 1;
            $ownedQuotation->is_complete = 1;
            $ownedQuotation->save();

            $enquiry->quotation_first_sent_to_customer_at = $enquiry->quotation_first_sent_to_customer_at ?? $sentAt;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED;
            $enquiry->save();

            $poNumber = $this->nullableString($intake['client_po_number'] ?? null)
                ?? $this->nullableString($intake['reference_number'] ?? null);

            return $this->receptionReadinessService->markReadyForReception(
                $enquiry->fresh() ?? $enquiry,
                (string) $ownedQuotation->id,
                [
                    'client_po_number' => $poNumber,
                    'po_skipped' => filter_var($intake['po_skipped'] ?? ($poNumber === null), FILTER_VALIDATE_BOOLEAN),
                ],
            )->load(['currentQuotation.details', 'submissionFormInstance', 'requestedAnalyses', 'contact']);
        }

        return $enquiry;
    }

    private function cloneQuotationForEnquiry(
        QuotationHeader $source,
        SampleSubmissionRequest $enquiry,
    ): QuotationHeader {
        $clone = $source->replicate([
            'quote_number',
            'laboratory_ref',
            'sample_submission_request_id',
            'source_quotation_header_id',
            'revision_of_quotation_header_id',
            'sent_to_customer_at',
            'upload_url',
            'customer_acceptance_signature',
            'customer_acceptance_signer_name',
            'customer_acceptance_signed_at',
            'customer_acceptance_contact_id',
            'customer_acceptance_channel',
            'approval_requested_at',
            'approval_requested_by',
            'approval_decision_at',
            'approval_comments',
        ]);

        $clone->quote_number = $this->ownedQuotationNumber($source);
        $clone->laboratory_ref = $clone->quote_number;
        $clone->sample_submission_request_id = $enquiry->id;
        $clone->source_quotation_header_id = $source->id;
        $clone->from_enquiry = true;
        $clone->status = QuotationApprovalService::HEADER_STATUS_COMPLETE;
        $clone->is_draft = 0;
        $clone->is_complete = 1;
        $clone->is_approved = 1;
        $clone->save();

        foreach ($source->details as $detail) {
            $clonedDetail = $detail->replicate(['quotation_header_id']);
            $clonedDetail->quotation_header_id = $clone->id;
            $clonedDetail->save();

            QuotationDetailAnalysisSplit::query()
                ->where('quotation_detail_id', $detail->id)
                ->each(function (QuotationDetailAnalysisSplit $split) use ($clonedDetail): void {
                    QuotationDetailAnalysisSplit::query()->create([
                        'quotation_detail_id' => $clonedDetail->id,
                        'analysis_type_id' => $split->analysis_type_id,
                    ]);
                });
        }

        return $clone->fresh(['details']) ?? $clone;
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

    private function ownedQuotationNumber(QuotationHeader $source): string
    {
        $date = $source->quote_date
            ? Carbon::parse($source->quote_date)
            : now();

        return AmSpecQuotationNumberGenerator::generate($date);
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

        foreach ($configs as $index => $config) {
            $typeId = trim((string) ($config['sample_type_id'] ?? ''));
            $typed = ($typeId !== '' && is_array($byType[$typeId] ?? null))
                ? $byType[$typeId]
                : [];
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
}
