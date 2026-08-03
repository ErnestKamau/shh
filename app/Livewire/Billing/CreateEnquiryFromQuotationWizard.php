<?php

namespace App\Livewire\Billing;

use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\Services\Commercial\CommercialEnquirySyncService;
use App\Services\Commercial\EnquiryFromQuotationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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

    /** @var array<string, array<string, mixed>> sample_type_id => field values */
    public array $sectionFieldValuesByType = [];

    public bool $sendEmail = true;

    public bool $sendPortal = false;

    public string $errorMessage = '';

    /** @var list<array<string, mixed>> */
    public array $quoteLines = [];

    /** @var list<array<string, mixed>> */
    public array $trfGroups = [];

    public string $quoteNumber = '';

    public string $customerName = '';

    public bool $isMultiSampleType = false;

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
            $this->seedSelectionsForGroups();
            $this->applyDeliveryDefaultsForChannel();
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
            $this->phase = 'fill';

            return;
        }

        if ($this->phase === 'fill') {
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
        if (! $this->isMultiSampleType) {
            $rules['numberOfSamples'] = ['required', 'integer', 'min:1', 'max:10000'];
        }
        $this->validate($rules);

        if (! $this->sendEmail && ! $this->sendPortal) {
            $this->errorMessage = 'Select at least one delivery channel (email or portal).';

            return null;
        }

        if ($this->sourceChannel === CommercialEnquirySyncService::SOURCE_WALK_IN && ! $this->sendEmail) {
            $this->errorMessage = 'Email delivery is required for walk-in requests.';

            return null;
        }

        try {
            $quotation = QuotationHeader::query()->findOrFail($this->quotationId);
            $trfCount = count($this->trfGroups);
            $enquiry = app(EnquiryFromQuotationService::class)->createAndSend(
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
            );

            $reference = $enquiry->reference_number ?: $enquiry->formatted_number;
            $trfLabel = $trfCount > 1
                ? $trfCount.' Test Request Forms were generated'
                : 'A Test Request Form was generated';

            session()->flash(
                'success',
                'Enquiry '.$reference.' was created from quotation '.$quotation->quote_number
                .' and marked Quotation Sent. '.$trfLabel.'.'
            );

            return $this->redirect(url()->previous() ?: route('quotation-index'), navigate: false);
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
     * @return list<array{key: string, label: string, active: bool}>
     */
    public function getStepBadgesProperty(): array
    {
        $badges = [
            ['key' => 'review', 'label' => '1. Quote review', 'active' => $this->phase === 'review'],
        ];
        $n = 2;
        foreach ($this->trfGroups as $index => $group) {
            $name = (string) ($group['sample_type_name'] ?? 'TRF');
            $badges[] = [
                'key' => 'trf-'.$index,
                'label' => $n.'. '.$name,
                'active' => in_array($this->phase, ['sections', 'fill'], true) && $this->trfIndex === $index,
            ];
            $n++;
        }
        $badges[] = [
            'key' => 'send',
            'label' => $n.'. Send quote',
            'active' => $this->phase === 'send',
        ];

        return $badges;
    }

    public function render()
    {
        return view('livewire.billing.create-enquiry-from-quotation-wizard');
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
        $this->sendEmail = true;
        $this->sendPortal = false;
        $this->errorMessage = '';
        $this->quoteLines = [];
        $this->trfGroups = [];
        $this->quoteNumber = '';
        $this->customerName = '';
        $this->isMultiSampleType = false;
        $this->resetValidation();
    }

    private function seedSelectionsForGroups(): void
    {
        $selected = [];
        $values = [];

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
            foreach ($group['sections'] ?? [] as $section) {
                foreach ($section['fields'] ?? [] as $field) {
                    $name = (string) ($field['name'] ?? '');
                    if ($name === '' || array_key_exists($name, $typeValues)) {
                        continue;
                    }
                    $elementType = (string) ($field['element_type'] ?? 'text');
                    $hasOptions = is_array($field['options'] ?? null) && ($field['options'] ?? []) !== [];
                    $typeValues[$name] = match (true) {
                        $elementType === 'checkbox' && $hasOptions => [],
                        $elementType === 'checkbox' => false,
                        default => '',
                    };
                }
            }
            if ($this->sampleDescription !== '' && array_key_exists('sample_description', $typeValues)) {
                $typeValues['sample_description'] = $this->sampleDescription;
            }
            $values[$typeId] = $typeValues;
        }

        $this->selectedSectionIdsByType = $selected;
        $this->sectionFieldValuesByType = $values;
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

        foreach ($this->currentSelectedSections as $section) {
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
     * @return array<string, array<string, mixed>>
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
            $allowed = [];
            foreach ($group['sections'] ?? [] as $section) {
                if (! isset($selected[(string) ($section['id'] ?? '')])) {
                    continue;
                }
                foreach ($section['fields'] ?? [] as $field) {
                    $name = (string) ($field['name'] ?? '');
                    if ($name !== '') {
                        $allowed[$name] = true;
                    }
                }
            }

            $typePayload = [];
            foreach ($this->sectionFieldValuesByType[$typeId] ?? [] as $name => $value) {
                if (! isset($allowed[$name])) {
                    continue;
                }
                if (is_bool($value)) {
                    $typePayload[$name] = $value ? '1' : '0';

                    continue;
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
                    if ($selected === []) {
                        continue;
                    }
                    $typePayload[$name] = implode(',', $selected);

                    continue;
                }
                $trimmed = is_string($value) ? trim($value) : $value;
                if ($trimmed === '' || $trimmed === null) {
                    continue;
                }
                $typePayload[$name] = $trimmed;
            }

            if ($this->sampleDescription !== '' && ! isset($typePayload['sample_description'])) {
                $typePayload['sample_description'] = $this->sampleDescription;
            }

            $payload[$typeId] = $typePayload;
        }

        return $payload;
    }
}
