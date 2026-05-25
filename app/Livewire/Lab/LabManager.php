<?php

namespace App\Livewire\Lab;

use App\Directorate;
use App\Lab;
use App\LabSection;
use App\ReportingUnit;
use App\Zone;
use App\Models\Equipments\Equipment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LabManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Lab Form
    public $labForm = [
        'name' => '',
        'code' => '',
        'start_sample_no' => '',
        'address' => '',
        'location' => '',
        'website' => '',
        'fax' => '',
        'email' => '',
        'phone1' => '',
        'phone2' => '',
        'phone3' => '',
        'is_external' => false,
        'active' => true,
    ];

    // Search and Filters
    public $search = '';
    public $zoneFilter = '';
    public $directorateFilter = '';
    public string $zoneFilterSearch = '';
    public string $directorateFilterSearch = '';
    public bool $showZoneFilterDropdown = false;
    public bool $showDirectorateFilterDropdown = false;

    // Modal States
    public $showLabModal = false;
    public $showLabSectionModal = false;
    public $showDeleteModal = false;

    // Delete Confirmation
    public $deleteId = null;
    public $deleteDetails = [];
    public string $deleteType = 'lab';

    // Editing State
    public $editingLab = null;

    // Messages
    public $message = '';
    public $messageType = '';

    // Pagination
    public $perPage = 10;
    public $perPageOptions = [10, 25, 50, 100];

    // Expandable rows and nested section forms
    public array $expandedLabs = [];
    public array $labSectionForms = [];
    public ?string $activeLabSectionLabId = null;
    public ?string $editingLabSection = null;
    public string $labSectionEquipmentSearch = '';
    public bool $showLabSectionEquipmentDropdown = false;
    public string $labSectionExpectedValueTypeSearch = '';
    public bool $showLabSectionExpectedValueTypeDropdown = false;
    public string $labSectionResultNatureSearch = '';
    public bool $showLabSectionResultNatureDropdown = false;
    public string $labSectionReportingUnitSearch = '';
    public bool $showLabSectionReportingUnitDropdown = false;

    public function mount(): void
    {
        // Initialize with defaults
    }

    public function getLabsProperty()
    {
        $query = Lab::query()->with([
            'zone',
            'directorate',
            'labSections' => function ($q): void {
                $q->with(['equipment', 'reportingUnit'])->orderBy('name');
            },
        ]);

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->zoneFilter !== '') {
            $query->where('zone_id', $this->zoneFilter);
        }

        if ($this->directorateFilter !== '') {
            $query->where('directorate_id', $this->directorateFilter);
        }

        return $query->orderBy('name')->paginate($this->perPage);
    }

    public function getAvailableEquipmentsProperty()
    {
        return Equipment::query()
            ->where('active', 1)
            ->orderBy('name')
            ->select(['id', 'name', 'equipment_number', 'lab_id'])
            ->get();
    }

    public function toggleLabRow(string $labId): void
    {
        if (in_array($labId, $this->expandedLabs, true)) {
            $this->expandedLabs = array_values(array_filter(
                $this->expandedLabs,
                fn ($id) => $id !== $labId
            ));

            return;
        }

        $this->expandedLabs[] = $labId;
        $this->initializeLabSectionForm($labId);
    }

    public function initializeLabSectionForm(string $labId): void
    {
        $this->labSectionForms[$labId] = [
            'name' => '',
            'code' => '',
            'description' => '',
            'does_environmental_analysis' => false,
            'equipment_id' => '',
            'expected_value_type' => '',
            'expected_value' => null,
            'expected_min' => null,
            'expected_max' => null,
            'optimum_level' => '',
            'result_nature' => '',
            'reading_frequency' => 1,
            'reading_frequency_interval' => null,
            'reading_frequency_schedule' => [],
            'reporting_unit' => '',
            'active' => true,
        ];

        $this->syncLabSectionFrequencySchedule($labId);
    }

    protected function populateLabSectionForm(string $labId, LabSection $section): void
    {
        $this->labSectionForms[$labId] = [
            'name' => $section->name,
            'code' => $section->code,
            'description' => $section->description ?? '',
            'does_environmental_analysis' => (bool) $section->does_environmental_analysis,
            'equipment_id' => $section->equipment_id ?? '',
            'expected_value_type' => $section->expected_value_type ?? '',
            'expected_value' => $section->expected_value,
            'expected_min' => $section->expected_min,
            'expected_max' => $section->expected_max,
            'optimum_level' => $section->optimum_level ?? '',
            'result_nature' => $section->result_nature ?? '',
            'reading_frequency' => $section->reading_frequency ?? 1,
            'reading_frequency_interval' => $section->reading_frequency_interval,
            'reading_frequency_schedule' => $this->scheduleFromSection($section),
            'reporting_unit' => $section->reporting_unit ?? '',
            'active' => (bool) $section->active,
        ];

        $this->syncLabSectionFrequencySchedule($labId);
    }

    public function updated($property, $value): void
    {
        if (preg_match('/^labSectionForms\.([^.]+)\.reading_frequency$/', $property, $matches)) {
            $this->syncLabSectionFrequencySchedule($matches[1]);
        }

        if (preg_match('/^labSectionForms\.([^.]+)\.does_environmental_analysis$/', $property, $matches) && $value) {
            $this->syncLabSectionFrequencySchedule($matches[1]);
        }
    }

    protected function syncLabSectionFrequencySchedule(string $labId): void
    {
        if (! isset($this->labSectionForms[$labId])) {
            return;
        }

        $count = max(1, min(5, (int) ($this->labSectionForms[$labId]['reading_frequency'] ?? 1)));
        $existing = $this->labSectionForms[$labId]['reading_frequency_schedule'] ?? [];

        if (! is_array($existing)) {
            $existing = [];
        }

        $bySlot = [];
        foreach ($existing as $row) {
            if (! is_array($row)) {
                continue;
            }

            $slot = (int) ($row['frequency'] ?? $row['slot'] ?? 0);
            if ($slot > 0) {
                $bySlot[$slot] = $row;
            }
        }

        $legacyInterval = $this->labSectionForms[$labId]['reading_frequency_interval'] ?? null;
        $schedule = [];

        for ($slot = 1; $slot <= $count; $slot++) {
            $prev = $bySlot[$slot] ?? [];
            $interval = $prev['interval'] ?? null;

            if ($slot > 1 && ($interval === null || $interval === '') && $legacyInterval !== null && $legacyInterval !== '') {
                $interval = $legacyInterval;
            }

            $schedule[] = [
                'frequency' => $slot,
                'interval' => $slot === 1 ? null : ($interval !== null && $interval !== '' ? $interval : ''),
                'label' => (string) ($prev['label'] ?? ''),
            ];
        }

        $this->labSectionForms[$labId]['reading_frequency_schedule'] = $schedule;
    }

    /**
     * @return list<array{frequency: int, interval: mixed, label: string}>
     */
    protected function scheduleFromSection(LabSection $section): array
    {
        $stored = $section->reading_frequency_schedule;

        if (is_array($stored) && $stored !== []) {
            return array_values($stored);
        }

        $count = max(1, min(5, (int) ($section->reading_frequency ?? 1)));
        $legacyInterval = $section->reading_frequency_interval;
        $schedule = [];

        for ($slot = 1; $slot <= $count; $slot++) {
            $schedule[] = [
                'frequency' => $slot,
                'interval' => $slot === 1 ? null : ($legacyInterval !== null ? (string) $legacyInterval : ''),
                'label' => '',
            ];
        }

        return $schedule;
    }

    /**
     * @param  list<array{frequency?: int, interval?: mixed, label?: string}>  $schedule
     * @return list<array{frequency: int, interval: float|null, label: string}>
     */
    protected function normalizeReadingFrequencySchedule(array $schedule, int $count): array
    {
        $normalized = [];

        for ($index = 0; $index < $count; $index++) {
            $row = $schedule[$index] ?? [];
            $slot = $index + 1;
            $interval = $row['interval'] ?? null;

            if ($slot === 1) {
                $interval = null;
            } elseif ($interval !== null && $interval !== '') {
                $interval = (float) $interval;
            } else {
                $interval = null;
            }

            $normalized[] = [
                'frequency' => $slot,
                'interval' => $interval,
                'label' => trim((string) ($row['label'] ?? '')),
            ];
        }

        return $normalized;
    }

    /**
     * @param  list<array{frequency: int, interval: float|null, label: string}>  $schedule
     */
    protected function deriveReadingFrequencyInterval(array $schedule, int $count): ?float
    {
        if ($count <= 1) {
            return null;
        }

        foreach ($schedule as $row) {
            if (($row['frequency'] ?? 0) > 1 && $row['interval'] !== null) {
                return (float) $row['interval'];
            }
        }

        return null;
    }

    protected function resetLabSectionDropdownState(): void
    {
        $this->labSectionEquipmentSearch = '';
        $this->showLabSectionEquipmentDropdown = false;
        $this->labSectionExpectedValueTypeSearch = '';
        $this->showLabSectionExpectedValueTypeDropdown = false;
        $this->labSectionResultNatureSearch = '';
        $this->showLabSectionResultNatureDropdown = false;
        $this->labSectionReportingUnitSearch = '';
        $this->showLabSectionReportingUnitDropdown = false;
    }

    public function resetLabSectionForm(string $labId): void
    {
        if ($this->editingLabSection && $this->activeLabSectionLabId === $labId) {
            $section = LabSection::find($this->editingLabSection);

            if ($section) {
                $this->populateLabSectionForm($labId, $section);
            } else {
                $this->initializeLabSectionForm($labId);
                $this->editingLabSection = null;
            }
        } else {
            $this->initializeLabSectionForm($labId);
        }

        if ($this->activeLabSectionLabId === $labId) {
            $this->resetLabSectionDropdownState();
        }

        $this->resetValidation();
    }

    public function showCreateLabSectionModal(string $labId): void
    {
        if (!in_array($labId, $this->expandedLabs, true)) {
            $this->expandedLabs[] = $labId;
        }

        if (!isset($this->labSectionForms[$labId])) {
            $this->initializeLabSectionForm($labId);
        }

        $this->activeLabSectionLabId = $labId;
        $this->editingLabSection = null;
        $this->initializeLabSectionForm($labId);
        $this->resetLabSectionDropdownState();
        $this->resetValidation();
        $this->showLabSectionModal = true;
    }

    public function showEditLabSectionModal(string $sectionId): void
    {
        $section = LabSection::findOrFail($sectionId);
        $labId = (string) $section->lab_id;

        if (!in_array($labId, $this->expandedLabs, true)) {
            $this->expandedLabs[] = $labId;
        }

        $this->activeLabSectionLabId = $labId;
        $this->editingLabSection = $sectionId;
        $this->populateLabSectionForm($labId, $section);
        $this->resetLabSectionDropdownState();
        $this->resetValidation();
        $this->showLabSectionModal = true;
    }

    public function closeLabSectionModal(): void
    {
        $this->showLabSectionModal = false;
        $this->activeLabSectionLabId = null;
        $this->editingLabSection = null;
        $this->resetLabSectionDropdownState();
        $this->resetValidation();
    }

    public function getFilteredLabSectionEquipmentsProperty()
    {
        $search = trim($this->labSectionEquipmentSearch);

        return collect($this->availableEquipments)
            ->filter(function ($equipment) use ($search): bool {
                if ($search === '') {
                    return true;
                }

                return str_contains(strtolower((string) $equipment->name), strtolower($search))
                    || str_contains(strtolower((string) ($equipment->equipment_number ?? '')), strtolower($search));
            })
            ->values()
            ->take(25);
    }

    public function getSelectedLabSectionEquipmentProperty(): ?Equipment
    {
        $labId = $this->activeLabSectionLabId;

        if (!$labId) {
            return null;
        }

        $equipmentId = data_get($this->labSectionForms, $labId . '.equipment_id');

        if (!$equipmentId) {
            return null;
        }

        return $this->availableEquipments->firstWhere('id', $equipmentId);
    }

    public function selectLabSectionEquipment(string $equipmentId): void
    {
        if (!$this->activeLabSectionLabId) {
            return;
        }

        data_set($this->labSectionForms, $this->activeLabSectionLabId . '.equipment_id', $equipmentId);
        $this->labSectionEquipmentSearch = '';
        $this->showLabSectionEquipmentDropdown = false;
    }

    public function clearLabSectionEquipment(): void
    {
        if (!$this->activeLabSectionLabId) {
            return;
        }

        data_set($this->labSectionForms, $this->activeLabSectionLabId . '.equipment_id', '');
        $this->labSectionEquipmentSearch = '';
        $this->showLabSectionEquipmentDropdown = false;
    }

    public function getExpectedValueTypeOptionsProperty()
    {
        return collect([
            ['id' => 'constant', 'label' => 'Constant Value'],
            ['id' => 'range', 'label' => 'Range'],
        ]);
    }

    public function getReportingUnitOptionsProperty()
    {
        return ReportingUnit::query()
            ->where('active', 1)
            ->orderBy('name')
            ->select(['id', 'name'])
            ->get();
    }

    public function getFilteredLabSectionReportingUnitsProperty()
    {
        $search = strtolower(trim($this->labSectionReportingUnitSearch));

        return $this->reportingUnitOptions
            ->filter(function (ReportingUnit $unit) use ($search): bool {
                if ($search === '') {
                    return true;
                }

                return str_contains(strtolower((string) $unit->name), $search);
            })
            ->values();
    }

    public function getSelectedLabSectionReportingUnitProperty(): ?ReportingUnit
    {
        $labId = $this->activeLabSectionLabId;

        if (!$labId) {
            return null;
        }

        $selectedId = data_get($this->labSectionForms, $labId . '.reporting_unit');

        if (!$selectedId) {
            return null;
        }

        return $this->reportingUnitOptions->firstWhere('id', $selectedId);
    }

    public function getFilteredLabSectionExpectedValueTypesProperty()
    {
        $search = strtolower(trim($this->labSectionExpectedValueTypeSearch));

        return $this->expectedValueTypeOptions
            ->filter(function (array $option) use ($search): bool {
                if ($search === '') {
                    return true;
                }

                return str_contains(strtolower($option['label']), $search);
            })
            ->values();
    }

    public function getSelectedLabSectionExpectedValueTypeProperty(): ?array
    {
        $labId = $this->activeLabSectionLabId;

        if (!$labId) {
            return null;
        }

        $selectedType = data_get($this->labSectionForms, $labId . '.expected_value_type');

        if (!$selectedType) {
            return null;
        }

        return $this->expectedValueTypeOptions->firstWhere('id', $selectedType);
    }

    public function selectLabSectionExpectedValueType(string $type): void
    {
        if (!$this->activeLabSectionLabId) {
            return;
        }

        data_set($this->labSectionForms, $this->activeLabSectionLabId . '.expected_value_type', $type);

        if ($type === 'constant') {
            data_set($this->labSectionForms, $this->activeLabSectionLabId . '.expected_min', null);
            data_set($this->labSectionForms, $this->activeLabSectionLabId . '.expected_max', null);
        }

        if ($type === 'range') {
            data_set($this->labSectionForms, $this->activeLabSectionLabId . '.expected_value', null);
            data_set($this->labSectionForms, $this->activeLabSectionLabId . '.optimum_level', '');
        }

        $this->labSectionExpectedValueTypeSearch = '';
        $this->showLabSectionExpectedValueTypeDropdown = false;
    }

    public function clearLabSectionExpectedValueType(): void
    {
        if (!$this->activeLabSectionLabId) {
            return;
        }

        data_set($this->labSectionForms, $this->activeLabSectionLabId . '.expected_value_type', '');
        data_set($this->labSectionForms, $this->activeLabSectionLabId . '.expected_value', null);
        data_set($this->labSectionForms, $this->activeLabSectionLabId . '.expected_min', null);
        data_set($this->labSectionForms, $this->activeLabSectionLabId . '.expected_max', null);
        $this->labSectionExpectedValueTypeSearch = '';
        $this->showLabSectionExpectedValueTypeDropdown = false;
    }

    public function saveLabSection(string $labId): void
    {
        if (!isset($this->labSectionForms[$labId])) {
            $this->initializeLabSectionForm($labId);
        }

        $form = $this->labSectionForms[$labId];
        $analysisOn = (bool) ($form['does_environmental_analysis'] ?? false);
        $valueType = $form['expected_value_type'] ?? null;

        $rules = [
            "labSectionForms.$labId.name" => 'required|string|max:255',
            "labSectionForms.$labId.code" => 'required|string|max:100|unique:lab_sections,code,' . ($this->editingLabSection ?? 'NULL') . ',id,lab_id,' . $labId,
            "labSectionForms.$labId.description" => 'nullable|string|max:2000',
            "labSectionForms.$labId.does_environmental_analysis" => 'boolean',
            "labSectionForms.$labId.active" => 'boolean',
        ];

        if ($analysisOn) {
            $rules["labSectionForms.$labId.equipment_id"] = 'required|exists:equipment,id';
            $rules["labSectionForms.$labId.expected_value_type"] = 'required|in:constant,range';
            $rules["labSectionForms.$labId.result_nature"] = 'required|string|max:1000';
            $rules["labSectionForms.$labId.reading_frequency"] = 'required|integer|min:1|max:5';

            $frequencyCount = (int) ($form['reading_frequency'] ?? 1);
            $schedule = is_array($form['reading_frequency_schedule'] ?? null)
                ? $form['reading_frequency_schedule']
                : [];

            for ($index = 0; $index < $frequencyCount; $index++) {
                $prefix = "labSectionForms.$labId.reading_frequency_schedule.$index";
                $rules["$prefix.label"] = 'required|string|max:255';

                if ($index > 0) {
                    $rules["$prefix.interval"] = 'required|numeric|min:0.01';
                } else {
                    $rules["$prefix.interval"] = 'nullable';
                }
            }

            $rules["labSectionForms.$labId.reporting_unit"] = 'required|exists:reporting_units,id';

            if ($valueType === 'constant') {
                $rules["labSectionForms.$labId.expected_value"] = 'required|numeric';
                $rules["labSectionForms.$labId.optimum_level"] = 'required|string|max:255';
            }

            if ($valueType === 'range') {
                $rules["labSectionForms.$labId.expected_min"] = 'required|numeric';
                $rules["labSectionForms.$labId.expected_max"] = 'required|numeric|gte:labSectionForms.' . $labId . '.expected_min';
            }
        }

        $this->validate($rules);

        $frequencySchedule = $analysisOn
            ? $this->normalizeReadingFrequencySchedule(
                is_array($form['reading_frequency_schedule'] ?? null) ? $form['reading_frequency_schedule'] : [],
                (int) ($form['reading_frequency'] ?? 1)
            )
            : [];

        try {
            DB::beginTransaction();

            $payload = [
                'lab_id' => $labId,
                'name' => trim((string) $form['name']),
                'code' => trim((string) $form['code']),
                'description' => $form['description'] !== '' ? $form['description'] : null,
                'does_environmental_analysis' => $analysisOn,
                'equipment_id' => $analysisOn && $form['equipment_id'] !== '' ? $form['equipment_id'] : null,
                'expected_value_type' => $analysisOn ? $valueType : null,
                'expected_value' => $analysisOn && $valueType === 'constant' ? $form['expected_value'] : null,
                'expected_min' => $analysisOn && $valueType === 'range' ? $form['expected_min'] : null,
                'expected_max' => $analysisOn && $valueType === 'range' ? $form['expected_max'] : null,
                'optimum_level' => $analysisOn && $valueType !== 'range' ? $form['optimum_level'] : null,
                'result_nature' => $analysisOn ? $form['result_nature'] : null,
                'reading_frequency' => $analysisOn ? (int) $form['reading_frequency'] : null,
                'reading_frequency_interval' => $analysisOn
                    ? $this->deriveReadingFrequencyInterval($frequencySchedule, (int) ($form['reading_frequency'] ?? 1))
                    : null,
                'reading_frequency_schedule' => $analysisOn ? $frequencySchedule : null,
                'reporting_unit' => $analysisOn ? $form['reporting_unit'] : null,
                'active' => (bool) ($form['active'] ?? true),
                'company_id' => getUserCompany(),
            ];

            if ($this->editingLabSection) {
                LabSection::findOrFail($this->editingLabSection)->update($payload);
                $this->message = 'Lab section updated successfully!';
            } else {
                LabSection::create($payload);
                $this->message = 'Lab section created successfully!';
            }

            DB::commit();

            $this->messageType = 'success';
            $this->resetLabSectionForm($labId);
            $this->closeLabSectionModal();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error creating lab section: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteLabSection(string $sectionId): void
    {
        try {
            $section = LabSection::findOrFail($sectionId);
            $labId = (string) $section->lab_id;

            $section->delete();

            if ($this->editingLabSection === $sectionId) {
                $this->editingLabSection = null;
                $this->closeLabSectionModal();
                $this->initializeLabSectionForm($labId);
            }

            $this->message = 'Lab section deleted successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Error deleting lab section: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function confirmDeleteLabSection(string $sectionId): void
    {
        $section = LabSection::with(['equipment', 'reportingUnit'])->findOrFail($sectionId);

        $this->deleteType = 'lab_section';
        $this->deleteId = $sectionId;
        $this->deleteDetails = [
            'name' => $section->name,
            'code' => $section->code,
            'description' => $section->description,
            'environmental_monitoring' => $section->does_environmental_analysis ? 'Yes' : 'No',
            'equipment' => $section->equipment->name ?? '-',
            'expected_value' => $section->expected_value_type === 'constant'
                ? 'Constant: '.($section->expected_value ?? '-')
                : ($section->expected_value_type === 'range'
                    ? 'Range: '.($section->expected_min ?? '-').' - '.($section->expected_max ?? '-')
                    : '-'),
            'optimum_level' => $section->formattedOptimumLevel(),
            'result_nature' => $section->result_nature ?? '-',
            'reading_frequency' => $section->does_environmental_analysis
                ? $section->readingFrequencyLabel()
                : '-',
            'reading_frequency_schedule' => $section->does_environmental_analysis
                ? $section->formattedReadingFrequencySchedule()
                : '-',
            'reporting_unit' => $section->reportingUnit->name ?? '-',
            'active' => $section->active ? 'Active' : 'Inactive',
        ];

        $this->showDeleteModal = true;
    }

    public function showCreateLabModal(): void
    {
        $this->resetLabForm();
        $this->editingLab = null;
        $this->showLabModal = true;
    }

    public function showEditLabModal($id): void
    {
        $lab = Lab::findOrFail($id);
        
        $this->labForm = [
            'name' => $lab->name,
            'code' => $lab->code,
            'start_sample_no' => $lab->start_sample_no,
            'address' => $lab->address,
            'location' => $lab->location,
            'website' => $lab->website,
            'fax' => $lab->fax,
            'email' => $lab->email,
            'phone1' => $lab->phone1,
            'phone2' => $lab->phone2,
            'phone3' => $lab->phone3,
            'is_external' => (bool) $lab->is_external,
            'active' => (bool) $lab->active,
        ];
        
        $this->editingLab = $id;
        $this->showLabModal = true;
    }

    public function closeLabModal(): void
    {
        $this->showLabModal = false;
        $this->resetLabForm();
    }

    public function resetLabForm(): void
    {
        $this->labForm = [
            'name' => '',
            'code' => '',
            'start_sample_no' => '',
            'address' => '',
            'location' => '',
            'website' => '',
            'fax' => '',
            'email' => '',
            'phone1' => '',
            'phone2' => '',
            'phone3' => '',
            'is_external' => false,
            'active' => true,
        ];
        $this->editingLab = null;
        $this->resetValidation();
    }

    public function saveLab(): void
    {
        $this->validate([
            'labForm.name' => 'required|string|max:255',
            'labForm.code' => 'required|string|max:255',
            'labForm.start_sample_no' => 'required|string|max:255',
            'labForm.address' => 'required|string',
            'labForm.location' => 'required|string|max:255',
            'labForm.email' => 'required|email|max:255',
            'labForm.phone1' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->labForm['name'],
                'code' => $this->labForm['code'],
                'start_sample_no' => $this->labForm['start_sample_no'],
                'address' => $this->labForm['address'],
                'location' => $this->labForm['location'],
                'website' => $this->labForm['website'],
                'fax' => $this->labForm['fax'],
                'email' => $this->labForm['email'],
                'phone1' => $this->labForm['phone1'],
                'phone2' => $this->labForm['phone2'],
                'phone3' => $this->labForm['phone3'],
                'is_external' => $this->labForm['is_external'] ? 1 : 0,
                'active' => $this->labForm['active'] ? 1 : 0,
                'company_id' => getUserCompany(),
            ];

            if ($this->editingLab) {
                Lab::findOrFail($this->editingLab)->update($data);
                $this->message = 'Lab updated successfully!';
            } else {
                Lab::create($data);
                $this->message = 'Lab created successfully!';
            }

            DB::commit();
            
            $this->messageType = 'success';
            $this->closeLabModal();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error saving lab: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function confirmDeleteLab($id): void
    {
        $lab = Lab::findOrFail($id);

        $this->deleteType = 'lab';
        $this->deleteId = $id;
        $this->deleteDetails = [
            'name' => $lab->name,
            'code' => $lab->code,
            'start_sample_no' => $lab->start_sample_no,
            'address' => $lab->address,
            'email' => $lab->email,
            'phone1' => $lab->phone1,
            'is_external' => $lab->is_external ? 'External' : 'Internal',
            'active' => $lab->active ? 'Active' : 'Inactive',
        ];
        
        $this->showDeleteModal = true;
    }

    public function deleteLab(): void
    {
        try {
            if ($this->deleteType === 'lab_section') {
                $section = LabSection::findOrFail($this->deleteId);
                $labId = (string) $section->lab_id;

                $section->delete();

                if ($this->editingLabSection === $this->deleteId) {
                    $this->editingLabSection = null;
                    $this->closeLabSectionModal();
                    $this->initializeLabSectionForm($labId);
                }

                $this->message = 'Lab section deleted successfully!';
            } else {
                Lab::findOrFail($this->deleteId)->delete();
                $this->message = 'Lab deleted successfully!';
            }

            $this->messageType = 'success';
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->message = 'Error deleting ' . str_replace('_', ' ', $this->deleteType) . ': ' . $e->getMessage();
            $this->messageType = 'error';
            $this->closeDeleteModal();
        }
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteType = 'lab';
        $this->deleteDetails = [];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->zoneFilter = '';
        $this->directorateFilter = '';
        $this->zoneFilterSearch = '';
        $this->directorateFilterSearch = '';
        $this->showZoneFilterDropdown = false;
        $this->showDirectorateFilterDropdown = false;
        $this->resetPage();
    }

    public function selectZoneFilter(string $zoneId): void
    {
        $this->zoneFilter = $zoneId;
        $this->zoneFilterSearch = '';
        $this->showZoneFilterDropdown = false;

        // Reset directorate if selected zone no longer contains it.
        if ($this->directorateFilter !== '' && !Directorate::whereKey($this->directorateFilter)->where('zone_id', $zoneId)->exists()) {
            $this->directorateFilter = '';
            $this->directorateFilterSearch = '';
        }

        $this->resetPage();
    }

    public function clearZoneFilter(): void
    {
        $this->zoneFilter = '';
        $this->zoneFilterSearch = '';
        $this->showZoneFilterDropdown = false;
        $this->resetPage();
    }

    public function selectDirectorateFilter(string $directorateId): void
    {
        $this->directorateFilter = $directorateId;
        $this->directorateFilterSearch = '';
        $this->showDirectorateFilterDropdown = false;
        $this->resetPage();
    }

    public function clearDirectorateFilter(): void
    {
        $this->directorateFilter = '';
        $this->directorateFilterSearch = '';
        $this->showDirectorateFilterDropdown = false;
        $this->resetPage();
    }

    public function getFilteredZonesProperty()
    {
        $search = trim($this->zoneFilterSearch);

        return Zone::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('key', 'like', '%' . $search . '%')
                        ->orWhere('value', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('key')
            ->limit(50)
            ->get();
    }

    public function getFilteredDirectoratesProperty()
    {
        $search = trim($this->directorateFilterSearch);

        return Directorate::query()
            ->when($this->zoneFilter !== '', function ($query): void {
                $query->where('zone_id', $this->zoneFilter);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->limit(50)
            ->get();
    }

    public function getSelectedZoneFilterProperty(): ?Zone
    {
        if ($this->zoneFilter === '') {
            return null;
        }

        return Zone::find($this->zoneFilter);
    }

    public function getSelectedDirectorateFilterProperty(): ?Directorate
    {
        if ($this->directorateFilter === '') {
            return null;
        }

        return Directorate::find($this->directorateFilter);
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingZoneFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDirectorateFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.lab.lab-manager');
    }
}
