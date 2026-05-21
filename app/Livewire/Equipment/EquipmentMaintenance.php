<?php

namespace App\Livewire\Equipment;

use Livewire\Component;
use Livewire\WithPagination;
use App\Company;
use App\Models\Equipments\Equipment;
use App\Models\Equipments\EquipmentAnnualMaintenance;
use App\Models\Equipments\EquipmentPreventiveMaintenance;
use App\Models\Equipments\EquipmentMaintenanceRegister;
use App\Models\Equipments\EquipmentMaintenanceProgram;
use App\Models\Equipments\EquipmentReplacementPlan;
use App\Models\Equipments\EquipmentReplacementPlanItem;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Carbon\Carbon;
use Livewire\Attributes\Computed;

class EquipmentMaintenance extends Component
{
    use WithPagination;

    public $activeTab = 'annual';
    public $search = '';
    public int $perPage = 10;
    public array $perPageOptions = [10, 25, 50, 100];

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

    // Program headers for Annual / Preventive / Register tabs
    public $annualPrograms = [];
    public $preventivePrograms = [];
    public $registerPrograms = [];
    public $activeAnnualProgramId = '';
    public $activePreventiveProgramId = '';
    public $activeRegisterProgramId = '';

    public bool $showCreateProgramModal = false;
    public $newProgramName = '';
    public $newProgramDate = '';
    public $newProgramDescription = '';
    public $newProgramStatus = 'draft';

