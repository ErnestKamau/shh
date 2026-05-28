<?php

namespace App\Livewire\Equipment\Concerns;

use App\Enums\Equipment\DepreciationMethodCode;
use App\Jobs\Equipment\GenerateDepreciationScheduleJob;
use App\Jobs\Equipment\RecalculateDepreciationScheduleJob;
use App\Models\Equipments\Depreciation\DepreciationMethod;
use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use App\Models\Equipments\Equipment;
use App\ModulePreConfigs;
use App\Services\Equipment\Depreciation\DepreciationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

trait InteractsWithEquipmentDepreciationWizard
{
    public array $depreciationForm = [];

    public bool $showDepreciationCurrencyDropdown = false;

    public string $depreciationCurrencySearch = '';

    public bool $showDepreciationMethodDropdown = false;

    public string $depreciationMethodSearch = '';

    public function bootInteractsWithEquipmentDepreciationWizard(): void
    {
        $this->totalSteps = 5;
    }

    public function getDepreciationMethodsProperty(): \Illuminate\Support\Collection
    {
        return DepreciationMethod::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getCurrencyOptionsProperty(): \Illuminate\Support\Collection
    {
        return ModulePreConfigs::query()
            ->where('type', 'Currency')
            ->orderBy('name')
            ->get();
    }

    public function getFilteredDepreciationCurrenciesProperty(): \Illuminate\Support\Collection
    {
        $search = trim($this->depreciationCurrencySearch);

        if ($search === '') {
            return $this->currencyOptions;
        }

        return $this->currencyOptions->filter(function ($currency) use ($search) {
            $name = (string) ($currency->name ?? '');
            $description = (string) ($currency->description ?? '');

            return stripos($name, $search) !== false
                || stripos($description, $search) !== false;
        })->values();
    }

    public function getSelectedDepreciationCurrencyProperty(): ?ModulePreConfigs
    {
        $code = $this->depreciationForm['currency'] ?? '';

        if ($code === '') {
            return null;
        }

        return $this->currencyOptions->firstWhere('name', $code);
    }

    public function getFilteredDepreciationMethodsProperty(): \Illuminate\Support\Collection
    {
        $search = trim($this->depreciationMethodSearch);

        if ($search === '') {
            return $this->depreciationMethods;
        }

        return $this->depreciationMethods->filter(
            fn ($method) => stripos((string) $method->name, $search) !== false
        )->values();
    }

    public function getSelectedDepreciationMethodProperty(): ?DepreciationMethod
    {
        $methodId = $this->depreciationForm['depreciation_method_id'] ?? '';

        if ($methodId === '') {
            return null;
        }

        return $this->depreciationMethods->firstWhere('id', $methodId);
    }

    public function selectDepreciationCurrency(string $name): void
    {
        $this->depreciationForm['currency'] = $name;
        $this->depreciationCurrencySearch = '';
        $this->showDepreciationCurrencyDropdown = false;
    }

    public function clearDepreciationCurrency(): void
    {
        $this->depreciationForm['currency'] = '';
        $this->depreciationCurrencySearch = '';
        $this->showDepreciationCurrencyDropdown = false;
    }

    public function selectDepreciationMethod(string $id): void
    {
        $this->depreciationForm['depreciation_method_id'] = $id;
        $this->depreciationMethodSearch = '';
        $this->showDepreciationMethodDropdown = false;
    }

    public function clearDepreciationMethod(): void
    {
        $this->depreciationForm['depreciation_method_id'] = '';
        $this->depreciationMethodSearch = '';
        $this->showDepreciationMethodDropdown = false;
    }

    protected function resetDepreciationTagSelectState(): void
    {
        $this->showDepreciationCurrencyDropdown = false;
        $this->depreciationCurrencySearch = '';
        $this->showDepreciationMethodDropdown = false;
        $this->depreciationMethodSearch = '';
    }

    public function initDepreciationForm(): void
    {
        $this->depreciationForm = $this->defaultDepreciationForm();
        $this->resetDepreciationTagSelectState();
    }

    protected function defaultDepreciationForm(): array
    {
        $defaultCurrency = ModulePreConfigs::query()
            ->where('type', 'Currency')
            ->where('name', 'KES')
            ->value('name')
            ?? ModulePreConfigs::query()
                ->where('type', 'Currency')
                ->orderBy('name')
                ->value('name')
            ?? 'USD';

        return [
            'enable_depreciation' => false,
            'depreciation_method_id' => '',
            'currency' => $defaultCurrency,
            'freight_cost' => 0,
            'capitalized_amount' => 0,
            'capitalized_amount_override' => false,
            'depreciation_start_date' => '',
            'useful_life_years' => 5,
            'salvage_value' => 0,
            'frequencies' => ['monthly'],
            'depreciation_rate' => null,
            'declining_balance_type' => 'standard',
            'expected_total_units' => null,
            'unit_type' => '',
            'current_units_used' => 0,
            'usage_source' => 'manual',
            'initial_book_value' => null,
            'current_book_value' => null,
            'accumulated_depreciation' => null,
            'status' => 'disabled',
        ];
    }

    public function loadDepreciationFormFromEquipment(Equipment $equipment): void
    {
        $config = $equipment->depreciationConfig;
        if (! $config) {
            $purchase = (float) ($equipment->purchase_price ?? 0);
            $this->depreciationForm = $this->defaultDepreciationForm();
            $this->depreciationForm['capitalized_amount'] = $purchase;
            $purchased = $equipment->date_purchased;
            $this->depreciationForm['depreciation_start_date'] = $purchased
                ? (\Carbon\Carbon::parse($purchased)->format('Y-m-d'))
                : now()->format('Y-m-d');
            $this->resetDepreciationTagSelectState();

            return;
        }

        $frequencies = $config->resolvedFrequencies();

        $this->depreciationForm = [
            'enable_depreciation' => (bool) $config->enable_depreciation,
            'depreciation_method_id' => $config->depreciation_method_id ?? '',
            'currency' => $config->currency ?? 'USD',
            'freight_cost' => $config->freight_cost,
            'capitalized_amount' => $config->capitalized_amount,
            'capitalized_amount_override' => false,
            'depreciation_start_date' => $config->depreciation_start_date?->format('Y-m-d') ?? '',
            'useful_life_years' => $config->useful_life_years,
            'salvage_value' => $config->salvage_value,
            'frequencies' => $frequencies,
            'depreciation_rate' => $config->depreciation_rate,
            'declining_balance_type' => $config->declining_balance_type?->value ?? $config->declining_balance_type,
            'expected_total_units' => $config->expected_total_units,
            'unit_type' => $config->unit_type ?? '',
            'current_units_used' => $config->current_units_used,
            'usage_source' => $config->usage_source ?? 'manual',
            'initial_book_value' => $config->initial_book_value,
            'current_book_value' => $config->current_book_value,
            'accumulated_depreciation' => $config->accumulated_depreciation,
            'status' => $config->status?->value ?? $config->status,
        ];
        $this->resetDepreciationTagSelectState();
    }

    public function updatedDepreciationFormFreightCost(): void
    {
        $this->syncCapitalizedAmountPreview();
    }

    public function updatedEquipmentFormPurchasePrice(): void
    {
        $this->syncCapitalizedAmountPreview();
    }

    protected function syncCapitalizedAmountPreview(): void
    {
        if (! empty($this->depreciationForm['capitalized_amount_override'])) {
            return;
        }

        $purchase = (float) ($this->equipmentForm['purchase_price'] ?? 0);
        $freight = (float) ($this->depreciationForm['freight_cost'] ?? 0);
        $this->depreciationForm['capitalized_amount'] = round($purchase + $freight, 2);
    }

    protected function getDepreciationStepRules(): array
    {
        if (empty($this->depreciationForm['enable_depreciation'])) {
            return [];
        }

        $currencyNames = $this->currencyOptions->pluck('name')->filter()->values()->all();

        $rules = [
            'depreciationForm.depreciation_method_id' => 'required|string|exists:depreciation_methods,id',
            'depreciationForm.currency' => ['required', 'string', 'max:10', Rule::in($currencyNames)],
            'depreciationForm.freight_cost' => 'nullable|numeric|min:0',
            'depreciationForm.depreciation_start_date' => 'required|date',
            'depreciationForm.useful_life_years' => 'required|integer|min:1|max:100',
            'depreciationForm.salvage_value' => 'required|numeric|min:0',
            'depreciationForm.frequencies' => 'required|array|min:1',
            'depreciationForm.frequencies.*' => 'in:monthly,quarterly,yearly',
        ];

        $capitalized = (float) ($this->depreciationForm['capitalized_amount'] ?? 0);
        $rules['depreciationForm.salvage_value'] .= '|lt:' . max(0.01, $capitalized);

        $method = $this->resolveSelectedDepreciationMethod();
        if ($method === DepreciationMethodCode::DecliningBalance->value) {
            $rules['depreciationForm.depreciation_rate'] = 'nullable|numeric|min:0.01|max:100';
            $rules['depreciationForm.declining_balance_type'] = 'required|in:standard,double';
        }

        if ($method === DepreciationMethodCode::UnitsOfProduction->value) {
            $rules['depreciationForm.expected_total_units'] = 'required|numeric|min:0.0001';
            $rules['depreciationForm.unit_type'] = 'required|string|max:100';
            $rules['depreciationForm.current_units_used'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    protected function resolveSelectedDepreciationMethod(): ?string
    {
        if (empty($this->depreciationForm['depreciation_method_id'])) {
            return null;
        }

        $method = DepreciationMethod::query()->find($this->depreciationForm['depreciation_method_id']);

        if (! $method) {
            return null;
        }

        $code = $method->code;

        return $code instanceof DepreciationMethodCode ? $code->value : (string) $code;
    }

    protected function persistDepreciationConfig(Equipment $equipment, bool $isNew): void
    {
        $service = app(DepreciationService::class);
        $previous = EquipmentDepreciationConfig::query()->where('equipment_id', $equipment->id)->first();

        $attributes = $this->depreciationForm;
        if (empty($attributes['capitalized_amount_override'])) {
            $attributes['capitalized_amount'] = (float) ($equipment->purchase_price ?? 0)
                + (float) ($attributes['freight_cost'] ?? 0);
        }

        $config = $service->upsertConfig($equipment, $attributes);

        if (! $config->enable_depreciation) {
            return;
        }

        $userId = Auth::id();
        if ($isNew || ! $previous) {
            GenerateDepreciationScheduleJob::dispatch($config->id, $userId, 'initial');

            return;
        }

        if ($service->hasImpactingChanges($previous, $attributes) || ! $previous->enable_depreciation) {
            RecalculateDepreciationScheduleJob::dispatch($config->id, $userId, 'equipment_edit');
        }
    }
}
