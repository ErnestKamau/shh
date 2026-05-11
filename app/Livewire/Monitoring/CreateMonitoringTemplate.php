<?php

namespace App\Livewire\Monitoring;

use App\Lab;
use App\LabSection;
use App\Models\Equipments\Equipment;
use App\Models\Equipments\MaintainanceCalibrationLog;
use App\Models\Monitoring\MonitoringTemplate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateMonitoringTemplate extends Component
{
    // Step management
    public int $currentStep = 1;

    // Template type
    public string $templateType = 'environmental'; // 'environmental' or 'equipment'

    // Step 1: Basic Info
    public string $name = '';

    public string $documentControlNumber = '';

    public string $version = '1';

    public string $effectiveDate = '';

    public string $status = 'draft';

    // Step 2: Lab Selection
    public array $selectedLabIds = [];

    // Step 3: Section/Equipment Selection
    public array $selectedSectionIds = [];

    public array $selectedEquipmentIds = [];

    // Step 4: Column Structure
    public array $columnStructure = [];

    public function mount(): void
    {
        // Initialize with empty column structure
        $this->columnStructure = [];
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
            ->with(['lab', 'equipment', 'equipment.latestCalibration'])
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

        if ($this->currentStep < 4) {
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

    public function toggleLab(string $labId): void
    {
        if (in_array($labId, $this->selectedLabIds)) {
            $this->selectedLabIds = array_values(array_filter(
                $this->selectedLabIds,
                fn($id) => $id !== $labId
            ));
        } else {
            $this->selectedLabIds[] = $labId;
        }

        // Reset selection for next step
        $this->selectedSectionIds = [];
        $this->selectedEquipmentIds = [];
    }

    public function toggleSection(string $sectionId): void
    {
        if (in_array($sectionId, $this->selectedSectionIds)) {
            $this->selectedSectionIds = array_values(array_filter(
                $this->selectedSectionIds,
                fn($id) => $id !== $sectionId
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
                fn($id) => $id !== $equipmentId
            ));
        } else {
            $this->selectedEquipmentIds[] = $equipmentId;
        }
    }

    public function addColumn(): void
    {
        $newColumn = [
            'id' => 'col_' . uniqid(),
            'name' => '',
            'frequency' => 'daily',
            'rows' => [
                [
                    'id' => 'row_' . uniqid(),
                    'label' => 'Initial Value',
                    'type' => 'numeric',
                ],
                [
                    'id' => 'row_' . uniqid(),
                    'label' => 'Final Value',
                    'type' => 'numeric',
                ],
            ],
        ];

        $this->columnStructure[] = $newColumn;
    }

    public function removeColumn(string $columnId): void
    {
        $this->columnStructure = array_values(array_filter(
            $this->columnStructure,
            fn($col) => $col['id'] !== $columnId
        ));
    }

    public function updateColumnName(string $columnId, string $name): void
    {
        foreach ($this->columnStructure as &$col) {
            if ($col['id'] === $columnId) {
                $col['name'] = $name;
                break;
            }
        }
    }

    public function addRowToColumn(string $columnId): void
    {
        foreach ($this->columnStructure as &$col) {
            if ($col['id'] === $columnId) {
                $col['rows'][] = [
                    'id' => 'row_' . uniqid(),
                    'label' => '',
                    'type' => 'numeric',
                ];
                break;
            }
        }
    }

    public function removeRowFromColumn(string $columnId, string $rowId): void
    {
        foreach ($this->columnStructure as &$col) {
            if ($col['id'] === $columnId) {
                $col['rows'] = array_values(array_filter(
                    $col['rows'],
                    fn($row) => $row['id'] !== $rowId
                ));
                break;
            }
        }
    }

    public function updateRowLabel(string $columnId, string $rowId, string $label): void
    {
        foreach ($this->columnStructure as &$col) {
            if ($col['id'] === $columnId) {
                foreach ($col['rows'] as &$row) {
                    if ($row['id'] === $rowId) {
                        $row['label'] = $label;
                        break;
                    }
                }
                break;
            }
        }
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
            'columnStructure' => 'required|array|min:1',
        ]);

        $template = MonitoringTemplate::create([
            'name' => $this->name,
            'document_control_number' => $this->documentControlNumber ?: null,
            'version' => (int) $this->version,
            'effective_date' => $this->effectiveDate ?: null,
            'monitoring_category' => $this->templateType,
            'status' => $this->status,
            'lab_id' => $this->selectedLabIds[0] ?? null, // Primary lab
            'company_id' => Auth::user()?->company_id,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'is_active' => true,
        ]);

        // Store column structure metadata
        $metadata = [
            'column_structure' => $this->columnStructure,
            'selected_labs' => $this->selectedLabIds,
            'selected_sections' => $this->selectedSectionIds,
            'selected_equipments' => $this->selectedEquipmentIds,
        ];

        // For now, store in template data - can be extended to create actual fields
        // This would be processed by a separate action to create MonitoringTemplateFields

        session()->flash('success', 'Monitoring template "' . $this->name . '" created successfully. You can now add fields and formula rules.');

        redirect()->route('livewire.monitoring');
    }

    public function cancel()
    {
        redirect()->route('livewire.monitoring');
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
                'selectedLabIds' => 'required|array|min:1',
            ],
            3 => $this->templateType === 'environmental' ?
                ['selectedSectionIds' => 'required|array|min:1'] :
                ['selectedEquipmentIds' => 'required|array|min:1'],
            4 => [
                'columnStructure' => 'required|array|min:1',
            ],
            default => [],
        };
    }

    public function render()
    {
        return view('livewire.monitoring.create-monitoring-template');
    }
}