    protected $queryString = [
        'activeTab' => ['except' => 'annual'],
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount()
    {
        $this->authorizeAction('equipment.maintenance.view');
        $this->loadPrograms();
        $this->loadPlans();
        $this->zones = \App\Zone::orderBy('value')->get();
        $this->loadMaintenancePeriod();
    }

    public function updatingSearch(): void
    {
        $this->resetPage('equipmentPage');
    }

    public function updatingPerPage(): void
    {
        $this->resetPage('equipmentPage');
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = max(10, (int) $value);
        $this->resetPage('equipmentPage');
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage('equipmentPage');
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
        $end = $start->copy()->addMonths(11); // Exactly 12 months total

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
                'label' => $quarterLabels[$i],
                'months' => $chunk,
            ];
        }
    }

    // ─── Equipment Query ─────────────────────────────────────────────────────

    #[Computed]
    public function equipments()
    {
        return $this->getEquipmentsBaseQuery()->paginate($this->perPage, ['*'], 'equipmentPage');
    }

    private function getEquipmentsBaseQuery()
    {
        $query = Equipment::query()->with([
            'assetLocation.lab.zone',
            'annualMaintenances' => fn($q) => $q->orderBy('serviced_date', 'desc'),
            'preventiveMaintenances',
            'maintenanceRegisters' => fn($q) => $q->orderBy('year', 'desc'),
        ]);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%' . $this->search . '%')
                    ->orWhere('serial_number', 'ilike', '%' . $this->search . '%');
            });
        }

        return $query;
    }

    private function allEquipments()
    {
        return $this->getEquipmentsBaseQuery()->get();
    }

    // ─── Program Headers (Annual / Preventive / Register) ──────────────────

    public function loadPrograms(): void
    {
        $this->annualPrograms = EquipmentMaintenanceProgram::where('type', 'annual')
            ->orderBy('created_at', 'desc')
            ->get();
        $this->preventivePrograms = EquipmentMaintenanceProgram::where('type', 'preventive')
            ->orderBy('created_at', 'desc')
            ->get();
        $this->registerPrograms = EquipmentMaintenanceProgram::where('type', 'register')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($this->annualPrograms->isNotEmpty() && !$this->activeAnnualProgramId) {
            $active = $this->annualPrograms->firstWhere('status', 'active');
            $this->activeAnnualProgramId = $active?->id ?? $this->annualPrograms->first()->id;
        }

        if ($this->preventivePrograms->isNotEmpty() && !$this->activePreventiveProgramId) {
            $active = $this->preventivePrograms->firstWhere('status', 'active');
            $this->activePreventiveProgramId = $active?->id ?? $this->preventivePrograms->first()->id;
        }

        if ($this->registerPrograms->isNotEmpty() && !$this->activeRegisterProgramId) {
            $active = $this->registerPrograms->firstWhere('status', 'active');
            $this->activeRegisterProgramId = $active?->id ?? $this->registerPrograms->first()->id;
        }
    }

    public function openCreateProgramModal(): void
    {
        $this->authorizeAction('equipment.maintenance.add');

        $this->newProgramName = '';
        $this->newProgramDate = now()->toDateString();
        $this->newProgramDescription = '';
        $this->newProgramStatus = 'draft';
        $this->showCreateProgramModal = true;
    }

    public function createCurrentProgram(): void
    {
        $this->authorizeAction('equipment.maintenance.add');

        if (!in_array($this->activeTab, ['annual', 'preventive', 'register'], true)) {
            return;
        }

        $this->validate([
            'newProgramName' => 'required|string|max:255',
            'newProgramDate' => 'required|date',
            'newProgramDescription' => 'nullable|string',
            'newProgramStatus' => 'required|in:active,draft',
        ]);

        if ($this->newProgramStatus === 'active') {
            EquipmentMaintenanceProgram::where('type', $this->activeTab)
                ->where('status', 'active')
                ->update(['status' => 'draft']);
        }

        $program = EquipmentMaintenanceProgram::create([
            'type' => $this->activeTab,
            'name' => $this->newProgramName,
            'program_date' => $this->newProgramDate,
            'description' => $this->newProgramDescription,
            'status' => $this->newProgramStatus,
        ]);

        if ($this->activeTab === 'annual') {
            $this->activeAnnualProgramId = $program->id;
        } elseif ($this->activeTab === 'preventive') {
            $this->activePreventiveProgramId = $program->id;
        } else {
            $this->activeRegisterProgramId = $program->id;
        }

        $this->showCreateProgramModal = false;
        $this->loadPrograms();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Program created successfully.']);
    }

    public function getCurrentProgramsProperty()
    {
        if ($this->activeTab === 'annual') {
            return $this->annualPrograms;
        }
        if ($this->activeTab === 'preventive') {
            return $this->preventivePrograms;
        }
        if ($this->activeTab === 'register') {
            return $this->registerPrograms;
        }

        return collect();
    }

    public function getCurrentProgramIdProperty(): ?string
    {
        if ($this->activeTab === 'annual') {
            return $this->activeAnnualProgramId ?: null;
        }
        if ($this->activeTab === 'preventive') {
            return $this->activePreventiveProgramId ?: null;
        }
        if ($this->activeTab === 'register') {
            return $this->activeRegisterProgramId ?: null;
        }

        return null;
    }

    public function getActiveMaintenanceProgramProperty()
    {
        $programId = $this->currentProgramId;
        if (!$programId) {
            return null;
        }

        return EquipmentMaintenanceProgram::find($programId);
    }

    // ─── Preventive Maintenance – month scheduling ────────────────────────────

    /**
     * Set or update the scheduled_month for ONE equipment.
     * $monthKey = "YYYY-MM" string derived from the month Carbon in the grid.
     */
    public function setScheduledMonth(string $equipmentId, string $monthKey): void
    {
        $this->authorizeAction('equipment.maintenance.edit');

        // monthKey = "2025-07"
        [$year, $month] = explode('-', $monthKey);
        $monthInt = (int) $month;

        // Find existing record or create new one
        $record = EquipmentPreventiveMaintenance::where('equipment_id', $equipmentId)->first();

        if ($record) {
            // If clicking the already-scheduled month, unschedule it
            if ($record->scheduled_month === $monthInt && !$record->is_serviced) {
                $record->scheduled_month = null;
            } else {
                $record->scheduled_month = $monthInt;
                // Reset serviced when rescheduling
                $record->is_serviced = false;
                $record->serviced_date = null;
            }
            $record->save();
        } else {
            EquipmentPreventiveMaintenance::create([
                'equipment_id' => $equipmentId,
                'scheduled_month' => $monthInt,
                'is_serviced' => false,
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

        if (!$this->bulkScheduledMonth) {
            return;
        }

        [, $month] = explode('-', $this->bulkScheduledMonth);
        $monthInt = (int) $month;

        foreach ($this->allEquipments() as $equipment) {
            $record = EquipmentPreventiveMaintenance::where('equipment_id', $equipment->id)->first();
            if ($record) {
                if (!$record->is_serviced) {
                    $record->scheduled_month = $monthInt;
                    $record->save();
                }
            } else {
                EquipmentPreventiveMaintenance::create([
                    'equipment_id' => $equipment->id,
                    'scheduled_month' => $monthInt,
                    'is_serviced' => false,
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

        $record = EquipmentPreventiveMaintenance::where('equipment_id', $equipmentId)->first();
        if ($record && $record->scheduled_month) {
            $record->is_serviced = true;
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

    public function selectEquipment($id, $name)
    {
        $this->selectedEquipmentId = $id;
        $this->planItemName = $name;
        $this->equipmentSearch = $name;
        $this->equipmentSearchResults = [];

        $eq = Equipment::with('assetLocation.lab.zone')->find($id);
        if ($eq) {
            $zone = $eq->assetLocation?->lab?->zone;
            if ($zone) {
                $this->planItemLocation = $zone->value ?: $zone->name ?: $zone->key ?: '';
            }
        }
    }

    // ─── Replacement Plan ────────────────────────────────────────────────────

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
            'newPlanName' => 'required|string|max:255',
            'newPlanStartYear' => 'required|integer|min:2000|max:2100',
            'newPlanEndYear' => 'required|integer|min:2000|max:2100|gte:newPlanStartYear',
        ]);
        $plan = EquipmentReplacementPlan::create([
            'name' => $this->newPlanName,
            'start_year' => $this->newPlanStartYear,
            'end_year' => $this->newPlanEndYear,
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
            'planItemName' => 'required|string|max:255',
            'planItemYear' => 'required|string',
            'planItemLocation' => 'nullable|string|max:255',
            'planItemRemark' => 'nullable|string',
        ]);
        if ($this->editingItemId) {
            $item = EquipmentReplacementPlanItem::find($this->editingItemId);
            $item?->update([
                'equipment_id' => $this->selectedEquipmentId ?: null,
                'equipment_name' => $this->planItemName,
                'scheduled_year' => $this->planItemYear,
                'location' => $this->planItemLocation,
                'remark' => $this->planItemRemark,
            ]);
        } else {
            EquipmentReplacementPlanItem::create([
                'equipment_replacement_plan_id' => $this->activePlanId,
                'equipment_id' => $this->selectedEquipmentId ?: null,
                'equipment_name' => $this->planItemName,
                'scheduled_year' => $this->planItemYear,
                'location' => $this->planItemLocation,
                'remark' => $this->planItemRemark,
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
            $this->editingItemId = $item->id;
            $this->selectedEquipmentId = $item->equipment_id;
            $this->planItemName = $item->equipment_name;
            $this->equipmentSearch = $item->equipment_name;
            $this->planItemYear = $item->scheduled_year;
            $this->planItemLocation = $item->location;
            $this->planItemRemark = $item->remark;
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
        $data = [['GCLA ANNUAL MAINTENANCE PROGRAM']];
        if ($this->maintenancePeriodLabel)
            $data[] = ['Period: ' . $this->maintenancePeriodLabel];
        $data[] = [];
        $data[] = ['Equipment Name', 'Serial Number', 'Location (Zones)', 'Serviced Date', 'Status', 'Next Service', 'Remark'];
        foreach ($this->allEquipments() as $eq) {
            $l = $eq->annualMaintenances->first();
            $data[] = [
                $eq->name,
                $eq->serial_number ?? '—',
                $eq->zone_name,
                $l && $l->serviced_date ? $l->serviced_date->format('Y-m-d') : '—',
                $l->status ?? '—',
                $l && $l->next_service ? $l->next_service->format('Y-m-d') : '—',
                $l->remark ?? '—',
            ];
        }
        return Excel::download(
            new class ($data) implements FromArray {
            protected $rows;
            public function __construct($r)
            {
                $this->rows = $r; }
            public function array(): array
            {
                return $this->rows; }
            },
            'GCLA_Annual_Maintenance_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function exportPreventive()
    {
        $this->authorizeAction('equipment.maintenance.view');
        $data = [['GCLA EQUIPMENT PREVENTIVE MAINTENANCE PROGRAM']];
        if ($this->maintenancePeriodLabel)
            $data[] = ['Period: ' . $this->maintenancePeriodLabel];
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
        foreach ($this->allEquipments() as $eq) {
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

        return Excel::download(
            new class ($data) implements FromArray {
            protected $rows;
            public function __construct($r)
            {
                $this->rows = $r; }
            public function array(): array
            {
                return $this->rows; }
            },
            'GCLA_Preventive_Maintenance_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function exportRegister()
    {
        $this->authorizeAction('equipment.maintenance.view');
        $data = [['GCLA DSM - EQUIPMENT MAINTENANCE REGISTER' . ($this->maintenancePeriodLabel ? ' FOR ' . $this->maintenancePeriodLabel : '')]];
        $data[] = [];
        $data[] = ['Equipment Name', 'Service Provider', 'Type of Service', 'Cost (USD)', 'Cost (TZS)'];
        $totalUsd = $totalTzs = 0;
        foreach ($this->allEquipments() as $eq) {
            $l = $eq->maintenanceRegisters->first();
            $usd = $l ? (float) $l->cost_usd : 0;
            $tzs = $l ? (float) $l->cost_tzs : 0;
            $totalUsd += $usd;
            $totalTzs += $tzs;
            $data[] = [$eq->name, $l->service_provider ?? '—', $l->service_type ?? '—', number_format($usd, 2), number_format($tzs, 2)];
        }
        $data[] = [];
        $data[] = ['Total', '', '', number_format($totalUsd, 2), number_format($totalTzs, 2)];
        return Excel::download(
            new class ($data) implements FromArray {
            protected $rows;
            public function __construct($r)
            {
                $this->rows = $r; }
            public function array(): array
            {
                return $this->rows; }
            },
            'GCLA_Maintenance_Register_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function exportReplacement()
    {
        $this->authorizeAction('equipment.maintenance.view');
        if (!$this->activePlanId)
            return;
        $plan = EquipmentReplacementPlan::with('items')->find($this->activePlanId);
        if (!$plan)
            return;
        $years = $plan->getYearsRange();
        $data = [[strtoupper($plan->name)]];
        if ($this->maintenancePeriodLabel)
            $data[] = ['Period: ' . $this->maintenancePeriodLabel];
        $data[] = [];
        $header = ['Equipment Name', 'Location (Zone)', ...$years, 'Remark'];
        $data[] = $header;
        foreach ($plan->items as $item) {
            $row = [$item->equipment_name, $item->location ?? '—'];
            foreach ($years as $year) {
                $row[] = ($item->scheduled_year === $year) ? '✓' : '—';
            }
            $row[] = $item->remark ?? '—';
            $data[] = $row;
        }
        return Excel::download(
            new class ($data) implements FromArray {
            protected $rows;
            public function __construct($r)
            {
                $this->rows = $r; }
            public function array(): array
            {
                return $this->rows; }
            },
            str_replace(' ', '_', $plan->name) . '_' . date('Ymd_His') . '.xlsx'
        );
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        $equipments = $this->equipments;

        $totalUsd = $totalTzs = 0;
        foreach ($this->allEquipments() as $eq) {
            $reg = $eq->maintenanceRegisters->first();
            if ($reg) {
                $totalUsd += (float) $reg->cost_usd;
                $totalTzs += (float) $reg->cost_tzs;
            }
        }

        $activePlan = $planItems = $planYears = null;
        $currentPrograms = $this->currentPrograms;
        $currentProgramId = $this->currentProgramId;
        $activeMaintenanceProgram = $this->activeMaintenanceProgram;
        if ($this->activePlanId) {
            $activePlan = EquipmentReplacementPlan::with(['items.equipment'])->find($this->activePlanId);
            if ($activePlan) {
                $planItems = $activePlan->items;
                $planYears = $activePlan->getYearsRange();
            }
        }

        return view('livewire.equipment.equipment-maintenance', compact(
            'equipments',
            'totalUsd',
            'totalTzs',
            'currentPrograms',
            'currentProgramId',
            'activeMaintenanceProgram',
            'activePlan',
            'planItems',
            'planYears'
        ));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function authorizeAction(string $permission): void
    {
        $user = auth()->user();
        if (!$user)
            abort(403);
        if ((method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) || $user->can($permission))
            return;
        abort(403, 'Unauthorized.');
    }
}
