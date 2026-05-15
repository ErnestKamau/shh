<?php

namespace App\Livewire\Equipment\Concerns;

use App\Models\Assets\AssetLocation;
use App\Models\Assets\AssetType;
use App\Models\Equipments\Equipment;
use App\ReportingUnit;

trait InteractsWithEquipmentFormWizard
{
    public int $currentStep = 1;

    public int $totalSteps = 4;

    public string $monitoredEquipmentSearch = '';

    public bool $showMonitoredEquipmentDropdown = false;

    public string $selectedMonitoredEquipmentLabel = '';

    public string $reportingUnitSearch = '';

    /** @var \Illuminate\Support\Collection<int, \App\Models\Equipments\Equipment> */
    public $filteredMonitoredEquipments = [];

    /** @var \Illuminate\Support\Collection<int, \App\ReportingUnit> */
    public $filteredReportingUnits = [];

    protected function isEditingEquipmentInWizard(): bool
    {
        if (property_exists($this, 'editingEquipment')) {
            return $this->editingEquipment !== null && $this->editingEquipment !== '';
        }

        if (property_exists($this, 'showEditModal')) {
            return (bool) $this->showEditModal;
        }

        return false;
    }

    protected function getStepRules(int $step): array
    {
        $rules = [];

        if ($step === 1) {
            $rules = [
                'equipmentForm.name' => 'required|string|max:255',
                'equipmentForm.equipment_number' => 'required|string|max:255',
                'equipmentForm.description' => 'required|string',
                'equipmentForm.make' => 'required|string|max:255',
                'equipmentForm.model' => 'required|string|max:255',
                'equipmentForm.serial_number' => 'nullable|string|max:255',
                'equipmentForm.barcode_number' => 'nullable|string|max:255',
                'equipmentForm.manufacturer' => 'nullable|string|max:255',
                'photo' => 'nullable|image|max:10240',
            ];
        }

        if ($step === 2) {
            $rules = [
                'equipmentForm.status' => 'required|string',
                'equipmentForm.condition' => 'required|string|max:255',
                'equipmentForm.assigned_department' => 'required|string',
                'equipmentForm.assigned_employee_id' => 'nullable|string',
                'equipmentForm.warranty_date' => 'required|date',
                'equipmentForm.date_purchased' => 'nullable|date',
                'equipmentForm.asset_type_id' => 'nullable|string',
                'equipmentForm.asset_location_id' => 'nullable|string',
                'equipmentForm.active' => 'boolean',
            ];
        }

        if ($step === 3) {
            if (! empty($this->equipmentForm['requires_daily_log'])) {
                $type = $this->equipmentForm['daily_log_value_type'] ?? '';
                $nature = $this->equipmentForm['daily_log_nature'] ?? '';

                $rules['equipmentForm.daily_log_value_type'] = 'required|in:constant,range';
                $rules['equipmentForm.daily_log_nature'] = 'required|in:qualitative,quantitative';
                $rules['equipmentForm.daily_log_frequency'] = 'required|integer|min:1|max:6';
                $rules['equipmentForm.daily_log_reporting_unit'] = 'nullable|string|max:255';
                $rules['equipmentForm.daily_log_monitored_by_another_equipment'] = 'boolean';
                if (! empty($this->equipmentForm['daily_log_monitored_by_another_equipment'])) {
                    $rules['equipmentForm.daily_log_monitored_equipment_id'] = 'required|string';
                }

                if ($type === 'constant') {
                    $rules['equipmentForm.daily_log_expected_value'] = 'required|string|max:255';
                    if ($nature === 'quantitative') {
                        $rules['equipmentForm.daily_log_tolerance'] = 'required|integer|min:1|max:100';
                    }
                }

                if ($type === 'range') {
                    $rules['equipmentForm.daily_log_expected_min'] = 'required|numeric';
                    $rules['equipmentForm.daily_log_expected_max'] = 'required|numeric|gte:equipmentForm.daily_log_expected_min';
                    $rules['equipmentForm.daily_log_tolerance'] = 'required|integer|min:1|max:100';
                }
            }
        }

        if ($step === 4) {
            $rules = [
                'equipmentForm.maintainance_days' => 'required|integer|min:0',
                'equipmentForm.maintainance_notification_in_days' => 'required|integer|min:0',
                'equipmentForm.calibration_days' => 'required|integer|min:0',
                'equipmentForm.calibration_notification_in_days' => 'required|integer|min:0',
            ];

            if (! $this->isEditingEquipmentInWizard()) {
                $rules['equipmentForm.preventive_maintainance_period'] = 'required|integer|min:0';
                $rules['equipmentForm.preventive_maintainance_notification_days'] = 'required|integer|min:0';
            }
        }

        return $rules;
    }

