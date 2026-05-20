<?php

namespace App\Livewire\Equipment;

use Livewire\Component;
use Livewire\WithPagination;
use App\Company;
use App\Models\Equipments\Equipment;
use App\Models\Equipments\EquipmentAnnualMaintenance;
use App\Models\Equipments\EquipmentAnnualProgram;
use App\Models\Equipments\EquipmentPreventiveMaintenance;
use App\Models\Equipments\EquipmentPreventiveProgram;
use App\Models\Equipments\EquipmentMaintenanceRegister;
use App\Models\Equipments\EquipmentMaintenanceRegisterProgram;
use App\Models\Equipments\EquipmentReplacementPlan;
use App\Models\Equipments\EquipmentReplacementPlanItem;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Carbon\Carbon;

class EquipmentMaintenance extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $activeTab = 'annual';
    public $search = '';
    public $annualPerPage = 10;
    public $preventivePerPage = 10;
    public $registerPerPage = 10;
    public $perPageOptions = [10, 25, 50, 100];

    // Global maintenance period from company settings
    public $maintenancePeriodLabel = '';
    public $maintenanceMonths = [];   // ordered list of Carbon months in the period
    public $maintenanceQuarters = []; // [['label'=>'1st Quarter','months'=>[...Carbon...]], ...]

    // Bulk month setter for preventive tab
    public $bulkScheduledMonth = ''; // value: "YYYY-MM" (e.g. "2025-07")

    // Replacement Plan properties
    public $plans = [];
    public $activePlanId = '';
    public $showCreatePlanModal = false;
    public $showAddPlanItemModal = false;

    // Annual Program header properties
    public $annualPrograms = [];
    public $activeAnnualProgramId = '';
    public $showCreateAnnualProgramModal = false;
    public $newAnnualProgramName = '';
    public $newAnnualProgramDate = '';
    public $newAnnualProgramDescription = '';
    public $newAnnualProgramStatus = 'draft';

    // Preventive Program header properties
    public $preventivePrograms = [];
    public $activePreventiveProgramId = '';
    public $showCreatePreventiveProgramModal = false;
    public $newPreventiveProgramName = '';
    public $newPreventiveProgramDate = '';
    public $newPreventiveProgramDescription = '';
    public $newPreventiveProgramStatus = 'draft';

    // Maintenance Register header properties
    public $registerPrograms = [];
    public $activeRegisterProgramId = '';
    public $showCreateRegisterProgramModal = false;
    public $newRegisterProgramName = '';
    public $newRegisterProgramDate = '';
    public $newRegisterProgramDescription = '';
    public $newRegisterProgramStatus = 'draft';

    // Annual Program item modal fields
    public $showAddAnnualItemModal = false;
    public $editingAnnualItemId = null;
    public $annualEquipmentId = '';
    public $annualEquipmentName = '';
    public $annualEquipmentSearch = '';
    public $annualEquipmentSearchResults = [];
    public $annualServicedDate = '';
    public $annualRecordStatus = '';
    public $annualNextService = '';
    public $annualRemark = '';

    // Preventive Program item modal fields
    public $showAddPreventiveItemModal = false;
    public $editingPreventiveItemId = null;
    public $preventiveEquipmentId = '';
    public $preventiveEquipmentName = '';
    public $preventiveEquipmentSearch = '';
    public $preventiveEquipmentSearchResults = [];
    public $preventiveScheduledMonth = '';
    public $preventiveIsServiced = false;
    public $preventiveServicedDate = '';
    public $preventiveNotes = '';

    // Register Program item modal fields
    public $showAddRegisterItemModal = false;
    public $editingRegisterItemId = null;
    public $registerEquipmentId = '';
    public $registerEquipmentName = '';
    public $registerEquipmentSearch = '';
    public $registerEquipmentSearchResults = [];
    public $registerYear = '';
    public $registerServiceProvider = '';
    public $registerServiceType = '';
    public $registerCostUsd = '';
    public $registerCostTzs = '';

    // Create plan fields
    public $newPlanName = '';
    public $newPlanStartYear = '';
    public $newPlanEndYear = '';

    // Plan Item fields
    public $editingItemId = null;
    public $selectedEquipmentId = '';
    public $planItemName = '';
    public $planItemYear = '';
    public $planItemLocation = '';
    public $planItemRemark = '';

    // Searchable dropdown for equipment
    public $equipmentSearch = '';
    public $equipmentSearchResults = [];

    // System Zones dropdown list
    public $zones = [];

    protected $queryString = [
        'activeTab' => ['except' => 'annual'],
        'search'    => ['except' => ''],
    ];

    public function mount()
    {
        $this->authorizeAction('equipment.maintenance.view');
        $this->loadAnnualPrograms();
        $this->loadPreventivePrograms();
        $this->loadRegisterPrograms();
        $this->loadPlans();
        $this->zones = \App\Zone::orderBy('value')->get();
        $this->loadMaintenancePeriod();
    }

    public function updatedSearch(): void
    {
        $this->resetPaginationForActiveTab();
    }

    public function updatedActiveTab(): void
    {
        $this->resetPaginationForActiveTab();
    }

    public function updatedAnnualPerPage(): void
    {
        $this->resetPage('annualPage');
    }

    public function updatedPreventivePerPage(): void
    {
        $this->resetPage('preventivePage');
    }

    public function updatedRegisterPerPage(): void
    {
        $this->resetPage('registerPage');
    }

    private function resetPaginationForActiveTab(): void
    {
        if ($this->activeTab === 'annual') {
            $this->resetPage('annualPage');
            return;
        }

        if ($this->activeTab === 'preventive') {
            $this->resetPage('preventivePage');
            return;
        }

        if ($this->activeTab === 'register') {
            $this->resetPage('registerPage');
        }
    }

    // ─── Period Loading ───────────────────────────────────────────────────────

    private function loadMaintenancePeriod(): void
    {
        $company = Company::where('active', 1)->first();

        // Use current year/month as fallback
        $startY = date('Y');
        $startM = 1;

        if ($company && $company->maintenance_start_year) {
            $startY = (int) $company->maintenance_start_year;
            if ($company->maintenance_start_month) {
                $startM = (int) $company->maintenance_start_month;
            }
        }

        $start = Carbon::create($startY, $startM, 1);
        $end   = $start->copy()->addMonths(11); // Exactly 12 months total

        $this->maintenancePeriodLabel = $start->format('M Y') . ' – ' . $end->format('M Y');

        // Build ordered list of exactly 12 months
        $months = [];
        $cursor = $start->copy();
        for ($i = 0; $i < 12; $i++) {
            $months[] = $cursor->copy();
            $cursor->addMonth();
        }
        $this->maintenanceMonths = $months;

        // Split into exactly 4 quarters of 3 months each
        $this->maintenanceQuarters = [];
        $quarterLabels = ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];
        foreach (array_chunk($months, 3) as $i => $chunk) {
            $this->maintenanceQuarters[] = [
                'label'  => $quarterLabels[$i],
                'months' => $chunk,
            ];
        }
    }

    // ─── Equipment Query ─────────────────────────────────────────────────────

    private function equipmentsQuery()
    {
        $query = Equipment::with([
            'assetLocation.lab.zone',
            'annualMaintenances' => function ($q) {
                if ($this->activeAnnualProgramId) {
                    $q->where('equipment_annual_program_id', $this->activeAnnualProgramId);
                }
                $q->orderBy('serviced_date', 'desc');
            },
            'preventiveMaintenances' => function ($q) {
                if ($this->activePreventiveProgramId) {
                    $q->where('equipment_preventive_program_id', $this->activePreventiveProgramId);
                }
                $q->orderBy('updated_at', 'desc');
            },
            'maintenanceRegisters'  => function ($q) {
                if ($this->activeRegisterProgramId) {
                    $q->where('equipment_maintenance_register_program_id', $this->activeRegisterProgramId);
                }
                $q->orderBy('year', 'desc');
            },
        ]);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('serial_number', 'ilike', '%' . $this->search . '%');
            });
        }

        return $query;
    }

    private function getPaginatedEquipments()
    {
        if ($this->activeTab === 'preventive') {
            return $this->equipmentsQuery()->paginate((int) $this->preventivePerPage, ['*'], 'preventivePage');
        }

        if ($this->activeTab === 'register') {
            return $this->equipmentsQuery()->paginate((int) $this->registerPerPage, ['*'], 'registerPage');
        }

        return $this->equipmentsQuery()->paginate((int) $this->annualPerPage, ['*'], 'annualPage');
    }

    private function getExportEquipments()
    {
        return $this->equipmentsQuery()->get();
    }

    // ─── Preventive Maintenance – month scheduling ────────────────────────────

    /**
     * Set or update the scheduled_month for ONE equipment.
     * $monthKey = "YYYY-MM" string derived from the month Carbon in the grid.
     */
    public function setScheduledMonth(string $equipmentId, string $monthKey): void
    {
        $this->authorizeAction('equipment.maintenance.edit');

        if (!$this->activePreventiveProgramId) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Please create/select a preventive program first.']);
            return;
        }

        // monthKey = "2025-07"
        [$year, $month] = explode('-', $monthKey);
        $monthInt = (int) $month;

        // Find existing record or create new one
        $record = EquipmentPreventiveMaintenance::where('equipment_id', $equipmentId)
            ->where('equipment_preventive_program_id', $this->activePreventiveProgramId)
            ->first();

        if ($record) {
            // If clicking the already-scheduled month, unschedule it
            if ($record->scheduled_month === $monthInt && !$record->is_serviced) {
                $record->scheduled_month = null;
            } else {
                $record->scheduled_month = $monthInt;
                // Reset serviced when rescheduling
                $record->is_serviced   = false;
                $record->serviced_date = null;
            }
            $record->save();
        } else {
            EquipmentPreventiveMaintenance::create([
                'equipment_id'    => $equipmentId,
                'equipment_preventive_program_id' => $this->activePreventiveProgramId,
                'scheduled_month' => $monthInt,
                'is_serviced'     => false,
            ]);
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Maintenance schedule updated.']);
    }

    /**
     * Apply bulkScheduledMonth to all currently loaded equipments.
     */
    public function applyBulkScheduledMonth(): void
    {
        $this->authorizeAction('equipment.maintenance.edit');

        if (!$this->activePreventiveProgramId) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Please create/select a preventive program first.']);
            return;
        }

        if (!$this->bulkScheduledMonth) {
            return;
        }

        [, $month] = explode('-', $this->bulkScheduledMonth);
        $monthInt = (int) $month;

        $equipments = $this->equipmentsQuery()->get();

        foreach ($equipments as $equipment) {
            $record = EquipmentPreventiveMaintenance::where('equipment_id', $equipment->id)
                ->where('equipment_preventive_program_id', $this->activePreventiveProgramId)
                ->first();
            if ($record) {
                if (!$record->is_serviced) {
                    $record->scheduled_month = $monthInt;
                    $record->save();
                }
            } else {
                EquipmentPreventiveMaintenance::create([
                    'equipment_id'    => $equipment->id,
                    'equipment_preventive_program_id' => $this->activePreventiveProgramId,
                    'scheduled_month' => $monthInt,
                    'is_serviced'     => false,
                ]);
            }
        }

        $this->bulkScheduledMonth = '';
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Bulk maintenance schedule applied to all equipment.']);
    }

    /**
     * Mark an equipment's preventive maintenance as serviced.
     */
    public function markServiced(string $equipmentId): void
    {
        $this->authorizeAction('equipment.maintenance.edit');

        $record = EquipmentPreventiveMaintenance::where('equipment_id', $equipmentId)
            ->where('equipment_preventive_program_id', $this->activePreventiveProgramId)
            ->first();
        if ($record && $record->scheduled_month) {
            $record->is_serviced   = true;
            $record->serviced_date = now()->toDateString();
            $record->save();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Equipment marked as serviced/maintained.']);
        }
    }

    // ─── Autocomplete ─────────────────────────────────────────────────────────

    public function updatedEquipmentSearch()
    {
        if (strlen($this->equipmentSearch) >= 2) {
            $this->equipmentSearchResults = Equipment::where('name', 'ilike', '%' . $this->equipmentSearch . '%')
                ->orWhere('serial_number', 'ilike', '%' . $this->equipmentSearch . '%')
                ->take(10)->get();
        } else {
            $this->equipmentSearchResults = [];
        }
    }

    public function updatedAnnualEquipmentSearch(): void
    {
        if (strlen($this->annualEquipmentSearch) < 2) {
            $this->annualEquipmentSearchResults = [];
            return;
        }

        $this->annualEquipmentSearchResults = Equipment::where('name', 'ilike', '%' . $this->annualEquipmentSearch . '%')
            ->orWhere('serial_number', 'ilike', '%' . $this->annualEquipmentSearch . '%')
            ->take(10)
            ->get();
    }

    public function updatedPreventiveEquipmentSearch(): void
    {
        if (strlen($this->preventiveEquipmentSearch) < 2) {
            $this->preventiveEquipmentSearchResults = [];
            return;
        }

        $this->preventiveEquipmentSearchResults = Equipment::where('name', 'ilike', '%' . $this->preventiveEquipmentSearch . '%')
            ->orWhere('serial_number', 'ilike', '%' . $this->preventiveEquipmentSearch . '%')
            ->take(10)
            ->get();
    }

    public function updatedRegisterEquipmentSearch(): void
    {
        if (strlen($this->registerEquipmentSearch) < 2) {
            $this->registerEquipmentSearchResults = [];
            return;
        }

        $this->registerEquipmentSearchResults = Equipment::where('name', 'ilike', '%' . $this->registerEquipmentSearch . '%')
            ->orWhere('serial_number', 'ilike', '%' . $this->registerEquipmentSearch . '%')
            ->take(10)
            ->get();
    }

    public function selectEquipment($id, $name)
    {
        $this->selectedEquipmentId = $id;
        $this->planItemName        = $name;
        $this->equipmentSearch     = $name;
        $this->equipmentSearchResults = [];

        $eq = Equipment::with('assetLocation.lab.zone')->find($id);
        if ($eq) {
            $zone = $eq->assetLocation?->lab?->zone;
            if ($zone) {
                $this->planItemLocation = $zone->value ?: $zone->name ?: $zone->key ?: '';
            }
        }
    }

    public function selectAnnualEquipment($id, $name): void
    {
        $this->annualEquipmentId = $id;
        $this->annualEquipmentName = $name;
        $this->annualEquipmentSearch = $name;
        $this->annualEquipmentSearchResults = [];
    }

    public function selectPreventiveEquipment($id, $name): void
    {
        $this->preventiveEquipmentId = $id;
        $this->preventiveEquipmentName = $name;
        $this->preventiveEquipmentSearch = $name;
        $this->preventiveEquipmentSearchResults = [];
    }

    public function selectRegisterEquipment($id, $name): void
    {
        $this->registerEquipmentId = $id;
        $this->registerEquipmentName = $name;
        $this->registerEquipmentSearch = $name;
        $this->registerEquipmentSearchResults = [];
    }

    public function openAddAnnualItemModal(?string $id = null): void
    {
        $this->authorizeAction($id ? 'equipment.maintenance.edit' : 'equipment.maintenance.add');

        if (!$this->activeAnnualProgramId) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Create/select an annual program first.']);
            return;
        }

        $this->editingAnnualItemId = null;
        $this->annualEquipmentId = '';
        $this->annualEquipmentName = '';
        $this->annualEquipmentSearch = '';
        $this->annualEquipmentSearchResults = [];
        $this->annualServicedDate = '';
        $this->annualRecordStatus = '';
        $this->annualNextService = '';
        $this->annualRemark = '';

        if ($id) {
            $record = EquipmentAnnualMaintenance::find($id);
            if ($record) {
                $this->editingAnnualItemId = $record->id;
                $this->annualEquipmentId = $record->equipment_id;
                $this->annualEquipmentName = optional($record->equipment)->name ?? '';
                $this->annualEquipmentSearch = $this->annualEquipmentName;
                $this->annualServicedDate = $record->serviced_date ? $record->serviced_date->format('Y-m-d') : '';
                $this->annualRecordStatus = (string) ($record->status ?? '');
                $this->annualNextService = $record->next_service ? $record->next_service->format('Y-m-d') : '';
                $this->annualRemark = (string) ($record->remark ?? '');
            }
        }

        $this->showAddAnnualItemModal = true;
    }

    public function saveAnnualItem(): void
    {
        $this->authorizeAction($this->editingAnnualItemId ? 'equipment.maintenance.edit' : 'equipment.maintenance.add');

        if (!$this->activeAnnualProgramId) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Create/select an annual program first.']);
            return;
        }

        $this->validate([
            'annualEquipmentId' => 'required|uuid',
            'annualServicedDate' => 'nullable|date',
            'annualRecordStatus' => 'nullable|string|max:255',
            'annualNextService' => 'nullable|date',
            'annualRemark' => 'nullable|string',
        ]);

        $payload = [
            'equipment_id' => $this->annualEquipmentId,
            'equipment_annual_program_id' => $this->activeAnnualProgramId,
            'serviced_date' => $this->annualServicedDate ?: null,
            'status' => $this->annualRecordStatus ?: null,
            'next_service' => $this->annualNextService ?: null,
            'remark' => $this->annualRemark ?: null,
        ];

        if ($this->editingAnnualItemId) {
            EquipmentAnnualMaintenance::whereKey($this->editingAnnualItemId)->update($payload);
        } else {
            EquipmentAnnualMaintenance::create($payload);
        }

        $this->showAddAnnualItemModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Annual program item saved.']);
    }

    public function deleteAnnualItem(string $id): void
    {
        $this->authorizeAction('equipment.maintenance.delete');
        EquipmentAnnualMaintenance::whereKey($id)->delete();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Annual item removed.']);
    }

    public function openAddPreventiveItemModal(?string $id = null): void
    {
        $this->authorizeAction($id ? 'equipment.maintenance.edit' : 'equipment.maintenance.add');

        if (!$this->activePreventiveProgramId) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Create/select a preventive program first.']);
            return;
        }

        $this->editingPreventiveItemId = null;
        $this->preventiveEquipmentId = '';
        $this->preventiveEquipmentName = '';
        $this->preventiveEquipmentSearch = '';
        $this->preventiveEquipmentSearchResults = [];
        $this->preventiveScheduledMonth = '';
        $this->preventiveIsServiced = false;
        $this->preventiveServicedDate = '';
        $this->preventiveNotes = '';

        if ($id) {
            $record = EquipmentPreventiveMaintenance::find($id);
            if ($record) {
                $this->editingPreventiveItemId = $record->id;
                $this->preventiveEquipmentId = $record->equipment_id;
                $this->preventiveEquipmentName = optional($record->equipment)->name ?? '';
                $this->preventiveEquipmentSearch = $this->preventiveEquipmentName;
                $this->preventiveScheduledMonth = $record->scheduled_month ? str_pad((string) $record->scheduled_month, 2, '0', STR_PAD_LEFT) : '';
                $this->preventiveIsServiced = (bool) $record->is_serviced;
                $this->preventiveServicedDate = $record->serviced_date ? $record->serviced_date->format('Y-m-d') : '';
                $this->preventiveNotes = (string) ($record->notes ?? '');
            }
        }

        $this->showAddPreventiveItemModal = true;
    }

    public function savePreventiveItem(): void
    {
        $this->authorizeAction($this->editingPreventiveItemId ? 'equipment.maintenance.edit' : 'equipment.maintenance.add');

        if (!$this->activePreventiveProgramId) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Create/select a preventive program first.']);
            return;
        }

        $this->validate([
            'preventiveEquipmentId' => 'required|uuid',
            'preventiveScheduledMonth' => 'required|integer|min:1|max:12',
            'preventiveServicedDate' => 'nullable|date',
            'preventiveNotes' => 'nullable|string|max:1000',
        ]);

        $payload = [
            'equipment_id' => $this->preventiveEquipmentId,
            'equipment_preventive_program_id' => $this->activePreventiveProgramId,
            'scheduled_month' => (int) $this->preventiveScheduledMonth,
            'is_serviced' => (bool) $this->preventiveIsServiced,
            'serviced_date' => $this->preventiveIsServiced ? ($this->preventiveServicedDate ?: now()->toDateString()) : null,
            'notes' => $this->preventiveNotes ?: null,
        ];

        if ($this->editingPreventiveItemId) {
            EquipmentPreventiveMaintenance::whereKey($this->editingPreventiveItemId)->update($payload);
        } else {
            EquipmentPreventiveMaintenance::create($payload);
        }

        $this->showAddPreventiveItemModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Preventive program item saved.']);
    }

    public function deletePreventiveItem(string $id): void
    {
        $this->authorizeAction('equipment.maintenance.delete');
        EquipmentPreventiveMaintenance::whereKey($id)->delete();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Preventive item removed.']);
    }

    public function openAddRegisterItemModal(?string $id = null): void
    {
        $this->authorizeAction($id ? 'equipment.maintenance.edit' : 'equipment.maintenance.add');

        if (!$this->activeRegisterProgramId) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Create/select a maintenance register first.']);
            return;
        }

        $this->editingRegisterItemId = null;
        $this->registerEquipmentId = '';
        $this->registerEquipmentName = '';
        $this->registerEquipmentSearch = '';
        $this->registerEquipmentSearchResults = [];
        $this->registerYear = '';
        $this->registerServiceProvider = '';
        $this->registerServiceType = '';
        $this->registerCostUsd = '';
        $this->registerCostTzs = '';

        if ($id) {
            $record = EquipmentMaintenanceRegister::find($id);
            if ($record) {
                $this->editingRegisterItemId = $record->id;
                $this->registerEquipmentId = $record->equipment_id;
                $this->registerEquipmentName = optional($record->equipment)->name ?? '';
                $this->registerEquipmentSearch = $this->registerEquipmentName;
                $this->registerYear = (string) ($record->year ?? '');
                $this->registerServiceProvider = (string) ($record->service_provider ?? '');
                $this->registerServiceType = (string) ($record->service_type ?? '');
                $this->registerCostUsd = $record->cost_usd !== null ? (string) $record->cost_usd : '';
                $this->registerCostTzs = $record->cost_tzs !== null ? (string) $record->cost_tzs : '';
            }
        }

        $this->showAddRegisterItemModal = true;
    }

    public function saveRegisterItem(): void
    {
        $this->authorizeAction($this->editingRegisterItemId ? 'equipment.maintenance.edit' : 'equipment.maintenance.add');

        if (!$this->activeRegisterProgramId) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Create/select a maintenance register first.']);
            return;
        }

        $this->validate([
            'registerEquipmentId' => 'required|uuid',
            'registerYear' => 'nullable|string|max:255',
            'registerServiceProvider' => 'nullable|string|max:255',
            'registerServiceType' => 'nullable|string|max:255',
            'registerCostUsd' => 'nullable|numeric|min:0',
            'registerCostTzs' => 'nullable|numeric|min:0',
        ]);

        $payload = [
            'equipment_id' => $this->registerEquipmentId,
            'equipment_maintenance_register_program_id' => $this->activeRegisterProgramId,
            'year' => $this->registerYear ?: null,
            'service_provider' => $this->registerServiceProvider ?: null,
            'service_type' => $this->registerServiceType ?: null,
            'cost_usd' => $this->registerCostUsd !== '' ? $this->registerCostUsd : null,
            'cost_tzs' => $this->registerCostTzs !== '' ? $this->registerCostTzs : null,
        ];

        if ($this->editingRegisterItemId) {
            EquipmentMaintenanceRegister::whereKey($this->editingRegisterItemId)->update($payload);
        } else {
            EquipmentMaintenanceRegister::create($payload);
        }

        $this->showAddRegisterItemModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Maintenance register item saved.']);
    }

    public function deleteRegisterItem(string $id): void
    {
        $this->authorizeAction('equipment.maintenance.delete');
        EquipmentMaintenanceRegister::whereKey($id)->delete();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Register item removed.']);
    }

    // ─── Replacement Plan ────────────────────────────────────────────────────

    public function loadAnnualPrograms(): void
    {
        $this->annualPrograms = EquipmentAnnualProgram::orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('program_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($this->annualPrograms->isNotEmpty() && !$this->activeAnnualProgramId) {
            $this->activeAnnualProgramId = optional($this->annualPrograms->firstWhere('status', 'active'))->id
                ?: $this->annualPrograms->first()->id;
        }
    }

    public function loadPreventivePrograms(): void
    {
        $this->preventivePrograms = EquipmentPreventiveProgram::orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('program_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($this->preventivePrograms->isNotEmpty() && !$this->activePreventiveProgramId) {
            $this->activePreventiveProgramId = optional($this->preventivePrograms->firstWhere('status', 'active'))->id
                ?: $this->preventivePrograms->first()->id;
        }
    }

    public function loadRegisterPrograms(): void
    {
        $this->registerPrograms = EquipmentMaintenanceRegisterProgram::orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('program_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($this->registerPrograms->isNotEmpty() && !$this->activeRegisterProgramId) {
            $this->activeRegisterProgramId = optional($this->registerPrograms->firstWhere('status', 'active'))->id
                ?: $this->registerPrograms->first()->id;
        }
    }

    public function createAnnualProgram(): void
    {
        $this->authorizeAction('equipment.maintenance.add');

        $this->validate([
            'newAnnualProgramName' => 'required|string|max:255',
            'newAnnualProgramDate' => 'required|date',
            'newAnnualProgramDescription' => 'nullable|string|max:2000',
            'newAnnualProgramStatus' => 'required|in:active,draft',
        ]);

        if ($this->newAnnualProgramStatus === 'active') {
            EquipmentAnnualProgram::query()->update(['status' => 'draft']);
        }

        $program = EquipmentAnnualProgram::create([
            'name' => $this->newAnnualProgramName,
            'program_date' => $this->newAnnualProgramDate,
            'description' => $this->newAnnualProgramDescription,
            'status' => $this->newAnnualProgramStatus,
        ]);

        $this->newAnnualProgramName = '';
        $this->newAnnualProgramDate = '';
        $this->newAnnualProgramDescription = '';
        $this->newAnnualProgramStatus = 'draft';
        $this->showCreateAnnualProgramModal = false;
        $this->activeAnnualProgramId = $program->id;
        $this->loadAnnualPrograms();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Annual program created successfully.']);
    }

    public function createPreventiveProgram(): void
    {
        $this->authorizeAction('equipment.maintenance.add');

        $this->validate([
            'newPreventiveProgramName' => 'required|string|max:255',
            'newPreventiveProgramDate' => 'required|date',
            'newPreventiveProgramDescription' => 'nullable|string|max:2000',
            'newPreventiveProgramStatus' => 'required|in:active,draft',
        ]);

        if ($this->newPreventiveProgramStatus === 'active') {
            EquipmentPreventiveProgram::query()->update(['status' => 'draft']);
        }

        $program = EquipmentPreventiveProgram::create([
            'name' => $this->newPreventiveProgramName,
            'program_date' => $this->newPreventiveProgramDate,
            'description' => $this->newPreventiveProgramDescription,
            'status' => $this->newPreventiveProgramStatus,
        ]);

        $this->newPreventiveProgramName = '';
        $this->newPreventiveProgramDate = '';
        $this->newPreventiveProgramDescription = '';
        $this->newPreventiveProgramStatus = 'draft';
        $this->showCreatePreventiveProgramModal = false;
        $this->activePreventiveProgramId = $program->id;
        $this->loadPreventivePrograms();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Preventive program created successfully.']);
    }

    public function createRegisterProgram(): void
    {
        $this->authorizeAction('equipment.maintenance.add');

        $this->validate([
            'newRegisterProgramName' => 'required|string|max:255',
            'newRegisterProgramDate' => 'required|date',
            'newRegisterProgramDescription' => 'nullable|string|max:2000',
            'newRegisterProgramStatus' => 'required|in:active,draft',
        ]);

        if ($this->newRegisterProgramStatus === 'active') {
            EquipmentMaintenanceRegisterProgram::query()->update(['status' => 'draft']);
        }

        $program = EquipmentMaintenanceRegisterProgram::create([
            'name' => $this->newRegisterProgramName,
            'program_date' => $this->newRegisterProgramDate,
            'description' => $this->newRegisterProgramDescription,
            'status' => $this->newRegisterProgramStatus,
        ]);

        $this->newRegisterProgramName = '';
        $this->newRegisterProgramDate = '';
        $this->newRegisterProgramDescription = '';
        $this->newRegisterProgramStatus = 'draft';
        $this->showCreateRegisterProgramModal = false;
        $this->activeRegisterProgramId = $program->id;
        $this->loadRegisterPrograms();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Maintenance register program created successfully.']);
    }

    public function loadPlans()
    {
        $this->plans = EquipmentReplacementPlan::orderBy('created_at', 'desc')->get();
        if ($this->plans->isNotEmpty() && !$this->activePlanId) {
            $this->activePlanId = $this->plans->first()->id;
        }
    }

    public function createPlan()
    {
        $this->authorizeAction('equipment.maintenance.add');
        $this->validate([
            'newPlanName'      => 'required|string|max:255',
            'newPlanStartYear' => 'required|integer|min:2000|max:2100',
            'newPlanEndYear'   => 'required|integer|min:2000|max:2100|gte:newPlanStartYear',
        ]);
        $plan = EquipmentReplacementPlan::create([
            'name'       => $this->newPlanName,
            'start_year' => $this->newPlanStartYear,
            'end_year'   => $this->newPlanEndYear,
        ]);
        $this->newPlanName = $this->newPlanStartYear = $this->newPlanEndYear = '';
        $this->showCreatePlanModal = false;
        $this->activePlanId = $plan->id;
        $this->loadPlans();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Equipment Replacement Plan created successfully!']);
    }

    public function openAddPlanItemModal()
    {
        $this->authorizeAction('equipment.maintenance.add');
        $this->resetPlanItemForm();
        if (auth()->check() && auth()->user()->zone) {
            $z = auth()->user()->zone;
            $this->planItemLocation = $z->value ?: $z->name ?: $z->key ?: '';
        }
        $this->showAddPlanItemModal = true;
    }

    public function resetPlanItemForm()
    {
        $this->editingItemId = $this->selectedEquipmentId = null;
        $this->planItemName = $this->equipmentSearch = $this->planItemYear = '';
        $this->planItemLocation = $this->planItemRemark = '';
        $this->equipmentSearchResults = [];
    }

    public function addPlanItem()
    {
        $this->authorizeAction($this->editingItemId ? 'equipment.maintenance.edit' : 'equipment.maintenance.add');
        $this->validate([
            'planItemName'     => 'required|string|max:255',
            'planItemYear'     => 'required|string',
            'planItemLocation' => 'nullable|string|max:255',
            'planItemRemark'   => 'nullable|string',
        ]);
        if ($this->editingItemId) {
            $item = EquipmentReplacementPlanItem::find($this->editingItemId);
            $item?->update([
                'equipment_id'   => $this->selectedEquipmentId ?: null,
                'equipment_name' => $this->planItemName,
                'scheduled_year' => $this->planItemYear,
                'location'       => $this->planItemLocation,
                'remark'         => $this->planItemRemark,
            ]);
        } else {
            EquipmentReplacementPlanItem::create([
                'equipment_replacement_plan_id' => $this->activePlanId,
                'equipment_id'   => $this->selectedEquipmentId ?: null,
                'equipment_name' => $this->planItemName,
                'scheduled_year' => $this->planItemYear,
                'location'       => $this->planItemLocation,
                'remark'         => $this->planItemRemark,
            ]);
        }
        $this->resetPlanItemForm();
        $this->showAddPlanItemModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Replacement plan item saved!']);
    }

    public function editPlanItem($itemId)
    {
        $this->authorizeAction('equipment.maintenance.edit');
        $item = EquipmentReplacementPlanItem::find($itemId);
        if ($item) {
            $this->editingItemId       = $item->id;
            $this->selectedEquipmentId = $item->equipment_id;
            $this->planItemName        = $item->equipment_name;
            $this->equipmentSearch     = $item->equipment_name;
            $this->planItemYear        = $item->scheduled_year;
            $this->planItemLocation    = $item->location;
            $this->planItemRemark      = $item->remark;
            $this->showAddPlanItemModal = true;
        }
    }

    public function deletePlanItem($itemId)
    {
        $this->authorizeAction('equipment.maintenance.delete');
        EquipmentReplacementPlanItem::find($itemId)?->delete();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Plan item removed.']);
    }

    // ─── Exports ─────────────────────────────────────────────────────────────

    public function exportAnnual()
    {
        $this->authorizeAction('equipment.maintenance.view');
        $equipments = $this->getExportEquipments();
        $data = [['GCLA ANNUAL MAINTENANCE PROGRAM']];
        if ($this->maintenancePeriodLabel) $data[] = ['Period: ' . $this->maintenancePeriodLabel];
        $data[] = [];
        $data[] = ['Equipment Name', 'Serial Number', 'Location (Zones)', 'Serviced Date', 'Status', 'Next Service', 'Remark'];
        foreach ($equipments as $eq) {
            $l = $eq->annualMaintenances->first();
            $data[] = [
                $eq->name, $eq->serial_number ?? '—', $eq->zone_name,
                $l && $l->serviced_date ? $l->serviced_date->format('Y-m-d') : '—',
                $l->status ?? '—',
                $l && $l->next_service ? $l->next_service->format('Y-m-d') : '—',
                $l->remark ?? '—',
            ];
        }
        return Excel::download(new class($data) implements FromArray {
            protected $rows;
            public function __construct($r) { $this->rows = $r; }
            public function array(): array { return $this->rows; }
        }, 'GCLA_Annual_Maintenance_' . date('Ymd_His') . '.xlsx');
    }

    public function exportPreventive()
    {
        $this->authorizeAction('equipment.maintenance.view');
        $equipments = $this->getExportEquipments();
        $data = [['GCLA EQUIPMENT PREVENTIVE MAINTENANCE PROGRAM']];
        if ($this->maintenancePeriodLabel) $data[] = ['Period: ' . $this->maintenancePeriodLabel];
        $data[] = [];

        // Build header row with months
        $header = ['S/No', 'Equipment Name', 'Serial Number', 'Location (Zones)'];
        foreach ($this->maintenanceQuarters as $q) {
            $header[] = $q['label'];
            foreach ($q['months'] as $m) {
                $header[] = $m->format('M');
            }
        }
        $data[] = $header;

        $i = 1;
        foreach ($equipments as $eq) {
            $pm = $eq->preventiveMaintenances->first();
            $row = [$i++, $eq->name, $eq->serial_number ?? '—', $eq->zone_name];
            foreach ($this->maintenanceQuarters as $q) {
                $row[] = ''; // quarter label spacer
                foreach ($q['months'] as $m) {
                    if ($pm && $pm->scheduled_month === $m->month) {
                        $row[] = $pm->is_serviced ? 'Done' : 'Scheduled';
                    } else {
                        $row[] = '';
                    }
                }
            }
            $data[] = $row;
        }

        return Excel::download(new class($data) implements FromArray {
            protected $rows;
            public function __construct($r) { $this->rows = $r; }
            public function array(): array { return $this->rows; }
        }, 'GCLA_Preventive_Maintenance_' . date('Ymd_His') . '.xlsx');
    }

    public function exportRegister()
    {
        $this->authorizeAction('equipment.maintenance.view');
        $equipments = $this->getExportEquipments();
        $data = [['GCLA DSM - EQUIPMENT MAINTENANCE REGISTER' . ($this->maintenancePeriodLabel ? ' FOR ' . $this->maintenancePeriodLabel : '')]];
        $data[] = [];
        $data[] = ['Equipment Name', 'Service Provider', 'Type of Service', 'Cost (USD)', 'Cost (TZS)'];
        $totalUsd = $totalTzs = 0;
        foreach ($equipments as $eq) {
            $l = $eq->maintenanceRegisters->first();
            $usd = $l ? (float) $l->cost_usd : 0;
            $tzs = $l ? (float) $l->cost_tzs : 0;
            $totalUsd += $usd; $totalTzs += $tzs;
            $data[] = [$eq->name, $l->service_provider ?? '—', $l->service_type ?? '—', number_format($usd, 2), number_format($tzs, 2)];
        }
        $data[] = [];
        $data[] = ['Total', '', '', number_format($totalUsd, 2), number_format($totalTzs, 2)];
        return Excel::download(new class($data) implements FromArray {
            protected $rows;
            public function __construct($r) { $this->rows = $r; }
            public function array(): array { return $this->rows; }
        }, 'GCLA_Maintenance_Register_' . date('Ymd_His') . '.xlsx');
    }

    public function exportReplacement()
    {
        $this->authorizeAction('equipment.maintenance.view');
        if (!$this->activePlanId) return;
        $plan = EquipmentReplacementPlan::with('items')->find($this->activePlanId);
        if (!$plan) return;
        $years = $plan->getYearsRange();
        $data  = [[strtoupper($plan->name)]];
        if ($this->maintenancePeriodLabel) $data[] = ['Period: ' . $this->maintenancePeriodLabel];
        $data[] = [];
        $header = ['Equipment Name', 'Location (Zone)', ...$years, 'Remark'];
        $data[] = $header;
        foreach ($plan->items as $item) {
            $row = [$item->equipment_name, $item->location ?? '—'];
            foreach ($years as $year) { $row[] = ($item->scheduled_year === $year) ? '✓' : '—'; }
            $row[] = $item->remark ?? '—';
            $data[] = $row;
        }
        return Excel::download(new class($data) implements FromArray {
            protected $rows;
            public function __construct($r) { $this->rows = $r; }
            public function array(): array { return $this->rows; }
        }, str_replace(' ', '_', $plan->name) . '_' . date('Ymd_His') . '.xlsx');
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        $equipments = $this->getPaginatedEquipments();
        $equipmentRows = $this->equipmentsQuery()->get();
        $totalUsd = $totalTzs = 0;
        foreach ($equipmentRows as $eq) {
            $reg = $eq->maintenanceRegisters->first();
            if ($reg) { $totalUsd += (float) $reg->cost_usd; $totalTzs += (float) $reg->cost_tzs; }
        }

        $activePlan = $planItems = $planYears = null;
        if ($this->activePlanId) {
            $activePlan = EquipmentReplacementPlan::with(['items.equipment'])->find($this->activePlanId);
            if ($activePlan) { $planItems = $activePlan->items; $planYears = $activePlan->getYearsRange(); }
        }

        $activeAnnualProgram = null;
        if ($this->activeAnnualProgramId) {
            $activeAnnualProgram = EquipmentAnnualProgram::withCount('maintenances')
                ->find($this->activeAnnualProgramId);
        }

        $activePreventiveProgram = null;
        if ($this->activePreventiveProgramId) {
            $activePreventiveProgram = EquipmentPreventiveProgram::withCount('maintenances')
                ->find($this->activePreventiveProgramId);
        }

        $activeRegisterProgram = null;
        if ($this->activeRegisterProgramId) {
            $activeRegisterProgram = EquipmentMaintenanceRegisterProgram::withCount('registers')
                ->find($this->activeRegisterProgramId);
        }

        return view('livewire.equipment.equipment-maintenance', compact(
            'equipments',
            'totalUsd',
            'totalTzs',
            'activePlan',
            'planItems',
            'planYears',
            'activeAnnualProgram',
            'activePreventiveProgram',
            'activeRegisterProgram'
        ));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function authorizeAction(string $permission): void
    {
        $user = auth()->user();
        if (!$user) abort(403);
        if ((method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) || $user->can($permission)) return;
        abort(403, 'Unauthorized.');
    }
}
