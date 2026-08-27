<?php

namespace App\Livewire\Billing;

use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\SamplePoint;
use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\Services\Commercial\CommercialEnquirySyncService;
use App\Services\Commercial\EnquiryFromQuotationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class CreateEnquiryFromQuotationWizard extends Component
{
    use AuthorizesRequests;

    public bool $show = false;

    /** review | sections | fill | send */
    public string $phase = 'review';

    public int $trfIndex = 0;

    public ?string $quotationId = null;

    public string $creationToken = '';

    public string $sourceChannel = CommercialEnquirySyncService::SOURCE_WALK_IN;

    public int $numberOfSamples = 1;

    public string $referenceNumber = '';

    public string $sampleDescription = '';

    public string $enquiryNotes = '';

    /** @var array<string, list<string>> sample_type_id => section ids */
    public array $selectedSectionIdsByType = [];

    /** @var array<string, array<string, mixed>> sample_type_id => non-row section field values */
    public array $sectionFieldValuesByType = [];

    /** @var array<string, array<int, array<string, mixed>>> sample_type_id => row index => row field values */
    public array $sectionRowFieldValuesByType = [];

    public bool $sendEmail = true;

    public bool $sendPortal = false;

    public bool $quoteAlreadySentFromBilling = false;

    public bool $notifyAgain = false;

    public string $errorMessage = '';

    /** @var list<array<string, mixed>> */
    public array $quoteLines = [];

    /** @var list<array<string, mixed>> */
    public array $trfGroups = [];

    public string $quoteNumber = '';

    public string $customerName = '';

    public bool $isMultiSampleType = false;

    /** CRM customer from quotation header (drives sampling-location options). */
    public string $crmCustomerId = '';

    /**
     * Walk-in-compatible CRM form bag (company unit for sample-point filtering).
     *
     * @var array<string, mixed>
     */
    public array $formData = [];

    #[On('open-create-enquiry-from-quotation')]
    public function open(string $quotationId): void
    {
        $this->authorize('laboratory.components.quotation.add');
        $this->resetWizardState();

        try {
            $quotation = QuotationHeader::query()
                ->with(['details', 'customer'])
                ->findOrFail($quotationId);

            $service = app(EnquiryFromQuotationService::class);
            $lines = $service->eligibleQuotationLines($quotation);
            $groups = $service->fillableTrfGroups($quotation);

            $this->quotationId = (string) $quotation->id;
            $pendingToken = SampleSubmissionRequest::query()
                ->where('created_from_quotation_header_id', $quotation->id)
                ->where('status', SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND)
                ->whereNotNull('quotation_creation_token')
                ->latest('created_at')
                ->value('quotation_creation_token');
            $this->creationToken = is_string($pendingToken) && $pendingToken !== ''
                ? $pendingToken
                : (string) Str::uuid();
            $this->quoteNumber = (string) ($quotation->quote_number ?? '');
            $this->customerName = (string) ($quotation->customer?->name ?? '');
            $this->numberOfSamples = $service->inferPhysicalSampleCount($lines);
            $this->isMultiSampleType = collect($lines)
                ->map(static fn (array $line): string => trim((string) ($line['sample_type_id'] ?? '')))
                ->filter()
                ->unique()
                ->count() > 1;
            $this->quoteLines = collect($lines)->map(static fn (array $line): array => [
                'parameter_label' => (string) ($line['parameter_label'] ?? $line['description'] ?? 'Parameter'),
                'sample_type_id' => (string) ($line['sample_type_id'] ?? ''),
                'quantity' => (int) ($line['physical_sample_count'] ?? $line['quantity'] ?? 1),
                'unit_price' => $line['unit_price'] ?? null,
            ])->values()->all();
            $this->trfGroups = $groups;
            $this->quoteAlreadySentFromBilling = $quotation->wasSentFromBilling();
            $this->notifyAgain = false;
            if ($this->quoteAlreadySentFromBilling) {
                $this->sendEmail = false;
                $this->sendPortal = false;
            }
            $this->seedSelectionsForGroups();
            $this->seedCrmFromQuotation($quotation);
            $this->applyDeliveryDefaultsForChannel();
            if ($this->quoteAlreadySentFromBilling) {
                $this->sendEmail = false;
                $this->sendPortal = false;
            }
            $this->show = true;
            $this->phase = 'review';
            $this->trfIndex = 0;
        } catch (Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
            $this->show = true;
            $this->phase = 'review';
            $this->quotationId = null;
        }
    }

    public function close(): void
    {
        $this->show = false;
        $this->resetWizardState();
    }

    public function nextStep(): void
    {
        $this->errorMessage = '';

        if ($this->phase === 'review') {
            $rules = [
                'sourceChannel' => ['required', 'in:'.implode(',', EnquiryFromQuotationService::allowedSourceChannels())],
                'referenceNumber' => ['nullable', 'string', 'max:255'],
                'sampleDescription' => ['nullable', 'string', 'max:5000'],
                'enquiryNotes' => ['nullable', 'string', 'max:5000'],
            ];
            if (! $this->isMultiSampleType) {
                $rules['numberOfSamples'] = ['required', 'integer', 'min:1', 'max:10000'];
            }
            $this->validate($rules);
            $this->applyDeliveryDefaultsForChannel();

            if ($this->trfGroups === []) {
                $this->phase = 'send';

                return;
            }

            $this->trfIndex = 0;
            $this->phase = 'sections';

            return;
        }

        if ($this->phase === 'sections') {
            $typeId = $this->currentSampleTypeId();
            $this->validate([
                'selectedSectionIdsByType.'.$typeId => ['array'],
                'selectedSectionIdsByType.'.$typeId.'.*' => ['string'],
            ]);
            $this->ensureRowFieldBucketsForType($typeId);
            $this->phase = 'fill';

            return;
        }

        if ($this->phase === 'fill') {
            $this->syncOpenSampleRowEditorsBeforeSubmit();
            $this->validateCurrentTypeRequiredFields();
            if ($this->trfIndex + 1 < count($this->trfGroups)) {
                $this->trfIndex++;
                $this->phase = 'sections';

                return;
            }
            $this->phase = 'send';
        }
    }

    public function previousStep(): void
    {
        $this->errorMessage = '';

        if ($this->phase === 'send') {
            if ($this->trfGroups === []) {
                $this->phase = 'review';

                return;
            }
            $this->trfIndex = count($this->trfGroups) - 1;
            $this->phase = 'fill';

            return;
        }

        if ($this->phase === 'fill') {
            $this->phase = 'sections';

            return;
        }

        if ($this->phase === 'sections') {
            if ($this->trfIndex > 0) {
                $this->trfIndex--;
                $this->phase = 'fill';

                return;
            }
            $this->phase = 'review';
        }
    }

    public function updatedSourceChannel(): void
    {
        $this->applyDeliveryDefaultsForChannel();
    }

    public function updatedNumberOfSamples(): void
    {
        // Sample counts are locked to the quotation; ignore Livewire updates.
        if ($this->quotationId === null) {
            return;
        }

        try {
            $quotation = QuotationHeader::query()->with('details')->find($this->quotationId);
            if ($quotation === null) {
                return;
            }
            $service = app(EnquiryFromQuotationService::class);
            $this->numberOfSamples = $service->inferPhysicalSampleCount(
                $service->eligibleQuotationLines($quotation)
            );
        } catch (Throwable) {
            // Keep current value if quote cannot be re-read.
        }

        foreach ($this->trfGroups as $group) {
            $typeId = trim((string) ($group['sample_type_id'] ?? ''));
            if ($typeId !== '') {
                $this->ensureRowFieldBucketsForType($typeId);
            }
        }
    }

    public function physicalSampleCountForType(string $typeId): int
    {
        if ($typeId === EnquiryFromQuotationService::UNIFIED_TRF_FIELD_KEY || $typeId === '') {
            return max(1, $this->numberOfSamples);
        }

        $max = collect($this->quoteLines)
            ->filter(static fn (array $line): bool => (string) ($line['sample_type_id'] ?? '') === $typeId)
            ->max(static fn (array $line): int => max(1, (int) ($line['quantity'] ?? 1)));

        if ($max !== null) {
            return max(1, (int) $max);
        }

        return max(1, $this->numberOfSamples);
    }

    public function sampleRowCompletionPercent(string $typeId, int $rowIndex): int
    {
        $fieldNames = $this->rowFieldNamesForType($typeId);
        if ($fieldNames === []) {
            return 0;
        }

        $values = is_array($this->sectionRowFieldValuesByType[$typeId][$rowIndex] ?? null)
            ? $this->sectionRowFieldValuesByType[$typeId][$rowIndex]
            : [];

        $filled = 0;
        foreach ($fieldNames as $name) {
            if ($this->wizardFieldHasValue($values[$name] ?? null)) {
                $filled++;
            }
        }

        return (int) round(($filled / count($fieldNames)) * 100);
    }

    public function cloneFirstSampleRowToOthers(string $typeId): void
    {
        $typeId = trim($typeId);
        if ($typeId === '') {
            return;
        }

        $rowCount = $this->physicalSampleCountForType($typeId);
        if ($rowCount <= 1) {
            return;
        }

        $source = is_array($this->sectionRowFieldValuesByType[$typeId][0] ?? null)
            ? $this->sectionRowFieldValuesByType[$typeId][0]
            : [];

        if ($source === []) {
            return;
        }

        for ($rowIndex = 1; $rowIndex < $rowCount; $rowIndex++) {
            $this->sectionRowFieldValuesByType[$typeId][$rowIndex] = $this->deepCopyWizardRowValues($source);
        }

        $this->dispatch('ceq-sample-rows-cloned');
    }

    public function cloneFirstSampleRowToRow(string $typeId, int $targetRowIndex): void
    {
        $typeId = trim($typeId);
        if ($typeId === '' || $targetRowIndex < 1) {
            return;
        }

        $rowCount = $this->physicalSampleCountForType($typeId);
        if ($targetRowIndex >= $rowCount) {
            return;
        }

        $source = is_array($this->sectionRowFieldValuesByType[$typeId][0] ?? null)
            ? $this->sectionRowFieldValuesByType[$typeId][0]
            : [];

        if ($source === []) {
            return;
        }

        $this->sectionRowFieldValuesByType[$typeId][$targetRowIndex] = $this->deepCopyWizardRowValues($source);
        $this->dispatch('ceq-sample-rows-cloned');
    }

    public function finish()
    {
        $this->authorize('laboratory.components.quotation.add');
        $this->errorMessage = '';

        $rules = [
            'quotationId' => ['required', 'uuid'],
            'creationToken' => ['required', 'uuid'],
            'sourceChannel' => ['required', 'in:'.implode(',', EnquiryFromQuotationService::allowedSourceChannels())],
            'sendEmail' => ['boolean'],
            'sendPortal' => ['boolean'],
        ];
        $this->validate($rules);

        $inheritBillingDelivery = $this->quoteAlreadySentFromBilling && ! $this->notifyAgain;

        if (! $inheritBillingDelivery) {
            if (! $this->sendEmail && ! $this->sendPortal) {
                $this->errorMessage = 'Select at least one delivery channel (email or portal).';

                return null;
            }

            if ($this->sourceChannel === CommercialEnquirySyncService::SOURCE_WALK_IN && ! $this->sendEmail) {
                $this->errorMessage = 'Email delivery is required for walk-in requests.';

                return null;
            }
        }

        try {
            $this->syncOpenSampleRowEditorsBeforeSubmit();
            $quotation = QuotationHeader::query()->findOrFail($this->quotationId);
            $service = app(EnquiryFromQuotationService::class);
            // Quote line quantities are authoritative — never take a user override here.
            $this->numberOfSamples = $service->inferPhysicalSampleCount(
                $service->eligibleQuotationLines($quotation)
            );
            $trfCount = count($this->trfGroups);
            $enquiry = $service->createAndSend(
                $quotation,
                [
                    'number_of_samples' => $this->numberOfSamples,
                    'reference_number' => $this->referenceNumber !== '' ? $this->referenceNumber : null,
                    'sample_description' => $this->sampleDescription !== '' ? $this->sampleDescription : null,
                    'enquiry_notes' => $this->enquiryNotes !== '' ? $this->enquiryNotes : null,
                    'source_channel' => $this->sourceChannel,
                    'section_field_values_by_type' => $this->valuesForSelectedSectionsByType(),
                ],
                $this->creationToken,
                $this->sendPortal,
                $this->sendEmail,
                notifyAgain: $this->quoteAlreadySentFromBilling && $this->notifyAgain,
            );

            $reference = $enquiry->reference_number ?: $enquiry->formatted_number;
            $trfLabel = $trfCount > 1
                ? $trfCount.' Test Request Forms were generated'
                : 'A Test Request Form was generated';

            $deliveryNote = $inheritBillingDelivery
                ? ' Quotation was already sent from Billing; enquiry marked Quotation Sent without re-sending.'
                : ' and marked Quotation Sent.';

            $pdfNote = ' Open the request view Documents rail for the TRF and quotation PDFs.';

            session()->flash(
                'success',
                'Enquiry '.$reference.' was created from quotation '.$quotation->quote_number
                .$deliveryNote.' '.$trfLabel.'.'.$pdfNote
            );

            return $this->redirect($enquiry->staffViewUrl(), navigate: false);
        } catch (Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCurrentTrfGroupProperty(): ?array
    {
        return $this->trfGroups[$this->trfIndex] ?? null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getCurrentSelectedSectionsProperty(): array
    {
        $group = $this->currentTrfGroup;
        if ($group === null) {
            return [];
        }

        $typeId = (string) ($group['sample_type_id'] ?? '');
        $selected = array_flip($this->selectedSectionIdsByType[$typeId] ?? []);

        return array_values(array_filter(
            $group['sections'] ?? [],
            static fn (array $section): bool => isset($selected[(string) $section['id']]),
        ));
    }

    /**
     * @return list<array{key: string, label: string, hint: string, icon: string, active: bool, done: bool}>
     */
    public function getStepBadgesProperty(): array
    {
        $badges = [
            [
                'key' => 'review',
                'label' => 'Review',
                'hint' => 'Quote & origin',
                'icon' => 'mdi-file-document-outline',
                'active' => $this->phase === 'review',
                'done' => $this->phase !== 'review',
            ],
        ];

        foreach ($this->trfGroups as $index => $group) {
            $name = trim((string) ($group['sample_type_name'] ?? 'TRF'));
            $active = in_array($this->phase, ['sections', 'fill'], true) && $this->trfIndex === $index;
            $done = $this->phase === 'send' || $this->trfIndex > $index;

            $badges[] = [
                'key' => 'trf-'.$index,
                'label' => $name !== '' ? $name : 'TRF',
                'hint' => $this->phase === 'sections' && $active
                    ? 'Choose sections'
                    : ($this->phase === 'fill' && $active ? 'Fill details' : 'TRF details'),
                'icon' => 'mdi-flask-outline',
                'active' => $active,
                'done' => $done,
            ];
        }

        $badges[] = [
            'key' => 'send',
            'label' => 'Finish',
            'hint' => $this->quoteAlreadySentFromBilling && ! $this->notifyAgain
                ? 'Create enquiry'
                : 'Create & send',
            'icon' => 'mdi-send-check',
            'active' => $this->phase === 'send',
            'done' => false,
        ];

        return $badges;
    }

    public function render()
    {
        return view('livewire.billing.create-enquiry-from-quotation-wizard');
    }

    /**
     * Map enquiry-wizard field DTO → shape expected by test-request-field-render.
     *
     * @param  array<string, mixed>  $field
     * @return array{name: string, label: string, type: string, required: bool, readonly: bool, options: mixed}
     */
    public function mapWizardFieldToTrfField(array $field): array
    {
        $name = (string) ($field['name'] ?? '');
        $elementType = strtolower(trim((string) ($field['element_type'] ?? 'text')));

        // Client / contact / unit live in the excluded Customer details section.
        // Keep sampling location as CRM sample-point select (seeded from quotation header).
        $unsupportedCrmTypes = [
            'client_select',
            'client_contact_select',
            'client_unit_select',
        ];
        if (in_array($elementType, $unsupportedCrmTypes, true)
            || in_array($name, ['contact_person', 'company_unit_id'], true)
        ) {
            $elementType = 'text';
        }

        $type = match ($name) {
            'sampling_time' => 'time',
            'method_of_sampling', 'test_category', 'test_requirements' => 'checkbox',
            'sample_description' => 'rich_text',
            default => $elementType !== '' ? $elementType : 'text',
        };

        return [
            'name' => $name,
            'label' => (string) ($field['label'] ?? $name),
            'type' => $type,
            'required' => (bool) ($field['is_required'] ?? false),
            'readonly' => false,
            'options' => $field['options'] ?? [],
            'unit_options' => $field['unit_options'] ?? [],
            'render_paired' => (bool) ($field['render_paired'] ?? false),
        ];
    }

    /**
     * RFT-style grid rows for wizard sample cards (same column packing language as walk-in TRF).
     *
     * @param  list<array<string, mixed>>  $fields
     * @return list<array{type: string, cols?: int, columns?: list<array<string, mixed>|null>}>
     */
    public function wizardSampleCardGridRows(array $fields): array
    {
        $mapped = [];
        foreach ($fields as $field) {
            $name = (string) ($field['name'] ?? '');
            if ($name === '') {
                continue;
            }
            if (($field['render_paired'] ?? false) && $name === 'sample_quantity_unit') {
                continue;
            }
            $trf = $this->mapWizardFieldToTrfField($field);
            if ($name === 'sample_quantity') {
                $mapped[] = [
                    'type' => 'qty_unit',
                    'label' => 'Qty / Unit',
                    'field' => $trf,
                    'unit_options' => $field['unit_options'] ?? [],
                ];
                continue;
            }
            $mapped[] = [
                'type' => 'field',
                'label' => $trf['label'],
                'field' => $trf,
            ];
        }

        $rows = [];
        $buffer = [];
        $flush = static function () use (&$rows, &$buffer): void {
            if ($buffer === []) {
                return;
            }
            $cols = count($buffer);
            while (count($buffer) < 3) {
                $buffer[] = null;
            }
            $rows[] = [
                'type' => 'fields',
                'cols' => max(1, min(3, $cols)),
                'columns' => $buffer,
            ];
            $buffer = [];
        };

        foreach ($mapped as $column) {
            $fieldName = (string) ($column['field']['name'] ?? '');
            $fieldType = (string) ($column['field']['type'] ?? '');
            $isFull = $fieldName === 'sample_description'
                || in_array($fieldType, ['textarea', 'rich_text'], true);

            if ($isFull) {
                $flush();
                $rows[] = [
                    'type' => 'fields',
                    'cols' => 1,
                    'columns' => [$column],
                ];
                continue;
            }

            $buffer[] = $column;
            if (count($buffer) === 3) {
                $flush();
            }
        }
        $flush();

        return $rows;
    }

    private function currentSampleTypeId(): string
    {
        return (string) ($this->currentTrfGroup['sample_type_id'] ?? '');
    }

    private function resetWizardState(): void
    {
        $this->phase = 'review';
        $this->trfIndex = 0;
        $this->quotationId = null;
        $this->creationToken = '';
        $this->sourceChannel = CommercialEnquirySyncService::SOURCE_WALK_IN;
        $this->numberOfSamples = 1;
        $this->referenceNumber = '';
        $this->sampleDescription = '';
        $this->enquiryNotes = '';
        $this->selectedSectionIdsByType = [];
        $this->sectionFieldValuesByType = [];
        $this->sectionRowFieldValuesByType = [];
        $this->sendEmail = true;
        $this->sendPortal = false;
        $this->quoteAlreadySentFromBilling = false;
        $this->notifyAgain = false;
        $this->errorMessage = '';
        $this->quoteLines = [];
        $this->trfGroups = [];
        $this->quoteNumber = '';
        $this->customerName = '';
        $this->isMultiSampleType = false;
        $this->crmCustomerId = '';
        $this->formData = [];
        $this->resetValidation();
    }

    /**
     * Prefill CRM client / company unit / sampling location from the quotation header.
     */
    private function seedCrmFromQuotation(QuotationHeader $quotation): void
    {
        $this->crmCustomerId = trim((string) ($quotation->crm_customer_id ?? ''));
        $unitId = trim((string) ($quotation->crm_company_unit_id ?? ''));
        $this->formData = [
            'company_unit_id' => $unitId,
        ];

        $pointId = trim((string) ($quotation->sample_point_id ?? ''));
        $locationText = trim((string) ($quotation->sampling_location ?? ''));
        $seedLocation = $pointId !== '' ? $pointId : $locationText;
        if ($seedLocation === '') {
            return;
        }

        foreach ($this->sectionFieldValuesByType as $typeId => $fields) {
            if (! is_array($fields)) {
                continue;
            }
            if (array_key_exists('sampling_location', $fields)) {
                $this->sectionFieldValuesByType[$typeId]['sampling_location'] = $seedLocation;
            }
            if (array_key_exists('sampling_point', $fields)) {
                $this->sectionFieldValuesByType[$typeId]['sampling_point'] = $seedLocation;
            }
        }
    }

    public function getSelectedCrmCustomerIdProperty(): string
    {
        return $this->crmCustomerId;
    }

    public function getSelectedCompanyUnitNameProperty(): ?string
    {
        $unitId = trim((string) ($this->formData['company_unit_id'] ?? ''));
        if ($unitId === '') {
            return null;
        }

        return CRMCompanyUnit::query()->whereKey($unitId)->value('name');
    }

    public function getCustomerSamplePointsProperty(): Collection
    {
        $customerId = trim($this->crmCustomerId);
        if ($customerId === '') {
            return collect();
        }

        $unitId = trim((string) ($this->formData['company_unit_id'] ?? ''));
        if ($unitId === '') {
            return collect();
        }

        return SamplePoint::query()
            ->where('crm_customer_id', $customerId)
            ->where('crm_company_unit_id', $unitId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function getCustomerCompanyUnitsProperty(): Collection
    {
        $customerId = trim($this->crmCustomerId);
        if ($customerId === '') {
            return collect();
        }

        return CRMCompanyUnit::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function getCustomerContactsProperty(): Collection
    {
        $customerId = trim($this->crmCustomerId);
        if ($customerId === '') {
            return collect();
        }

        $contacts = CustomerContact::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $unitId = trim((string) ($this->formData['company_unit_id'] ?? ''));
        if ($unitId === '') {
            return $contacts;
        }

        return $contacts
            ->filter(fn (CustomerContact $contact): bool => $contact->isLinkedToCompanyUnit($unitId))
            ->values();
    }

    public function updatedNotifyAgain(bool $value): void
    {
        if (! $this->quoteAlreadySentFromBilling) {
            return;
        }

        if ($value) {
            $this->applyDeliveryDefaultsForChannel();

            return;
        }

        $this->sendEmail = false;
        $this->sendPortal = false;
    }

    private function seedSelectionsForGroups(): void
    {
        $selected = [];
        $values = [];
        $rowValues = [];

        foreach ($this->trfGroups as $group) {
            $typeId = (string) ($group['sample_type_id'] ?? '');
            if ($typeId === '') {
                continue;
            }

            $selected[$typeId] = collect($group['sections'] ?? [])
                ->filter(static fn (array $section): bool => (bool) ($section['default_selected'] ?? false))
                ->pluck('id')
                ->map(static fn ($id): string => (string) $id)
                ->values()
                ->all();

            $typeValues = [];
            $rowFieldDefaults = [];
            foreach ($group['sections'] ?? [] as $section) {
                $isRowsSection = ($section['section_type'] ?? '') === 'rows_section';
                foreach ($section['fields'] ?? [] as $field) {
                    $name = (string) ($field['name'] ?? '');
                    if ($name === '') {
                        continue;
                    }

                    if ($isRowsSection) {
                        if (! array_key_exists($name, $rowFieldDefaults)) {
                            $rowFieldDefaults[$name] = $this->defaultValueForWizardField($field);
                        }

                        continue;
                    }

                    if (array_key_exists($name, $typeValues)) {
                        continue;
                    }

                    $typeValues[$name] = $this->defaultValueForWizardField($field);
                }
            }

            if ($this->sampleDescription !== '' && array_key_exists('sample_description', $rowFieldDefaults)) {
                $rowFieldDefaults['sample_description'] = $this->sampleDescription;
            }

            $values[$typeId] = $typeValues;
            $rowValues[$typeId] = $rowFieldDefaults;
        }

        $this->selectedSectionIdsByType = $selected;
        $this->sectionFieldValuesByType = $values;
        $this->sectionRowFieldValuesByType = [];

        foreach ($this->trfGroups as $group) {
            $typeId = trim((string) ($group['sample_type_id'] ?? ''));
            if ($typeId === '') {
                continue;
            }

            $defaults = $rowValues[$typeId] ?? [];
            $this->sectionRowFieldValuesByType[$typeId] = [];
            if ($defaults !== []) {
                $this->sectionRowFieldValuesByType[$typeId]['__defaults'] = $defaults;
            }
            $this->ensureRowFieldBucketsForType($typeId);
        }
    }

    private function applyDeliveryDefaultsForChannel(): void
    {
        if ($this->sourceChannel === CommercialEnquirySyncService::SOURCE_PORTAL) {
            $this->sendPortal = true;
            $this->sendEmail = false;

            return;
        }

        $this->sendEmail = true;
        $this->sendPortal = false;
    }

    private function validateCurrentTypeRequiredFields(): void
    {
        $typeId = $this->currentSampleTypeId();
        $rules = [];
        $messages = [];
        $rowCount = $this->physicalSampleCountForType($typeId);

        foreach ($this->currentSelectedSections as $section) {
            $isRowsSection = ($section['section_type'] ?? '') === 'rows_section';

            if ($isRowsSection) {
                for ($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++) {
                    foreach ($section['fields'] ?? [] as $field) {
                        if (! ($field['is_required'] ?? false)) {
                            continue;
                        }
                        $name = (string) ($field['name'] ?? '');
                        if ($name === '' || $name === 'sample_quantity_unit') {
                            continue;
                        }
                        $key = 'sectionRowFieldValuesByType.'.$typeId.'.'.$rowIndex.'.'.$name;
                        $rules[$key] = ['required'];
                        $messages[$key.'.required'] = 'Sample '.($rowIndex + 1).': '
                            .($field['label'] ?? $name).' is required.';
                    }
                }

                continue;
            }

            foreach ($section['fields'] ?? [] as $field) {
                if (! ($field['is_required'] ?? false)) {
                    continue;
                }
                $name = (string) ($field['name'] ?? '');
                if ($name === '') {
                    continue;
                }
                $key = 'sectionFieldValuesByType.'.$typeId.'.'.$name;
                $rules[$key] = ['required'];
                $messages[$key.'.required'] = ($field['label'] ?? $name).' is required.';
            }
        }

        if ($rules !== []) {
            $this->validate($rules, $messages);
        }
    }

    /**
     * @return array<string, array<int|string, mixed>>
     */
    private function valuesForSelectedSectionsByType(): array
    {
        $payload = [];

        foreach ($this->trfGroups as $group) {
            $typeId = (string) ($group['sample_type_id'] ?? '');
            if ($typeId === '') {
                continue;
            }

            $selected = array_flip($this->selectedSectionIdsByType[$typeId] ?? []);
            $allowedFlat = [];
            $allowedRow = [];
            foreach ($group['sections'] ?? [] as $section) {
                if (! isset($selected[(string) ($section['id'] ?? '')])) {
                    continue;
                }

                $isRowsSection = ($section['section_type'] ?? '') === 'rows_section';

                foreach ($section['fields'] ?? [] as $field) {
                    $name = (string) ($field['name'] ?? '');
                    if ($name === '') {
                        continue;
                    }

                    if ($isRowsSection) {
                        $allowedRow[$name] = true;
                    } else {
                        $allowedFlat[$name] = true;
                    }
                }
            }

            $typePayload = [];
            foreach ($this->sectionFieldValuesByType[$typeId] ?? [] as $name => $value) {
                if (! isset($allowedFlat[$name])) {
                    continue;
                }
                $normalized = $this->normalizeWizardFieldValueForExport($value);
                if ($normalized !== null) {
                    $typePayload[$name] = $normalized;
                }
            }

            $rowCount = $this->physicalSampleCountForType($typeId);
            $rowsPayload = [];
            for ($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++) {
                $rowValues = $this->sectionRowFieldValuesByType[$typeId][$rowIndex] ?? [];
                $rowPayload = [];
                foreach ($rowValues as $name => $value) {
                    if ($name === '__defaults' || ! isset($allowedRow[$name])) {
                        continue;
                    }
                    $normalized = $this->normalizeWizardFieldValueForExport($value);
                    if ($normalized !== null) {
                        $rowPayload[$name] = $normalized;
                    }
                }

                if ($this->sampleDescription !== '' && ! isset($rowPayload['sample_description']) && isset($allowedRow['sample_description'])) {
                    $rowPayload['sample_description'] = $this->sampleDescription;
                }

                if ($rowPayload !== []) {
                    $rowsPayload[$rowIndex] = $rowPayload;
                }
            }

            if ($rowsPayload !== []) {
                $payload[$typeId] = $rowsPayload;
            } elseif ($typePayload !== []) {
                $payload[$typeId] = $typePayload;
            }
        }

        return $payload;
    }

    private function ensureRowFieldBucketsForType(string $typeId): void
    {
        $typeId = trim($typeId);
        if ($typeId === '') {
            return;
        }

        $defaults = is_array($this->sectionRowFieldValuesByType[$typeId]['__defaults'] ?? null)
            ? $this->sectionRowFieldValuesByType[$typeId]['__defaults']
            : $this->rowFieldDefaultsForType($typeId);

        $rowCount = $this->physicalSampleCountForType($typeId);
        $existing = $this->sectionRowFieldValuesByType[$typeId] ?? [];
        $rows = [];

        for ($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++) {
            $previous = is_array($existing[$rowIndex] ?? null) ? $existing[$rowIndex] : [];
            $rows[$rowIndex] = array_merge($defaults, $previous);
        }

        if ($defaults !== []) {
            $rows['__defaults'] = $defaults;
        }

        $this->sectionRowFieldValuesByType[$typeId] = $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function rowFieldDefaultsForType(string $typeId): array
    {
        foreach ($this->trfGroups as $group) {
            if ((string) ($group['sample_type_id'] ?? '') !== $typeId) {
                continue;
            }

            $defaults = [];
            foreach ($group['sections'] ?? [] as $section) {
                if (($section['section_type'] ?? '') !== 'rows_section') {
                    continue;
                }

                foreach ($section['fields'] ?? [] as $field) {
                    $name = (string) ($field['name'] ?? '');
                    if ($name === '' || array_key_exists($name, $defaults)) {
                        continue;
                    }
                    $defaults[$name] = $this->defaultValueForWizardField($field);
                }
            }

            if ($this->sampleDescription !== '' && array_key_exists('sample_description', $defaults)) {
                $defaults['sample_description'] = $this->sampleDescription;
            }

            return $defaults;
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function defaultValueForWizardField(array $field): mixed
    {
        $elementType = (string) ($field['element_type'] ?? 'text');
        $hasOptions = is_array($field['options'] ?? null) && ($field['options'] ?? []) !== [];

        return match (true) {
            $elementType === 'checkbox' && $hasOptions => [],
            $elementType === 'checkbox' => false,
            default => '',
        };
    }

    private function normalizeWizardFieldValueForExport(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            $selected = [];
            $isAssocMap = $value !== [] && array_keys($value) !== range(0, count($value) - 1);
            if ($isAssocMap) {
                foreach ($value as $option => $checked) {
                    if ($checked === true || $checked === 1 || $checked === '1' || $checked === 'true') {
                        $token = trim((string) $option);
                        if ($token !== '') {
                            $selected[] = $token;
                        }
                    }
                }
            } else {
                foreach ($value as $item) {
                    $token = trim((string) $item);
                    if ($token !== '') {
                        $selected[] = $token;
                    }
                }
            }

            return $selected === [] ? null : implode(',', $selected);
        }

        $trimmed = is_string($value) ? trim($value) : $value;

        return ($trimmed === '' || $trimmed === null) ? null : $trimmed;
    }

    private function rowFieldNamesForType(string $typeId): array
    {
        foreach ($this->trfGroups as $group) {
            if ((string) ($group['sample_type_id'] ?? '') !== $typeId) {
                continue;
            }

            $names = [];
            foreach ($group['sections'] ?? [] as $section) {
                if (($section['section_type'] ?? '') !== 'rows_section') {
                    continue;
                }

                foreach ($section['fields'] ?? [] as $field) {
                    $name = (string) ($field['name'] ?? '');
                    if ($name === '' || $name === 'sample_quantity_unit') {
                        continue;
                    }
                    $names[] = $name;
                }
            }

            return array_values(array_unique($names));
        }

        return [];
    }

    private function syncOpenSampleRowEditorsBeforeSubmit(): void
    {
        $this->dispatch('ceq-sync-tinymce');
    }

    private function wizardFieldHasValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_array($value)) {
            if ($value === []) {
                return false;
            }

            $isAssocMap = array_keys($value) !== range(0, count($value) - 1);
            if ($isAssocMap) {
                foreach ($value as $checked) {
                    if ($checked === true || $checked === 1 || $checked === '1' || $checked === 'true') {
                        return true;
                    }
                }

                return false;
            }

            return true;
        }

        if (is_string($value)) {
            return trim(strip_tags($value)) !== '';
        }

        return $value !== null && $value !== '';
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    private function deepCopyWizardRowValues(array $source): array
    {
        $encoded = json_encode($source);

        return is_string($encoded) ? (json_decode($encoded, true) ?? []) : [];
    }
}
