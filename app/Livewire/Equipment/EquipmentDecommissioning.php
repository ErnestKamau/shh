<?php

namespace App\Livewire\Equipment;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Equipments\EquipmentDisposal;
use App\Services\Equipment\EquipmentDecommissioningService;
use Illuminate\Support\Facades\DB;

class EquipmentDecommissioning extends Component
{
    use WithFileUploads;

    public $disposalId;
    public $disposal;
    public $showModal = false;

    // Decommissioning data
    public $checklist = [];
    public $labelPhoto = null;

    // Messages
    public $message = '';
    public $messageType = 'success';

    protected $decommissioningService;

    public function boot(EquipmentDecommissioningService $decommissioningService)
    {
        $this->decommissioningService = $decommissioningService;
    }

    public function mount(?int $disposalId = null): void
    {
        if ($disposalId) {
            $this->disposalId = $disposalId;
            $this->loadDisposal();
        }
    }

    public function loadDisposal(): void
    {
        $this->disposal = EquipmentDisposal::with(['equipment'])->find($this->disposalId);

        if (!$this->disposal) {
            $this->message = 'Disposal request not found.';
            $this->messageType = 'danger';
            return;
        }

        // Load checklist
        $this->checklist = $this->disposal->decommissioning_checklist_json ?? [];
    }

    public function openModal(int $disposalId): void
    {
        $this->disposalId = $disposalId;
        $this->loadDisposal();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->labelPhoto = null;
        $this->message = '';
        $this->messageType = 'success';
    }

    public function startDecommissioning(): void
    {
        if (!$this->disposal) {
            $this->message = 'Disposal not found.';
            $this->messageType = 'danger';
            return;
        }

        $result = $this->decommissioningService->startDecommissioning($this->disposal);

        if ($result['success']) {
            $this->checklist = $result['checklist'];
            $this->message = $result['message'];
            $this->messageType = 'success';
            $this->loadDisposal();
        } else {
            $this->message = $result['message'];
            $this->messageType = 'danger';
        }
    }

    public function uploadLabelPhoto(): void
    {
        $this->validate([
            'labelPhoto' => 'required|image|max:10240',
        ]);

        $result = $this->decommissioningService->labelEquipment($this->disposal, $this->labelPhoto);

        if ($result['success']) {
            $this->message = $result['message'];
            $this->messageType = 'success';
            $this->labelPhoto = null;
            $this->loadDisposal();
        } else {
            $this->message = $result['message'];
            $this->messageType = 'danger';
        }
    }

    public function removeFromSchedules(): void
    {
        if (!$this->disposal) {
            return;
        }

        $equipment = $this->disposal->equipment;
        
        $calibrationCount = $this->decommissioningService->removeFromCalibrationSchedule($equipment);
        $maintenanceCount = $this->decommissioningService->removeFromMaintenanceSchedule($equipment);

        // Update checklist
        $checklist = $this->disposal->decommissioning_checklist_json ?? [];
        if (isset($checklist['removed_from_calibration_schedule'])) {
            $checklist['removed_from_calibration_schedule']['completed'] = true;
            $checklist['removed_from_calibration_schedule']['completed_at'] = now()->toIso8601String();
            $checklist['removed_from_calibration_schedule']['completed_by'] = auth()->user()->name;
        }
        if (isset($checklist['removed_from_maintenance_schedule'])) {
            $checklist['removed_from_maintenance_schedule']['completed'] = true;
            $checklist['removed_from_maintenance_schedule']['completed_at'] = now()->toIso8601String();
            $checklist['removed_from_maintenance_schedule']['completed_by'] = auth()->user()->name;
        }

        $this->disposal->decommissioning_checklist_json = $checklist;
        $this->disposal->removed_from_calibration_schedule = true;
        $this->disposal->removed_from_maintenance_schedule = true;
        $this->disposal->save();

        $this->message = "Removed from schedules: {$calibrationCount} calibration(s), {$maintenanceCount} maintenance(s).";
        $this->messageType = 'success';
        $this->loadDisposal();
    }

    public function toggleChecklistItem(string $key): void
    {
        if (!isset($this->checklist[$key])) {
            return;
        }

        $this->checklist[$key]['completed'] = !($this->checklist[$key]['completed'] ?? false);
        
        if ($this->checklist[$key]['completed']) {
            $this->checklist[$key]['completed_at'] = now()->toIso8601String();
            $this->checklist[$key]['completed_by'] = auth()->user()->name;
        } else {
            unset($this->checklist[$key]['completed_at']);
            unset($this->checklist[$key]['completed_by']);
        }

        $this->disposal->decommissioning_checklist_json = $this->checklist;
        $this->disposal->save();

        // Update boolean fields
        switch ($key) {
            case 'equipment_labeled':
                $this->disposal->equipment_labeled = $this->checklist[$key]['completed'];
                break;
            case 'removed_from_calibration_schedule':
                $this->disposal->removed_from_calibration_schedule = $this->checklist[$key]['completed'];
                break;
            case 'removed_from_maintenance_schedule':
                $this->disposal->removed_from_maintenance_schedule = $this->checklist[$key]['completed'];
                break;
            case 'utilities_disconnected':
                $this->disposal->utilities_disconnected = $this->checklist[$key]['completed'];
                break;
            case 'data_wiped':
                $this->disposal->data_wiped = $this->checklist[$key]['completed'];
                break;
            case 'storage_devices_removed':
                $this->disposal->storage_devices_removed = $this->checklist[$key]['completed'];
                break;
        }
        $this->disposal->save();
    }

    public function completeDecommissioning(): void
    {
        $result = $this->decommissioningService->completeDecommissioning($this->disposal, $this->checklist);

        if ($result['success']) {
            $this->message = $result['message'];
            $this->messageType = 'success';
            $this->loadDisposal();
            $this->dispatch('decommissioning-completed', ['disposal_id' => $this->disposal->id]);
        } else {
            $this->message = $result['message'];
            $this->messageType = 'danger';
        }
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = 'success';
    }

    public function render()
    {
        return view('livewire.equipment.equipment-decommissioning');
    }
}