    protected function validateCurrentStep(): void
    {
        $rules = $this->getStepRules($this->currentStep);
        if (! empty($rules)) {
            $this->validate($rules);
        }
    }

    public function goToStep(int $step): void
    {
        $targetStep = max(1, min($this->totalSteps, $step));

        if ($targetStep > $this->currentStep) {
            $this->validateCurrentStep();
        }

        $this->currentStep = $targetStep;
    }

    public function nextStep(): void
    {
        if ($this->currentStep >= $this->totalSteps) {
            return;
        }

        $this->validateCurrentStep();
        $this->currentStep++;
    }

    public function previousStep(): void
    {
        if ($this->currentStep <= 1) {
            return;
        }

        $this->currentStep--;
    }

    public function updatedEquipmentFormRequiresDailyLog(): void
    {
        if (empty($this->equipmentForm['requires_daily_log'])) {
            $this->equipmentForm['daily_log_value_type'] = '';
            $this->equipmentForm['daily_log_nature'] = '';
            $this->equipmentForm['daily_log_tolerance'] = null;
            $this->equipmentForm['daily_log_expected_value'] = '';
            $this->equipmentForm['daily_log_expected_min'] = null;
            $this->equipmentForm['daily_log_expected_max'] = null;
            $this->equipmentForm['daily_log_reporting_unit'] = '';
            $this->equipmentForm['daily_log_frequency'] = 1;
            $this->equipmentForm['daily_log_frequency_labels'] = [];
            $this->equipmentForm['daily_log_monitored_by_another_equipment'] = false;
            $this->equipmentForm['daily_log_monitored_equipment_id'] = null;
            $this->selectedReportingUnitName = '';
            $this->selectedMonitoredEquipmentLabel = '';
            $this->monitoredEquipmentSearch = '';

            $this->syncDailyLogFrequencyLabels();
        }
    }

    public function updatedEquipmentFormDailyLogFrequency(): void
    {
        $this->syncDailyLogFrequencyLabels();
    }

    protected function syncDailyLogFrequencyLabels(): void
    {
        $frequency = max(1, min(6, intval($this->equipmentForm['daily_log_frequency'] ?? 1)));
        $labels = is_array($this->equipmentForm['daily_log_frequency_labels'] ?? null)
            ? $this->equipmentForm['daily_log_frequency_labels']
            : [];

        $normalized = [];
        for ($i = 1; $i <= $frequency; $i++) {
            $key = (string) $i;
            $normalized[$key] = isset($labels[$key]) ? (string) $labels[$key] : '';
        }

        $this->equipmentForm['daily_log_frequency_labels'] = $normalized;
    }

    public function updatedEquipmentFormDailyLogMonitoredByAnotherEquipment(): void
    {
        if (empty($this->equipmentForm['daily_log_monitored_by_another_equipment'])) {
            $this->equipmentForm['daily_log_monitored_equipment_id'] = null;
            $this->selectedMonitoredEquipmentLabel = '';
            $this->monitoredEquipmentSearch = '';
            $this->showMonitoredEquipmentDropdown = false;
        }
    }

    public function updatedEquipmentFormDailyLogValueType(): void
    {
        if (($this->equipmentForm['daily_log_value_type'] ?? '') === 'range') {
            $this->equipmentForm['daily_log_nature'] = 'quantitative';
            $this->equipmentForm['daily_log_expected_value'] = '';
            $this->equipmentForm['daily_log_tolerance'] = null;
        } else {
            $this->equipmentForm['daily_log_expected_min'] = null;
            $this->equipmentForm['daily_log_expected_max'] = null;
        }
    }

    public function updatedEquipmentFormDailyLogNature(): void
    {
        if (($this->equipmentForm['daily_log_nature'] ?? '') !== 'quantitative') {
            $this->equipmentForm['daily_log_tolerance'] = null;
            $this->equipmentForm['daily_log_expected_min'] = null;
            $this->equipmentForm['daily_log_expected_max'] = null;
        } else {
            $this->equipmentForm['daily_log_expected_value'] = '';
        }
    }

