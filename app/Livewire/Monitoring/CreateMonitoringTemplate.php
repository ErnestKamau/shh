<?php

namespace App\Livewire\Monitoring;

use App\Analyte;
use App\Lab;
use App\LabSection;
use App\Livewire\Monitoring\Concerns\InteractsWithMonitoringConfiguredFields;
use App\Livewire\Monitoring\Concerns\InteractsWithMonitoringReadingSteps;
use App\Models\Equipments\Equipment;
use App\Models\Formulars\LookupTable;
use App\Models\Monitoring\MonitoringTemplate;
use App\Models\Monitoring\MonitoringTemplateField;
use App\Models\Monitoring\MonitoringVariable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateMonitoringTemplate extends Component
{
    use InteractsWithMonitoringConfiguredFields;
    use InteractsWithMonitoringReadingSteps;

    public int $currentStep = 1;

    public string $templateType = 'environmental';

    public string $module = 'lab';

    public string $name = '';

    public string $documentControlNumber = '';

    public string $version = '1';

    public string $effectiveDate = '';

    public string $status = 'draft';

    public array $selectedLabIds = [];

    public array $selectedSectionIds = [];

    public array $selectedEquipmentIds = [];

    /** @deprecated Retained for Livewire hydration compatibility. Use $readingSteps instead. */
    public array $columnStructure = [];

    public array $newVariable = [
        'name' => '',
        'slug' => '',
        'variable_type' => 'constant',
        'constant_value' => '',
        'description' => '',
    ];

    public bool $showVariableModal = false;

    public function mount(?string $module = null, ?string $templateType = null): void
    {
        if ($module !== null) {
            $this->module = $module;
        }
        if ($templateType !== null) {
            $this->templateType = $templateType;
        }

        $this->readingSteps = [];
        $this->configuredFields = [];
    }

    public function getAssignedLabsProperty()
    {
        return Lab::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getEnvironmentalSectionsByLabProperty()
    {
        if (empty($this->selectedLabIds)) {
            return collect();
        }

        return LabSection::query()
            ->whereIn('lab_id', $this->selectedLabIds)
            ->where('does_environmental_analysis', true)
            ->where('active', true)
            ->orderBy('lab_id')
            ->orderBy('name')
            ->with(['lab', 'equipment', 'equipment.latestCalibration', 'reportingUnit'])
            ->get();
    }

    public function getEquipmentByLabProperty()
    {
        if (empty($this->selectedLabIds)) {
            return collect();
        }

        return Equipment::query()
            ->whereIn('lab_id', $this->selectedLabIds)
            ->where('requires_daily_log', true)
            ->where('active', true)
            ->orderBy('lab_id')
            ->orderBy('name')
            ->with(['latestCalibration'])
            ->get();
    }

    public function getSelectedSectionsProperty()
    {
        if (empty($this->selectedSectionIds)) {
            return collect();
        }

        return $this->environmentalSectionsByLab->whereIn('id', $this->selectedSectionIds);
    }

    public function getSelectedEquipmentsProperty()
    {
        if (empty($this->selectedEquipmentIds)) {
            return collect();
        }

        return $this->equipmentByLab->whereIn('id', $this->selectedEquipmentIds);
    }

    public function nextStep(): void
    {
        $this->validate($this->getStepValidationRules());

        if ($this->currentStep < 5) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function setTemplateType(string $type): void
    {
        if (in_array($type, ['environmental', 'equipment'], true)) {
            $this->templateType = $type;
        }
    }

    public function updatedSelectedLabIds(): void
    {
        $this->normalizeSelectedLabIds();
    }

    protected function normalizeSelectedLabIds(): void
    {
        $this->selectedLabIds = array_values(array_unique(array_map(
            fn ($id) => (string) $id,
            $this->selectedLabIds
        )));

        $this->selectedSectionIds = [];
        $this->selectedEquipmentIds = [];
    }

    public function toggleSection(string $sectionId): void
    {
        if (in_array($sectionId, $this->selectedSectionIds)) {
            $this->selectedSectionIds = array_values(array_filter(
                $this->selectedSectionIds,
                fn ($id) => $id !== $sectionId
            ));
        } else {
            $this->selectedSectionIds[] = $sectionId;
        }
    }

    public function toggleEquipment(string $equipmentId): void
    {
        if (in_array($equipmentId, $this->selectedEquipmentIds)) {
            $this->selectedEquipmentIds = array_values(array_filter(
                $this->selectedEquipmentIds,
                fn ($id) => $id !== $equipmentId
            ));
        } else {
            $this->selectedEquipmentIds[] = $equipmentId;
        }
    }

    public function getAvailableVariablesProperty()
    {
        $dbVars = MonitoringVariable::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['slug', 'name', 'variable_type']);

        $predefined = collect([
            [
                'slug' => 'correction_factor',
                'name' => 'Equipment Correction Factor (Dynamic)',
                'variable_type' => 'dynamic',
            ],
            [
                'slug' => 'uncertainty_of_measure',
                'name' => 'Equipment Uncertainty of Measure (Dynamic)',
                'variable_type' => 'dynamic',
            ],
        ]);

        if ($this->templateType === 'environmental') {
            $predefined = $predefined->concat($this->environmentalLimitPredefinedVariables());
        }

        return $predefined->concat($dbVars);
    }

    public function openVariableModal(): void
    {
        $this->newVariable = [
            'name' => '',
            'slug' => '',
            'variable_type' => 'constant',
            'constant_value' => '',
            'description' => '',
        ];
        $this->resetErrorBag(['newVariable.name', 'newVariable.slug', 'newVariable.constant_value']);
        $this->showVariableModal = true;
    }

    public function closeVariableModal(): void
    {
        $this->showVariableModal = false;
    }

    public function createCustomVariable(): void
    {
        $this->validate([
            'newVariable.name' => 'required|string|max:255',
            'newVariable.slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                function ($attribute, $value, $fail) {
                    if (in_array($value, self::reservedMonitoringVariableSlugs(), true)) {
                        $fail('The slug matches a predefined system variable.');
                    }
                    if (MonitoringVariable::where('slug', $value)->exists()) {
                        $fail('The variable slug has already been taken.');
                    }
                },
            ],
            'newVariable.constant_value' => 'required|string|max:255',
            'newVariable.description' => 'nullable|string|max:1000',
        ], [], [
            'newVariable.name' => 'variable name',
            'newVariable.slug' => 'variable slug',
            'newVariable.constant_value' => 'value',
        ]);

        MonitoringVariable::create([
            'name' => $this->newVariable['name'],
            'slug' => $this->newVariable['slug'],
            'variable_type' => 'constant',
            'value' => ['constant_value' => $this->newVariable['constant_value']],
            'description' => $this->newVariable['description'] ?: null,
            'is_active' => true,
            'company_id' => Auth::user()?->company_id,
        ]);

        $this->showVariableModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Custom variable "'.$this->newVariable['name'].'" defined successfully!']);
    }

    public function saveTemplate()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'documentControlNumber' => 'nullable|string|max:255',
            'version' => 'required|integer|min:1',
            'effectiveDate' => 'nullable|date',
            'selectedLabIds' => 'required|array|min:1',
            'selectedSectionIds' => $this->templateType === 'environmental' ? 'required|array|min:1' : 'nullable',
            'selectedEquipmentIds' => $this->templateType === 'equipment' ? 'required|array|min:1' : 'nullable',
            ...$this->readingStructureValidationRules(),
        ]);

        try {
            DB::beginTransaction();

            $template = MonitoringTemplate::create([
                'name' => $this->name,
                'document_control_number' => $this->documentControlNumber ?: null,
                'version' => (int) $this->version,
                'effective_date' => $this->effectiveDate ?: null,
                'monitoring_category' => $this->templateType,
                'status' => $this->status,
                'lab_id' => $this->selectedLabIds[0] ?? null,
                'company_id' => Auth::user()?->company_id,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'is_active' => true,
            ]);

            $this->persistReadingStructure($template);
            $this->persistConfiguredFields($template);

            MonitoringTemplateField::create([
                'template_id' => $template->id,
                'field_key' => '__meta_scope_items',
                'label' => 'System Meta Data',
                'field_type' => 'metadata',
                'is_required' => false,
                'is_readonly' => true,
                'sort_order' => 9999,
                'field_config' => [
                    'sections' => $this->selectedSectionIds,
                    'equipment' => $this->selectedEquipmentIds,
                ],
            ]);

            DB::commit();

            session()->flash('success', 'Monitoring template "'.$this->name.'" created successfully.');

            return redirect()->route($this->module === 'equipment' ? 'equipment.monitoring' : 'livewire.monitoring');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error creating template: '.$e->getMessage()]);
        }
    }

    public function cancel()
    {
        return redirect()->route($this->module === 'equipment' ? 'equipment.monitoring' : 'livewire.monitoring');
    }

    protected function getStepValidationRules(): array
    {
        return match ($this->currentStep) {
            1 => [
                'name' => 'required|string|max:255',
                'documentControlNumber' => 'nullable|string|max:255',
                'version' => 'required|integer|min:1',
                'templateType' => 'required|in:environmental,equipment',
            ],
            2 => [
                'selectedLabIds' => ['required', 'array', 'min:1'],
                'selectedLabIds.*' => ['required', 'string', 'exists:labs,id'],
            ],
            3 => $this->templateType === 'environmental' ?
                ['selectedSectionIds' => 'required|array|min:1'] :
                ['selectedEquipmentIds' => 'required|array|min:1'],
            4 => $this->readingStructureValidationRules(),
            5 => [],
            default => [],
        };
    }

    public function render()
    {
        return view('livewire.monitoring.create-monitoring-template', [
            'lookupTables' => LookupTable::where('is_active', true)->orderBy('name')->get(),
            'analytes' => Analyte::orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }
}
