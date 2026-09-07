<?php

namespace App\Services\SubmissionForm;

use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\SamplePoint;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\Services\Commercial\CommercialEnquirySyncService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class SubmissionFormInstanceTrfEditService
{
    public function __construct(
        private readonly SubmissionFormInstanceSampleRowUpdateService $rowUpdateService,
        private readonly CommercialEnquirySyncService $enquirySyncService,
    ) {}

    /**
     * @return array{
     *     client_name: string,
     *     company_unit_id: string,
     *     contact_id: string,
     *     contact_name: string,
     *     contact_email: string,
     *     contact_phone: string,
     *     customer_id: ?string
     * }
     */
    public function customerDraft(SubmissionFormInstance $instance): array
    {
        $instance->loadMissing(['values.element', 'crmCustomer', 'sampleSubmissionRequest.contact', 'sampleSubmissionRequest.customer']);

        $map = $this->scalarValuesByName($instance);
        $enquiry = $instance->sampleSubmissionRequest;
        $contact = $enquiry?->contact;

        $rawContactPerson = $this->nonEmpty($map['contact_person'] ?? null)
            ?? $this->nonEmpty($map['crm_contact_id'] ?? null);
        $contactId = '';
        if ($rawContactPerson !== null && $this->looksLikeUuid($rawContactPerson)) {
            $contactId = $rawContactPerson;
            if ($contact === null || (string) $contact->id !== $contactId) {
                $contact = CustomerContact::query()->find($contactId) ?? $contact;
            }
        } elseif ($contact !== null) {
            $contactId = (string) $contact->id;
        }

        $contactName = $this->contactDisplayName($contact);
        if ($contactName === null && $rawContactPerson !== null && ! $this->looksLikeUuid($rawContactPerson)) {
            $contactName = $rawContactPerson;
        }

        return [
            'client_name' => $this->nonEmpty($map['customer_name'] ?? $map['client_name'] ?? null)
                ?? $this->nonEmpty($enquiry?->customer?->name ?? $instance->crmCustomer?->name)
                ?? '',
            'company_unit_id' => $this->nonEmpty($map['company_unit_id'] ?? null) ?? '',
            'contact_id' => $contactId,
            'contact_name' => $contactName ?? '',
            'contact_email' => $this->nonEmpty($map['customer_email'] ?? $map['email'] ?? $map['contact_email'] ?? null)
                ?? $this->nonEmpty($contact?->email)
                ?? '',
            'contact_phone' => $this->nonEmpty($map['mobile_number'] ?? $map['contact_phone'] ?? $map['mobile'] ?? null)
                ?? $this->nonEmpty($contact?->mobile ?? $contact?->telephone)
                ?? '',
            'customer_id' => $this->nonEmpty(
                (string) ($instance->crm_customer_id ?? $enquiry?->crm_customer_id ?? '')
            ),
        ];
    }

    /**
     * @return list<array{id: string, text: string, email: string, phone: string}>
     */
    public function contactOptions(?string $customerId, ?string $unitId = null): array
    {
        if ($customerId === null || $customerId === '') {
            return [];
        }

        $unitId = trim((string) ($unitId ?? ''));

        return CustomerContact::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('first_name')
            ->get()
            ->when($unitId !== '', fn ($contacts) => $contacts->filter(
                fn (CustomerContact $contact): bool => $contact->isLinkedToCompanyUnit($unitId)
            ))
            ->map(function (CustomerContact $contact): array {
                $name = $this->contactDisplayName($contact)
                    ?? $this->nonEmpty($contact->email)
                    ?? 'Contact';

                return [
                    'id' => (string) $contact->id,
                    'text' => $name,
                    'email' => $this->nonEmpty($contact->email) ?? '',
                    'phone' => $this->nonEmpty($contact->mobile ?? $contact->telephone) ?? '',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{id: string, text: string, email: string, phone: string}|null
     */
    public function contactOptionById(?string $contactId): ?array
    {
        $contactId = trim((string) ($contactId ?? ''));
        if ($contactId === '' || ! $this->looksLikeUuid($contactId)) {
            return null;
        }

        $contact = CustomerContact::query()->find($contactId);
        if ($contact === null) {
            return null;
        }

        return [
            'id' => (string) $contact->id,
            'text' => $this->contactDisplayName($contact)
                ?? $this->nonEmpty($contact->email)
                ?? 'Contact',
            'email' => $this->nonEmpty($contact->email) ?? '',
            'phone' => $this->nonEmpty($contact->mobile ?? $contact->telephone) ?? '',
        ];
    }

    /**
     * @return list<array{id: string, text: string}>
     */
    public function unitOptions(?string $customerId): array
    {
        if ($customerId === null || $customerId === '') {
            return [];
        }

        return CRMCompanyUnit::query()
            ->where('crm_customer_id', $customerId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (CRMCompanyUnit $unit): array => [
                'id' => (string) $unit->id,
                'text' => (string) $unit->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function samplePointOptions(?string $unitId): array
    {
        if ($unitId === null || $unitId === '') {
            return [];
        }

        return SamplePoint::query()
            ->where('crm_company_unit_id', $unitId)
            ->with('area')
            ->orderBy('name')
            ->get()
            ->map(function (SamplePoint $point): array {
                $label = $point->area && $point->area->name
                    ? $point->area->name.' - '.$point->name
                    : (string) $point->name;

                return [
                    'value' => (string) $point->id,
                    'label' => $label,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     label: string,
     *     element_type: string,
     *     required: bool,
     *     options: array<int|string, mixed>
     * }>
     */
    public function collectionFieldDefinitions(SubmissionForm $form): array
    {
        $form->loadMissing(['sections.elementHolders.elements']);

        $definitions = [];

        foreach ($form->sections as $section) {
            if (mb_strtolower(trim((string) ($section->title ?? ''))) !== 'sample collection data') {
                continue;
            }

            foreach ($section->elementHolders as $holder) {
                foreach ($holder->elements as $element) {
                    $name = (string) ($element->name ?? '');
                    if ($name === '' || (bool) ($element->is_hidden ?? false)) {
                        continue;
                    }
                    if (in_array($name, SubmissionFormSchemaHelper::miscellaneousTrfFieldNames(), true)) {
                        continue;
                    }
                    if (SubmissionFormSchemaHelper::shouldHideFromTrfDisplay($element)) {
                        continue;
                    }

                    $definitions[] = [
                        'id' => (string) $element->id,
                        'name' => $name,
                        'label' => (string) ($element->label ?? $name),
                        'element_type' => $name === 'sampling_time'
                            ? 'time'
                            : (string) $element->element_type,
                        'required' => (bool) $element->is_required,
                        'options' => $element->getFormattedOptions(),
                    ];
                }
            }
        }

        return $definitions;
    }

    /**
     * @param  list<array{name: string, element_type: string, options?: array<int|string, mixed>}>  $definitions
     * @return array<string, mixed>
     */
    public function collectionDraft(SubmissionFormInstance $instance, array $definitions): array
    {
        $map = $this->scalarValuesByName($instance);
        $draft = [];

        foreach ($definitions as $definition) {
            $name = (string) ($definition['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $type = (string) ($definition['element_type'] ?? 'text');
            $raw = $map[$name] ?? '';

            if ($type === 'checkbox' || in_array($name, $this->collectionCheckboxFieldNames(), true)) {
                $draft[$name] = SubmissionFormSchemaHelper::checkboxGroupValueMap($raw);

                continue;
            }

            if ($name === 'extra_sampling_equipment') {
                $decoded = json_decode($raw, true);
                $draft[$name] = is_array($decoded) ? $decoded : [];

                continue;
            }

            if ($name === 'thermometer_id' && $this->formUsesSamplingEquipmentPicker($instance)) {
                $draft[$name] = app(\App\Services\Sampleworkflow\TrfSamplingEquipmentResolver::class)
                    ->rowsForForm($raw);

                continue;
            }

            $draft[$name] = $raw;
        }

        return $draft;
    }

    /**
     * @param  array{
     *     company_unit_id?: string,
     *     contact_name?: string,
     *     contact_email?: string,
     *     contact_phone?: string
     * }  $customer
     * @param  array<string, mixed>  $collection
     * @param  array<int, array<string, mixed>>  $sampleRows
     */
    public function saveAll(
        SubmissionFormInstance $instance,
        array $customer,
        array $collection,
        array $sampleRows,
    ): SubmissionFormInstance {
        return DB::transaction(function () use ($instance, $customer, $collection, $sampleRows): SubmissionFormInstance {
            $instance->loadMissing(['submissionForm']);

            $scalarPayload = [
                'company_unit_id' => $customer['company_unit_id'] ?? null,
                'contact_person' => $customer['contact_id'] ?? $customer['contact_name'] ?? null,
                'crm_contact_id' => $customer['contact_id'] ?? null,
                'customer_email' => $customer['contact_email'] ?? null,
                'email' => $customer['contact_email'] ?? null,
                'mobile_number' => $customer['contact_phone'] ?? null,
                'contact_phone' => $customer['contact_phone'] ?? null,
            ];

            foreach ($collection as $name => $value) {
                $scalarPayload[(string) $name] = $value;
            }

            $this->upsertScalars($instance, $scalarPayload);

            foreach ($sampleRows as $rowIndex => $fields) {
                if (! is_array($fields)) {
                    continue;
                }

                $instance = $this->rowUpdateService->updateRow($instance, (int) $rowIndex, $fields);
            }

            $fresh = $instance->fresh([
                'values.element',
                'submissionForm',
                'batches',
                'analysisAcceptanceForms',
                'sampleSubmissionRequest.contact',
                'sampleSubmissionRequest.customer',
                'crmCustomer',
            ]) ?? $instance;

            $this->enquirySyncService->resyncSampleDataFromInstance($fresh);

            return $fresh;
        });
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function upsertScalars(SubmissionFormInstance $instance, array $fields): void
    {
        $form = $instance->submissionForm;
        if ($form === null) {
            return;
        }

        $form->loadMissing(['sections.elementHolders.elements']);

        /** @var Collection<string, SubmissionFormElement> $byName */
        $byName = collect();
        foreach ($form->sections as $section) {
            foreach ($section->elementHolders as $holder) {
                foreach ($holder->elements as $element) {
                    $name = (string) ($element->name ?? '');
                    if ($name === '') {
                        continue;
                    }
                    $byName->put($name, $element);
                }
            }
        }

        foreach ($fields as $name => $value) {
            $name = trim((string) $name);
            if ($name === '' || ! $byName->has($name)) {
                continue;
            }

            /** @var SubmissionFormElement $element */
            $element = $byName->get($name);
            $stored = $this->encodeValueForStorage($value, (string) $element->element_type, (string) $element->name);

            SubmissionFormInstanceValue::withoutAuditing(function () use ($instance, $element, $stored): void {
                SubmissionFormInstanceValue::query()->updateOrCreate(
                    [
                        'submission_form_instance_id' => $instance->id,
                        'submission_form_element_id' => $element->id,
                        'array_index' => null,
                    ],
                    [
                        'value' => $stored,
                    ],
                );
            });
        }
    }

    /**
     * @return array<string, string>
     */
    private function scalarValuesByName(SubmissionFormInstance $instance): array
    {
        $instance->loadMissing(['values.element']);
        $map = [];

        foreach ($instance->values as $value) {
            if ($value->array_index !== null) {
                continue;
            }

            $name = trim((string) ($value->element?->name ?? ''));
            if ($name === '') {
                continue;
            }

            $map[$name] = (string) ($value->value ?? '');
        }

        return $map;
    }

    private function encodeValueForStorage(mixed $value, string $elementType, string $fieldName = ''): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($fieldName === 'extra_sampling_equipment' && is_array($value)) {
            $rows = array_values(array_filter($value, static function ($row): bool {
                if (! is_array($row)) {
                    return false;
                }

                return trim((string) ($row['label'] ?? '')) !== ''
                    || trim((string) ($row['id'] ?? '')) !== '';
            }));

            return $rows === [] ? null : json_encode($rows);
        }

        if ($fieldName === 'thermometer_id' && is_array($value)) {
            return app(\App\Services\Sampleworkflow\TrfSamplingEquipmentResolver::class)->encodeIds($value);
        }

        if (is_array($value)) {
            if ($value !== [] && array_keys($value) !== range(0, count($value) - 1)) {
                $selected = [];
                foreach ($value as $key => $flag) {
                    if ((bool) $flag) {
                        $selected[] = (string) $key;
                    }
                }

                return $selected === [] ? null : implode(',', $selected);
            }

            $flat = array_values(array_filter(array_map(
                static fn ($item): string => trim((string) $item),
                $value,
            ), static fn (string $token): bool => $token !== ''));

            return $flat === [] ? null : implode(',', $flat);
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function contactDisplayName(?CustomerContact $contact): ?string
    {
        if ($contact === null) {
            return null;
        }

        return $this->nonEmpty(trim(
            (string) ($contact->first_name ?? '').' '
            .(string) ($contact->middle_name ?? '').' '
            .(string) ($contact->last_name ?? '')
        ));
    }

    private function nonEmpty(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function formUsesSamplingEquipmentPicker(SubmissionFormInstance $instance): bool
    {
        $documentCode = strtoupper(trim((string) ($instance->submissionForm?->document_code ?? '')));

        if ($documentCode === strtoupper(TrfDocumentCodeForSampleType::WASTE_WATER)
            || $documentCode === strtoupper(TrfDocumentCodeForSampleType::WASTE_WATER_LEGACY)
            || str_contains($documentCode, 'WASTEWATER')
            || (str_contains($documentCode, 'WASTE') && str_contains($documentCode, '036'))) {
            return false;
        }

        if (in_array($documentCode, [
            strtoupper(TrfDocumentCodeForSampleType::FOOD),
            strtoupper(TrfDocumentCodeForSampleType::FOOD_AND_FEED),
            strtoupper(TrfDocumentCodeForSampleType::WATER),
        ], true)) {
            return true;
        }

        return $documentCode !== ''
            && str_contains($documentCode, 'WATER')
            && ! str_contains($documentCode, 'WASTE');
    }

    /**
     * @return list<string>
     */
    private function collectionCheckboxFieldNames(): array
    {
        return [
            'sampling_apparatus',
            'method_of_sampling',
            'reason_of_collection',
            'transport_condition',
            'sampling_technique',
            'sampling_source',
            'sample_types_ww',
            'field_data_requirements',
        ];
    }

    private function looksLikeUuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value,
        );
    }
}