    protected function normalizeEquipmentPayload(array $data): array
    {
        $nullableNumeric = [
            'daily_log_expected_min',
            'daily_log_expected_max',
            'daily_log_tolerance',
            'preventive_maintainance_period',
            'preventive_maintainance_notification_days',
        ];

        $nullableUuid = [
            'assigned_employee_id',
            'asset_type_id',
            'asset_location_id',
            'daily_log_monitored_equipment_id',
        ];

        foreach ($nullableNumeric as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === false) {
                $data[$field] = null;
            }
        }

        foreach ($nullableUuid as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    public function searchReportingUnits(): void
    {
        $this->showReportingUnitDropdown = true;
        $search = $this->reportingUnitSearch;

        $this->filteredReportingUnits = ReportingUnit::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function selectReportingUnit($unitIdOrName): void
    {
        $name = ReportingUnit::query()
            ->where('id', $unitIdOrName)
            ->value('name') ?? $unitIdOrName;

        $this->equipmentForm['daily_log_reporting_unit'] = $name;
        $this->selectedReportingUnitName = $name;
        $this->reportingUnitSearch = '';
        $this->showReportingUnitDropdown = false;
    }

    public function clearReportingUnit(): void
    {
        $this->equipmentForm['daily_log_reporting_unit'] = '';
        $this->selectedReportingUnitName = '';
        $this->reportingUnitSearch = '';
    }

    public function searchMonitoredEquipments(): void
    {
        $this->showMonitoredEquipmentDropdown = true;
        $search = $this->monitoredEquipmentSearch;

        $query = Equipment::query()
            ->where('company_id', getUserCompany())
            ->where('is_disposal', 0)
            ->where('active', 1);

        $excludeId = $this->resolveMonitoredEquipmentExcludeId();
        if ($excludeId !== null && $excludeId !== '') {
            $query->where('id', '!=', $excludeId);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('equipment_number', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $this->filteredMonitoredEquipments = $query
            ->orderBy('equipment_number')
            ->limit(10)
            ->get(['id', 'equipment_number', 'name']);
    }

    protected function resolveMonitoredEquipmentExcludeId(): ?string
    {
        if (property_exists($this, 'editingEquipment')) {
            return $this->editingEquipment?->id;
        }

        if (property_exists($this, 'equipmentId')) {
            return $this->equipmentId;
        }

        return null;
    }

    public function selectMonitoredEquipment(string $id): void
    {
        $equipment = Equipment::find($id);
        $label = $equipment ? (($equipment->equipment_number ?? 'N/A') . ' - ' . ($equipment->name ?? '')) : '';

        $this->equipmentForm['daily_log_monitored_equipment_id'] = $id;
        $this->selectedMonitoredEquipmentLabel = $label;
        $this->monitoredEquipmentSearch = '';
        $this->showMonitoredEquipmentDropdown = false;
    }

    public function clearMonitoredEquipment(): void
    {
        $this->equipmentForm['daily_log_monitored_equipment_id'] = null;
        $this->selectedMonitoredEquipmentLabel = '';
        $this->monitoredEquipmentSearch = '';
    }

    public function searchAssetTypes(): void
    {
        $this->showAssetTypeDropdown = true;
        $search = $this->assetTypeSearch;

        $this->filteredAssetTypes = AssetType::where('is_active', 1)
            ->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', '%' . $search . '%')
                    ->orWhere('descripton', 'like', '%' . $search . '%');
            })
            ->limit(10)
            ->get();
    }

    public function selectAssetType($id, $name): void
    {
        $this->equipmentForm['asset_type_id'] = $id;
        $this->selectedAssetTypeName = $name;
        $this->assetTypeSearch = '';
        $this->showAssetTypeDropdown = false;
    }

    public function clearAssetType(): void
    {
        $this->equipmentForm['asset_type_id'] = null;
        $this->selectedAssetTypeName = '';
        $this->assetTypeSearch = '';
    }

    public function searchAssetLocations(): void
    {
        $this->showAssetLocationDropdown = true;
        $search = $this->assetLocationSearch;

        $this->filteredAssetLocations = AssetLocation::where('is_active', 1)
            ->where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->get();
    }

    public function selectAssetLocation($id, $name = null): void
    {
        if ($name === null) {
            $assetLocation = AssetLocation::find($id);
            $name = $assetLocation?->name ?? '';
        }

        $this->equipmentForm['asset_location_id'] = $id;
        $this->selectedAssetLocationName = $name;
        $this->assetLocationSearch = '';
        $this->showAssetLocationDropdown = false;
    }

    public function clearAssetLocation(): void
    {
        $this->equipmentForm['asset_location_id'] = null;
        $this->selectedAssetLocationName = '';
        $this->assetLocationSearch = '';
    }

    public function getDailyLogFrequencyRowsProperty(): array
    {
        $frequency = max(1, min(6, intval($this->equipmentForm['daily_log_frequency'] ?? 1)));
        $labels = is_array($this->equipmentForm['daily_log_frequency_labels'] ?? null)
            ? $this->equipmentForm['daily_log_frequency_labels']
            : [];

        $rows = [];
        for ($i = 1; $i <= $frequency; $i++) {
            $key = (string) $i;
            $rows[] = [
                'id' => $i,
                'label' => $labels[$key] ?? '',
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getEquipmentFormSaveValidationRules(): array
    {
        $rules = [
            'equipmentForm.name' => 'required|string|max:255',
            'equipmentForm.equipment_number' => 'required|string|max:255',
            'equipmentForm.description' => 'required|string',
            'equipmentForm.make' => 'required|string|max:255',
            'equipmentForm.model' => 'required|string|max:255',
            'equipmentForm.serial_number' => 'nullable|string|max:255',
            'equipmentForm.barcode_number' => 'nullable|string|max:255',
            'equipmentForm.manufacturer' => 'nullable|string|max:255',
            'equipmentForm.status' => 'required|string',
            'equipmentForm.condition' => 'required|string|max:255',
            'equipmentForm.assigned_department' => 'required|string',
            'equipmentForm.assigned_employee_id' => 'nullable|string',
            'equipmentForm.warranty_date' => 'required|date',
            'equipmentForm.date_purchased' => 'nullable|date',
            'equipmentForm.maintainance_days' => 'required|integer|min:0',
            'equipmentForm.maintainance_notification_in_days' => 'required|integer|min:0',
            'equipmentForm.calibration_days' => 'required|integer|min:0',
            'equipmentForm.calibration_notification_in_days' => 'required|integer|min:0',
            'equipmentForm.asset_type_id' => 'nullable|string',
            'equipmentForm.asset_location_id' => 'nullable|string',
            'equipmentForm.active' => 'boolean',
            'photo' => 'nullable|image|max:10240',
        ];

        if (! empty($this->equipmentForm['requires_daily_log'])) {
            $type = $this->equipmentForm['daily_log_value_type'] ?? '';
            $nature = $this->equipmentForm['daily_log_nature'] ?? '';

            $rules['equipmentForm.daily_log_value_type'] = 'required|in:constant,range';
            $rules['equipmentForm.daily_log_nature'] = 'required|in:qualitative,quantitative';

            if ($type === 'constant') {
                $rules['equipmentForm.daily_log_expected_value'] = 'required|string|max:255';
                if ($nature === 'quantitative') {
                    $rules['equipmentForm.daily_log_tolerance'] = 'required|integer|min:1|max:100';
                }
            }

            if ($type === 'range') {
                $rules['equipmentForm.daily_log_expected_min'] = 'required|numeric';
                $rules['equipmentForm.daily_log_expected_max'] = 'required|numeric|gte:equipmentForm.daily_log_expected_min';
                $rules['equipmentForm.daily_log_tolerance'] = 'required|integer|min:1|max:100';
            }

            $rules['equipmentForm.daily_log_frequency'] = 'required|integer|min:1|max:6';
            $rules['equipmentForm.daily_log_reporting_unit'] = 'nullable|string|max:255';
            $rules['equipmentForm.daily_log_monitored_by_another_equipment'] = 'boolean';
            if (! empty($this->equipmentForm['daily_log_monitored_by_another_equipment'])) {
                $rules['equipmentForm.daily_log_monitored_equipment_id'] = 'required|string';
            }
        }

        return $rules;
    }
}
