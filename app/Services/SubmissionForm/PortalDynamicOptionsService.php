<?php

namespace App\Services\SubmissionForm;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\InventoryStore;
use App\InventoryStoreSlot;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\SamplePoint;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\SampleCondition;
use App\SampleType;
use App\Standards;
use App\User;
use App\Models\System\SystemConfigurationsType;
use App\Zone;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PortalDynamicOptionsService
{
    /** @var list<string> */
    private const VALID_TYPES = [
        'client_select',
        'sample_type_select',
        'client_unit_select',
        'client_contact_select',
        'client_submission_officers_select',
        'analysis_type_select',
        'analysis_elements_select',
        'store_select',
        'store_slot_select',
        'sample_condition_select',
        'standard_select',
        'sample_point_select',
        'user_select',
        'zone_select',
        'system_config_select',
    ];

    private const MAX_PER_PAGE = 100;

    /**
     * @return array{options: list<array{value: mixed, label: string}>, pagination?: array<string, mixed>}
     */
    public function resolve(Request $request, ?string $crmCustomerId): array
    {
        $elementType = (string) $request->string('element_type');

        if ($elementType === '' || ! in_array($elementType, self::VALID_TYPES, true)) {
            throw ValidationException::withMessages([
                'element_type' => ['The element type field is invalid or unsupported for the portal.'],
            ]);
        }

        $clientId = $this->resolveClientId($request, $crmCustomerId);
        $sampleTypeId = $request->input('sample_type_id');
        $storeId = $request->input('store_id');
        $clientUnitId = $request->input('client_unit_id');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(self::MAX_PER_PAGE, max(1, (int) $request->input('per_page', 100)));
        $search = (string) $request->input('search', '');

        return match ($elementType) {
            'client_select' => $this->clientSelect($crmCustomerId, $search, $page, $perPage),
            'sample_type_select' => $this->sampleTypeSelect($request),
            'client_unit_select' => ['options' => $this->normalizeOptions($this->clientUnits($clientId))],
            'client_contact_select' => ['options' => $this->normalizeOptions($this->clientContacts($clientId))],
            'client_submission_officers_select' => ['options' => $this->submissionOfficers($clientId)],
            'store_select' => ['options' => $this->stores()],
            'store_slot_select' => ['options' => $this->storeSlots($storeId)],
            'sample_condition_select' => ['options' => $this->sampleConditions()],
            'analysis_type_select' => ['options' => $this->analysisTypes($sampleTypeId)],
            'analysis_elements_select' => ['options' => $this->analysisElements($request->input('analysis_type_id'))],
            'standard_select' => ['options' => $this->standards()],
            'sample_point_select' => ['options' => $this->samplePoints($clientUnitId)],
            'user_select' => $this->userSelect($search, $page, $perPage),
            'zone_select' => ['options' => $this->zones()],
            'system_config_select' => ['options' => $this->systemConfigOptions((string) $request->input('source_field', ''))],
            default => ['options' => []],
        };
    }

    private function resolveClientId(Request $request, ?string $crmCustomerId): ?string
    {
        $requested = $request->input('client_id');

        if ($crmCustomerId !== null) {
            if ($requested !== null && (string) $requested !== '' && (string) $requested !== $crmCustomerId) {
                throw ValidationException::withMessages([
                    'client_id' => ['client_id must match the authenticated customer.'],
                ]);
            }

            return $crmCustomerId;
        }

        return $requested !== null && (string) $requested !== '' ? (string) $requested : null;
    }

    /**
     * @return array{options: list<array{value: mixed, label: string}>, pagination: array<string, mixed>}
     */
    private function clientSelect(?string $crmCustomerId, string $search, int $page, int $perPage): array
    {
        if ($crmCustomerId !== null) {
            $customer = CRMCustomer::query()
                ->where('id', $crmCustomerId)
                ->where('active', 1)
                ->first(['id', 'name']);

            $options = $customer
                ? [['value' => $customer->id, 'label' => $customer->name]]
                : [];

            return [
                'options' => $options,
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'total' => count($options),
                    'has_more' => false,
                ],
            ];
        }

        $query = CRMCustomer::query()->where('active', 1);

        if ($search !== '') {
            $query->where('name', 'LIKE', '%'.$search.'%');
        }

        $paginator = $query->orderBy('name')->paginate($perPage, ['id', 'name'], 'page', $page);

        $options = collect($paginator->items())
            ->map(fn (CRMCustomer $client): array => [
                'value' => $client->id,
                'label' => $client->name,
            ])
            ->all();

        return [
            'options' => $options,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ];
    }

    /**
     * @return array{options: list<array{value: mixed, label: string}>}
     */
    private function sampleTypeSelect(Request $request): array
    {
        $query = SampleType::query()->orderBy('name');

        if ($request->filled('submission_form_id')) {
            $form = SubmissionForm::query()
                ->with('sampleAnalysisStages')
                ->find($request->input('submission_form_id'));

            if ($form && $form->sampleAnalysisStages->isNotEmpty()) {
                $stageIds = $form->sampleAnalysisStages->pluck('id')->all();
                $query->whereHas('sampleAnalysisStages', function ($q) use ($stageIds): void {
                    $q->whereIn('sample_analysis_stages.id', $stageIds);
                });
            }
        }

        $options = $query->get(['id', 'name'])
            ->map(fn (SampleType $type): array => [
                'value' => $type->id,
                'label' => $type->name,
            ])
            ->all();

        return ['options' => $options];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function clientUnits(?string $clientId): array
    {
        if ($clientId === null) {
            return [];
        }

        return CRMCompanyUnit::query()
            ->where('crm_customer_id', $clientId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($unit): array => ['id' => $unit->id, 'text' => $unit->name])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function clientContacts(?string $clientId): array
    {
        if ($clientId === null) {
            return [];
        }

        return CustomerContact::query()
            ->where('crm_customer_id', $clientId)
            ->orderBy('first_name')
            ->get()
            ->map(function (CustomerContact $contact): array {
                $name = trim($contact->first_name.' '.$contact->middle_name.' '.$contact->last_name);

                return ['id' => $contact->id, 'text' => $name !== '' ? $name : $contact->email];
            })
            ->all();
    }

    /**
     * @return list<array{value: mixed, label: string}>
     */
    private function submissionOfficers(?string $clientId): array
    {
        if ($clientId === null) {
            return [];
        }

        return CustomerContact::query()
            ->where('crm_customer_id', $clientId)
            ->where('active', 1)
            ->where('can_submit_sample', 1)
            ->get()
            ->map(function (CustomerContact $officer): array {
                $fullName = trim($officer->first_name.' '.$officer->middle_name.' '.$officer->last_name);
                $label = $fullName.($officer->email ? ' ('.$officer->email.')' : '');

                return ['value' => $officer->id, 'label' => $label];
            })
            ->all();
    }

    /**
     * @return list<array{value: mixed, label: string}>
     */
    private function stores(): array
    {
        return InventoryStore::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($store): array => ['value' => $store->id, 'label' => $store->name])
            ->all();
    }

    /**
     * @return list<array{value: mixed, label: string}>
     */
    private function storeSlots(mixed $storeId): array
    {
        if ($storeId === null || $storeId === '') {
            return [];
        }

        return InventoryStoreSlot::query()
            ->where('store_id', $storeId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($slot): array => ['value' => $slot->id, 'label' => $slot->name])
            ->all();
    }

    /**
     * @return list<array{value: mixed, label: string}>
     */
    private function sampleConditions(): array
    {
        return SampleCondition::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($condition): array => ['value' => $condition->id, 'label' => $condition->name])
            ->all();
    }

    /**
     * @return list<array{value: mixed, label: string}>
     */
    private function analysisTypes(mixed $sampleTypeId): array
    {
        if ($sampleTypeId === null || $sampleTypeId === '') {
            return [];
        }

        return AnalysisType::query()
            ->where('sample_type_id', $sampleTypeId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($type): array => ['value' => $type->id, 'label' => $type->name])
            ->all();
    }

    /**
     * @return list<array{value: mixed, label: string}>
     */
    private function analysisElements(mixed $analysisTypeId): array
    {
        if ($analysisTypeId === null || $analysisTypeId === '') {
            return [];
        }

        return AnalysisElements::query()
            ->where('analysis_type_id', $analysisTypeId)
            ->where('active', true)
            ->with('analyte')
            ->get()
            ->map(function (AnalysisElements $element): array {
                $parameterName = 'Unknown Parameter';
                if ($element->analyte) {
                    $parameterName = $element->analyte->name ?? 'Unknown Parameter';
                } elseif ($element->analyte_id) {
                    $analyte = Analyte::query()->find($element->analyte_id);
                    $parameterName = $analyte?->name ?? 'Unknown Parameter';
                }

                $methodName = 'No Method';
                if ($element->method) {
                    $method = \App\AnalysisMethod::query()->find($element->method);
                    $methodName = $method ? ($method->name ?? $element->method) : $element->method;
                }

                return [
                    'value' => $element->id,
                    'label' => $parameterName.' ('.$methodName.')',
                ];
            })
            ->all();
    }

    /**
     * @return list<array{value: mixed, label: string}>
     */
    private function standards(): array
    {
        return Standards::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($standard): array => ['value' => $standard->id, 'label' => $standard->name])
            ->all();
    }

    /**
     * @return list<array{value: mixed, label: string}>
     */
    private function samplePoints(mixed $clientUnitId): array
    {
        if ($clientUnitId === null || $clientUnitId === '') {
            return [];
        }

        return SamplePoint::query()
            ->where('crm_company_unit_id', $clientUnitId)
            ->with('area')
            ->get()
            ->map(function (SamplePoint $samplePoint): array {
                $label = $samplePoint->area && $samplePoint->area->name
                    ? $samplePoint->area->name.' - '.$samplePoint->name
                    : $samplePoint->name;

                return ['value' => $samplePoint->id, 'label' => $label];
            })
            ->all();
    }

    /**
     * @return array{options: list<array{value: mixed, label: string}>, pagination: array<string, mixed>}
     */
    private function userSelect(string $search, int $page, int $perPage): array
    {
        $query = User::query()
            ->where('active', 1)
            ->where('is_client', 0)
            ->whereNull('supplier_id');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'LIKE', '%'.$search.'%')
                    ->orWhere('email', 'LIKE', '%'.$search.'%');
            });
        }

        $paginator = $query->orderBy('name')->paginate($perPage, ['id', 'name', 'email'], 'page', $page);

        $options = collect($paginator->items())
            ->map(function (User $user): array {
                $label = $user->name;
                if (! empty($user->email)) {
                    $label .= ' ('.$user->email.')';
                }

                return ['value' => $user->id, 'label' => $label];
            })
            ->all();

        return [
            'options' => $options,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ];
    }

    /**
     * @return list<array{value: mixed, label: string}>
     */
    private function systemConfigOptions(string $typeName): array
    {
        if ($typeName === '') {
            return [];
        }

        $type = SystemConfigurationsType::where('configuration_type', $typeName)->first();

        if (! $type) {
            return [];
        }

        return $type->configurations()
            ->where('status', true)
            ->orderBy('key')
            ->get()
            ->map(fn ($cfg) => ['value' => $cfg->value, 'label' => $cfg->key])
            ->values()
            ->all();
    }

    private function zones(): array
    {
        $element = new SubmissionFormElement(['element_type' => 'zone_select']);

        return $element->getDynamicOptions();
    }

    /**
     * @param  list<array<string, mixed>>  $raw
     * @return list<array{value: mixed, label: string}>
     */
    private function normalizeOptions(array $raw): array
    {
        return collect($raw)
            ->map(function (array $row): array {
                $value = $row['value'] ?? $row['id'] ?? null;
                $label = $row['label'] ?? $row['text'] ?? (string) $value;

                return ['value' => $value, 'label' => (string) $label];
            })
            ->values()
            ->all();
    }
}
