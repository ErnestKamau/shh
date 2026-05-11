<?php

namespace App\Livewire\Personnel\Zones;

use App\Directorate;
use App\Lab;
use App\User;
use App\Zone;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ZoneConfigurationManager extends Component
{
    public string $module = '';

    public ?string $selectedZoneId = null;
    public ?string $selectedDirectorateId = null;

    public string $zoneSearch = '';
    public string $directorateSearch = '';
    public string $labSearch = '';

    public bool $showZoneModal = false;
    public bool $showDirectorateModal = false;
    public bool $showLabModal = false;
    public bool $showDeleteModal = false;

    public ?string $editingZoneId = null;
    public ?string $editingDirectorateId = null;
    public ?string $editingLabId = null;
    public ?string $deleteZoneId = null;
    public ?string $deleteDirectorateId = null;
    public ?string $deleteLabId = null;

    public string $message = '';
    public string $messageType = 'success';
    public bool $showToast = false;

    public array $zoneForm = [
        'key' => '',
        'value' => '',
        'description' => '',
        'section_head_user_id' => '',
    ];

    public array $directorateForm = [
        'name' => '',
        'code' => '',
        'section_head_user_id' => '',
    ];

    public array $labForm = [
        'name' => '',
        'code' => '',
        'section_head_user_id' => '',
        'email' => '',
        'phone1' => '',
        'address' => '',
        'location' => '',
        'start_sample_no' => '',
    ];

    public function mount(string $module): void
    {
        $this->module = $module;
        $this->selectedDirectorateId = Directorate::query()
            ->whereHas('zone', fn ($query) => $query->where('inventory_location_id', getCurrentUserLocation()->id))
            ->orderBy('name')
            ->value('id');

        $this->syncSelectedZoneFromDirectorate();
        if (!$this->selectedZoneId) {
            $this->selectedZoneId = Zone::query()
                ->where('inventory_location_id', getCurrentUserLocation()->id)
                ->orderBy('key')
                ->value('id');

            $this->syncSelectedDirectorate();
        }
    }

    public function getUsersProperty(): Collection
    {
        return User::query()
            ->where('company_id', getUserCompany())
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function getZonesProperty(): Collection
    {
        $query = Zone::query()
            ->with('sectionHeadUser')
            ->withCount('directorates')
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->orderBy('key');

        if ($this->selectedDirectorateId) {
            $zoneId = Directorate::query()
                ->where('id', $this->selectedDirectorateId)
                ->value('zone_id');

            if ($zoneId) {
                $query->where('id', $zoneId);
            }
        }

        if ($this->zoneSearch !== '') {
            $query->where(function ($builder): void {
                $builder->where('key', 'like', '%' . $this->zoneSearch . '%')
                    ->orWhere('value', 'like', '%' . $this->zoneSearch . '%')
                    ->orWhere('description', 'like', '%' . $this->zoneSearch . '%');
            });
        }

        return $query->get();
    }

    public function getDirectoratesProperty(): Collection
    {
        $query = Directorate::query()
            ->with(['sectionHeadUser', 'head', 'zone'])
            ->whereHas('zone', fn ($zoneQuery) => $zoneQuery->where('inventory_location_id', getCurrentUserLocation()->id))
            ->withCount('labs')
            ->orderBy('name');

        if ($this->directorateSearch !== '') {
            $query->where(function ($builder): void {
                $builder->where('name', 'like', '%' . $this->directorateSearch . '%')
                    ->orWhere('code', 'like', '%' . $this->directorateSearch . '%');
            });
        }

        return $query->get();
    }

    public function getLabsProperty(): Collection
    {
        if (!$this->selectedDirectorateId) {
            return collect();
        }

        $query = Lab::query()
            ->with(['sectionHeadUser', 'manager', 'directorate'])
            ->where('directorate_id', $this->selectedDirectorateId)
            ->orderBy('name');

        if ($this->selectedZoneId) {
            $query->where('zone_id', $this->selectedZoneId);
        }

        if ($this->labSearch !== '') {
            $query->where(function ($builder): void {
                $builder->where('name', 'like', '%' . $this->labSearch . '%')
                    ->orWhere('code', 'like', '%' . $this->labSearch . '%');
            });
        }

        return $query->get();
    }

    public function selectZone(string $zoneId): void
    {
        $this->selectedZoneId = $zoneId;
        $this->syncSelectedDirectorate();
    }

    public function selectDirectorate(string $directorateId): void
    {
        $this->selectedDirectorateId = $directorateId;
        $this->syncSelectedZoneFromDirectorate();
    }

    public function openZoneModal(): void
    {
        $this->resetValidation();
        $this->editingZoneId = null;
        $this->zoneForm = [
            'key' => '',
            'value' => '',
            'description' => '',
            'section_head_user_id' => '',
        ];
        $this->showZoneModal = true;
    }

    public function closeZoneModal(): void
    {
        $this->showZoneModal = false;
    }

    public function saveZone(): void
    {
        $validated = $this->validate([
            'zoneForm.key' => ['required', 'string', 'max:255'],
            'zoneForm.value' => ['required', 'string', 'max:255'],
            'zoneForm.description' => ['nullable', 'string', 'max:1000'],
            'zoneForm.section_head_user_id' => ['nullable', 'uuid', 'exists:users,id'],
        ]);

        $zonePayload = [
            'key' => trim($validated['zoneForm']['key']),
            'value' => trim($validated['zoneForm']['value']),
            'description' => $validated['zoneForm']['description'] ?: null,
            'section_head_user_id' => $validated['zoneForm']['section_head_user_id'] ?: null,
        ];

        if ($this->editingZoneId) {
            Zone::query()
                ->where('id', $this->editingZoneId)
                ->where('inventory_location_id', getCurrentUserLocation()->id)
                ->update($zonePayload);

            $this->selectedZoneId = $this->editingZoneId;
            $this->message = 'Zone updated successfully.';
        } else {
            $zone = Zone::query()->create($zonePayload + [
                'module' => 'Global',
                'inventory_location_id' => getCurrentUserLocation()->id,
            ]);
            $this->selectedZoneId = (string) $zone->id;
            $this->message = 'Zone created successfully.';
        }

        $this->syncSelectedDirectorate();
        $this->showZoneModal = false;
        $this->messageType = 'success';
        $this->showToast = true;
    }

    public function editZone(string $zoneId): void
    {
        $zone = Zone::query()
            ->where('id', $zoneId)
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->first();

        if (!$zone) {
            $this->message = 'Zone not found.';
            $this->messageType = 'danger';
            $this->showToast = true;
            return;
        }

        $this->resetValidation();
        $this->editingZoneId = (string) $zone->id;
        $this->zoneForm = [
            'key' => (string) $zone->key,
            'value' => (string) ($zone->value ?? ''),
            'description' => (string) ($zone->description ?? ''),
            'section_head_user_id' => (string) ($zone->section_head_user_id ?? ''),
        ];
        $this->showZoneModal = true;
    }

    public function confirmDeleteZone(string $zoneId): void
    {
        $this->deleteZoneId = $zoneId;
        $this->deleteDirectorateId = null;
        $this->deleteLabId = null;
        $this->showDeleteModal = true;
    }

    public function openDirectorateModal(): void
    {
        $this->resetValidation();
        $this->editingDirectorateId = null;
        $this->directorateForm = [
            'name' => '',
            'code' => '',
            'section_head_user_id' => '',
        ];
        $this->showDirectorateModal = true;
    }

    public function closeDirectorateModal(): void
    {
        $this->showDirectorateModal = false;
    }

    public function saveDirectorate(): void
    {
        if (!$this->selectedZoneId) {
            $this->addError('selectedZoneId', 'Please select a zone before adding a directorate.');
            return;
        }

        $nameRule = Rule::unique('directorates', 'name');
        $codeRule = Rule::unique('directorates', 'code');

        if ($this->editingDirectorateId) {
            $nameRule = $nameRule->ignore($this->editingDirectorateId);
            $codeRule = $codeRule->ignore($this->editingDirectorateId);
        }

        $validated = $this->validate([
            'directorateForm.name' => ['required', 'string', 'max:255', $nameRule],
            'directorateForm.code' => ['required', 'string', 'max:100', $codeRule],
            'directorateForm.section_head_user_id' => ['nullable', 'uuid', 'exists:users,id'],
        ]);

        $sectionHeadUserId = $validated['directorateForm']['section_head_user_id'] ?: null;

        $directoratePayload = [
            'name' => trim($validated['directorateForm']['name']),
            'code' => trim($validated['directorateForm']['code']),
            'zone_id' => $this->selectedZoneId,
            'section_head_user_id' => $sectionHeadUserId,
            'head_id' => $sectionHeadUserId,
        ];

        if ($this->editingDirectorateId) {
            Directorate::query()
                ->where('id', $this->editingDirectorateId)
                ->where('zone_id', $this->selectedZoneId)
                ->update($directoratePayload);

            $this->selectedDirectorateId = $this->editingDirectorateId;
            $this->message = 'Directorate updated successfully.';
        } else {
            $directorate = Directorate::query()->create($directoratePayload + [
                'active' => true,
            ]);
            $this->selectedDirectorateId = (string) $directorate->id;
            $this->message = 'Directorate created successfully.';
        }

        $this->showDirectorateModal = false;
        $this->messageType = 'success';
        $this->showToast = true;
    }

    public function editDirectorate(string $directorateId): void
    {
        if (!$this->selectedZoneId) {
            $this->message = 'Please select a zone first.';
            $this->messageType = 'danger';
            $this->showToast = true;
            return;
        }

        $directorate = Directorate::query()
            ->where('id', $directorateId)
            ->where('zone_id', $this->selectedZoneId)
            ->first();

        if (!$directorate) {
            $this->message = 'Directorate not found.';
            $this->messageType = 'danger';
            $this->showToast = true;
            return;
        }

        $this->resetValidation();
        $this->editingDirectorateId = (string) $directorate->id;
        $this->directorateForm = [
            'name' => (string) $directorate->name,
            'code' => (string) $directorate->code,
            'section_head_user_id' => (string) ($directorate->section_head_user_id ?? ''),
        ];
        $this->showDirectorateModal = true;
    }

    public function confirmDeleteDirectorate(string $directorateId): void
    {
        $this->deleteZoneId = null;
        $this->deleteDirectorateId = $directorateId;
        $this->deleteLabId = null;
        $this->showDeleteModal = true;
    }

    public function openLabModal(): void
    {
        if (!$this->selectedZoneId || !$this->selectedDirectorateId) {
            $this->addError('selectedDirectorateId', 'Please select a zone and directorate before adding a lab.');
            return;
        }

        $this->resetValidation();
        $this->editingLabId = null;
        $this->labForm = [
            'name' => '',
            'code' => '',
            'section_head_user_id' => '',
            'email' => '',
            'phone1' => '',
            'address' => '',
            'location' => '',
            'start_sample_no' => '',
        ];
        $this->showLabModal = true;
    }

    public function closeLabModal(): void
    {
        $this->showLabModal = false;
    }

    public function saveLab(): void
    {
        if (!$this->selectedZoneId || !$this->selectedDirectorateId) {
            $this->addError('selectedDirectorateId', 'Please select a zone and directorate before adding a lab.');
            return;
        }

        $nameRule = Rule::unique('labs', 'name')
            ->where(fn ($query) => $query->where('directorate_id', $this->selectedDirectorateId));
        $codeRule = Rule::unique('labs', 'code')
            ->where(fn ($query) => $query->where('directorate_id', $this->selectedDirectorateId));

        if ($this->editingLabId) {
            $nameRule = $nameRule->ignore($this->editingLabId);
            $codeRule = $codeRule->ignore($this->editingLabId);
        }

        $validated = $this->validate([
            'labForm.name' => ['required', 'string', 'max:255', $nameRule],
            'labForm.code' => ['required', 'string', 'max:100', $codeRule],
            'labForm.section_head_user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'labForm.email' => ['required', 'email', 'max:255'],
            'labForm.phone1' => ['required', 'string', 'max:100'],
            'labForm.address' => ['required', 'string', 'max:255'],
            'labForm.location' => ['required', 'string', 'max:255'],
            'labForm.start_sample_no' => ['nullable', 'string', 'max:100'],
        ]);

        $sectionHeadUserId = $validated['labForm']['section_head_user_id'] ?: null;

        $labPayload = [
            'name' => trim($validated['labForm']['name']),
            'code' => trim($validated['labForm']['code']),
            'zone_id' => $this->selectedZoneId,
            'directorate_id' => $this->selectedDirectorateId,
            'section_head_user_id' => $sectionHeadUserId,
            'manager_id' => $sectionHeadUserId,
            'company_id' => getUserCompany(),
            'email' => trim($validated['labForm']['email']),
            'phone1' => trim($validated['labForm']['phone1']),
            'address' => trim($validated['labForm']['address']),
            'location' => trim($validated['labForm']['location']),
            'start_sample_no' => $validated['labForm']['start_sample_no'] !== ''
                ? trim($validated['labForm']['start_sample_no'])
                : trim($validated['labForm']['code']),
        ];

        if ($this->editingLabId) {
            Lab::query()
                ->where('id', $this->editingLabId)
                ->where('zone_id', $this->selectedZoneId)
                ->where('directorate_id', $this->selectedDirectorateId)
                ->update($labPayload);

            $this->message = 'Lab updated successfully.';
        } else {
            Lab::query()->create($labPayload + [
                'active' => true,
                'is_external' => false,
            ]);
            $this->message = 'Lab created successfully.';
        }

        $this->showLabModal = false;
        $this->messageType = 'success';
        $this->showToast = true;
    }

    public function editLab(string $labId): void
    {
        if (!$this->selectedZoneId || !$this->selectedDirectorateId) {
            $this->message = 'Please select a zone and directorate first.';
            $this->messageType = 'danger';
            $this->showToast = true;
            return;
        }

        $lab = Lab::query()
            ->where('id', $labId)
            ->where('zone_id', $this->selectedZoneId)
            ->where('directorate_id', $this->selectedDirectorateId)
            ->first();

        if (!$lab) {
            $this->message = 'Lab not found.';
            $this->messageType = 'danger';
            $this->showToast = true;
            return;
        }

        $this->resetValidation();
        $this->editingLabId = (string) $lab->id;
        $this->labForm = [
            'name' => (string) $lab->name,
            'code' => (string) $lab->code,
            'section_head_user_id' => (string) ($lab->section_head_user_id ?? ''),
            'email' => (string) ($lab->email ?? ''),
            'phone1' => (string) ($lab->phone1 ?? ''),
            'address' => (string) ($lab->address ?? ''),
            'location' => (string) ($lab->location ?? ''),
            'start_sample_no' => (string) ($lab->start_sample_no ?? ''),
        ];
        $this->showLabModal = true;
    }

    public function confirmDeleteLab(string $labId): void
    {
        $this->deleteZoneId = null;
        $this->deleteDirectorateId = null;
        $this->deleteLabId = $labId;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteZoneId = null;
        $this->deleteDirectorateId = null;
        $this->deleteLabId = null;
    }

    public function deleteSelected(): void
    {
        if ($this->deleteZoneId) {
            $zoneId = $this->deleteZoneId;
            $hasDirectorates = Directorate::query()->where('zone_id', $zoneId)->exists();
            if ($hasDirectorates) {
                $this->message = 'Cannot delete zone with existing directorates.';
                $this->messageType = 'danger';
                $this->showToast = true;
                $this->closeDeleteModal();
                return;
            }

            Zone::query()
                ->where('id', $zoneId)
                ->where('inventory_location_id', getCurrentUserLocation()->id)
                ->delete();

            if ($this->selectedZoneId === $zoneId) {
                $this->selectedZoneId = Zone::query()
                    ->where('inventory_location_id', getCurrentUserLocation()->id)
                    ->orderBy('key')
                    ->value('id');
                $this->syncSelectedDirectorate();
            }

            $this->message = 'Zone deleted successfully.';
            $this->messageType = 'success';
            $this->showToast = true;
            $this->closeDeleteModal();
            return;
        }

        if ($this->deleteDirectorateId) {
            $directorateId = $this->deleteDirectorateId;
            $hasLabs = Lab::query()->where('directorate_id', $directorateId)->exists();
            if ($hasLabs) {
                $this->message = 'Cannot delete directorate with existing labs.';
                $this->messageType = 'danger';
                $this->showToast = true;
                $this->closeDeleteModal();
                return;
            }

            Directorate::query()
                ->where('id', $directorateId)
                ->delete();

            if ($this->selectedDirectorateId === $directorateId) {
                $this->syncSelectedDirectorate();
            }

            $this->message = 'Directorate deleted successfully.';
            $this->messageType = 'success';
            $this->showToast = true;
            $this->closeDeleteModal();
            return;
        }

        if ($this->deleteLabId) {
            $deleted = Lab::query()
                ->where('id', $this->deleteLabId)
                ->where('zone_id', $this->selectedZoneId)
                ->where('directorate_id', $this->selectedDirectorateId)
                ->delete();

            if ($deleted === 0) {
                $this->message = 'Lab not found.';
                $this->messageType = 'danger';
                $this->showToast = true;
                $this->closeDeleteModal();
                return;
            }

            $this->message = 'Lab deleted successfully.';
            $this->messageType = 'success';
            $this->showToast = true;
            $this->closeDeleteModal();
            return;
        }

        $this->closeDeleteModal();
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->showToast = false;
    }

    public function getDeletePromptProperty(): string
    {
        if ($this->deleteZoneId) {
            return 'Delete this zone? This action cannot be undone.';
        }

        if ($this->deleteDirectorateId) {
            return 'Delete this directorate? This action cannot be undone.';
        }

        if ($this->deleteLabId) {
            return 'Delete this lab? This action cannot be undone.';
        }

        return 'Delete selected item?';
    }

    private function syncSelectedDirectorate(): void
    {
        if (!$this->selectedZoneId) {
            $this->selectedDirectorateId = null;
            return;
        }

        $exists = Directorate::query()
            ->where('zone_id', $this->selectedZoneId)
            ->where('id', $this->selectedDirectorateId)
            ->exists();

        if ($exists) {
            return;
        }

        $this->selectedDirectorateId = Directorate::query()
            ->where('zone_id', $this->selectedZoneId)
            ->orderBy('name')
            ->value('id');
    }

    private function syncSelectedZoneFromDirectorate(): void
    {
        if (!$this->selectedDirectorateId) {
            $this->selectedZoneId = null;
            return;
        }

        $zoneId = Directorate::query()
            ->where('id', $this->selectedDirectorateId)
            ->value('zone_id');

        $this->selectedZoneId = $zoneId ? (string) $zoneId : null;
    }

    public function render()
    {
        return view('livewire.personnel.zones.zone-configuration-manager');
    }
}
