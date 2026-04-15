<?php

namespace App\Livewire;

use App\Zone;
use Livewire\Component;
use Livewire\WithPagination;

class ZoneManager extends Component
{
    use WithPagination;

    public string $module = '';
    public string $search = '';
    public int $perPage = 25;
    public array $perPageOptions = [10, 25, 50, 100];
    public bool $showZoneModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingZoneId = null;
    public string $zoneKey = '';
    public string $zoneValue = '';
    public string $zoneDescription = '';
    public string $message = '';
    public string $messageType = 'success';

    protected string $paginationTheme = 'bootstrap';

    public function mount(string $module): void
    {
        $this->module = $module;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->editingZoneId = null;
        $this->zoneKey = '';
        $this->zoneValue = '';
        $this->zoneDescription = '';
        $this->showZoneModal = true;
    }

    public function openEditModal(int $zoneId): void
    {
        $zone = Zone::query()
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->findOrFail($zoneId);

        $this->editingZoneId = $zone->id;
        $this->zoneKey = (string) $zone->key;
        $this->zoneValue = (string) $zone->value;
        $this->zoneDescription = (string) ($zone->description ?? '');
        $this->showZoneModal = true;
    }

    public function closeZoneModal(): void
    {
        $this->showZoneModal = false;
    }

    public function saveZone(): void
    {
        $this->validate([
            'zoneKey' => 'required|string|max:255',
            'zoneValue' => 'required|string|max:255',
            'zoneDescription' => 'nullable|string|max:1000',
        ]);

        if ($this->editingZoneId) {
            $zone = Zone::query()
                ->where('inventory_location_id', getCurrentUserLocation()->id)
                ->findOrFail($this->editingZoneId);
        } else {
            $zone = new Zone();
            $zone->module = 'Global';
            $zone->inventory_location_id = getCurrentUserLocation()->id;
        }

        $zone->key = $this->zoneKey;
        $zone->value = $this->zoneValue;
        $zone->description = $this->zoneDescription;
        $zone->save();

        $this->showZoneModal = false;
        $this->message = $this->editingZoneId ? 'Zone updated successfully.' : 'Zone added successfully.';
        $this->messageType = 'success';
    }

    public function openDeleteModal(int $zoneId): void
    {
        $zone = Zone::query()
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->findOrFail($zoneId);

        $this->editingZoneId = $zone->id;
        $this->zoneKey = (string) $zone->key;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteZone(): void
    {
        $this->validate(['editingZoneId' => 'required|integer']);

        $zone = Zone::query()
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->findOrFail($this->editingZoneId);

        $zone->delete();
        $this->showDeleteModal = false;
        $this->message = 'Zone deleted successfully.';
        $this->messageType = 'success';
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function getZoneItemsProperty()
    {
        $query = Zone::query()
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->orderBy('key');

        if ($this->search !== '') {
            $searchText = '%' . $this->search . '%';
            $query->where(function ($builder) use ($searchText): void {
                $builder->where('key', 'like', $searchText)
                    ->orWhere('value', 'like', $searchText)
                    ->orWhere('description', 'like', $searchText);
            });
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.zone-manager');
    }
}
