<?php

namespace App\Livewire\Equipment\Assets;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Assets\AssetLocation;
use App\Lab;
use App\Directorate;
use App\Zone;

class AssetLocationManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 25;

    // Filters
    public $directorateFilter = null;
    public $zoneFilter = null;
    public $labFilter = null;

    // Tag-select UI state
    public $directorateSearch = '';
    public $zoneSearch = '';
    public $labSearch = '';
    public $showDirectorateDropdown = false;
    public $showZoneDropdown = false;
    public $showLabDropdown = false;
    
    // Form properties
    public $showModal = false;
    public $editId = null;
    public $location_code = '';
    public $name = '';
    public $is_active = true;
    public $lab_id = null;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'location_code' => 'required|string|max:50|unique:asset_locations,location_code',
        'name' => 'required|string|max:255',
        'is_active' => 'boolean',
        'lab_id' => 'nullable|uuid|exists:labs,id',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->perPage = 25;
        $this->directorateFilter = null;
        $this->zoneFilter = null;
        $this->labFilter = null;

        $this->directorateSearch = '';
        $this->zoneSearch = '';
        $this->labSearch = '';

        $this->showDirectorateDropdown = false;
        $this->showZoneDropdown = false;
        $this->showLabDropdown = false;

        $this->resetPage();
    }

    public function getSelectedDirectorateProperty()
    {
        if (empty($this->directorateFilter)) {
            return null;
        }

        return Directorate::query()->find($this->directorateFilter);
    }

    public function getFilteredDirectoratesProperty()
    {
        $search = strtolower(trim($this->directorateSearch));

        $query = Directorate::query()->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->limit(15)->get();
    }

    public function selectDirectorateFilter($id): void
    {
        $directorate = Directorate::query()->find($id);

        if (!$directorate) {
            return;
        }

        $this->directorateFilter = $directorate->id;
        $this->directorateSearch = (string) ($directorate->name ?? '');
        $this->showDirectorateDropdown = false;

        // Directorate change can invalidate lower-level filters.
        $this->zoneFilter = null;
        $this->labFilter = null;
        $this->zoneSearch = '';
        $this->labSearch = '';

        $this->resetPage();
    }

    public function clearDirectorateFilter(): void
    {
        $this->directorateFilter = null;
        $this->directorateSearch = '';
        $this->showDirectorateDropdown = true;

        $this->zoneFilter = null;
        $this->labFilter = null;
        $this->zoneSearch = '';
        $this->labSearch = '';

        $this->resetPage();
    }

    public function getSelectedZoneProperty()
    {
        if (empty($this->zoneFilter)) {
            return null;
        }

        return Zone::query()->find($this->zoneFilter);
    }

    public function getFilteredZonesProperty()
    {
        if (empty($this->directorateFilter)) {
            return collect();
        }

        $search = strtolower(trim($this->zoneSearch));
        $query = Zone::query()->orderBy('value');

        $directorateZoneId = Directorate::query()
            ->whereKey($this->directorateFilter)
            ->value('zone_id');

        if ($directorateZoneId) {
            $query->whereKey($directorateZoneId);
        } else {
            $query->whereRaw('1 = 0');
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('value', 'like', '%' . $search . '%')
                    ->orWhere('key', 'like', '%' . $search . '%');
            });
        }

        return $query->limit(15)->get();
    }

    public function selectZoneFilter($id): void
    {
        $zone = Zone::query()->find($id);

        if (!$zone) {
            return;
        }

        if (!empty($this->directorateFilter)) {
            $directorateZoneId = Directorate::query()
                ->whereKey($this->directorateFilter)
                ->value('zone_id');

            if ((string) $zone->id !== (string) $directorateZoneId) {
                return;
            }
        }

        $this->zoneFilter = $zone->id;
        $this->zoneSearch = (string) ($zone->value ?: $zone->key ?: '');
        $this->showZoneDropdown = false;

        // Zone change can invalidate lab filter.
        $this->labFilter = null;
        $this->labSearch = '';

        $this->resetPage();
    }

    public function clearZoneFilter(): void
    {
        $this->zoneFilter = null;
        $this->zoneSearch = '';
        $this->showZoneDropdown = true;

        $this->labFilter = null;
        $this->labSearch = '';

        $this->resetPage();
    }

    public function getSelectedLabFilterProperty()
    {
        if (empty($this->labFilter)) {
            return null;
        }

        return Lab::query()->find($this->labFilter);
    }

    public function getFilteredLabsProperty()
    {
        if (empty($this->zoneFilter)) {
            return collect();
        }

        $search = strtolower(trim($this->labSearch));

        $query = Lab::query()
            ->orderBy('name')
            ->where('zone_id', $this->zoneFilter)
            ->when(!empty($this->directorateFilter), function ($q) {
                $q->where('directorate_id', $this->directorateFilter);
            });

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->limit(15)->get();
    }

    public function selectLabFilter($id): void
    {
        $lab = Lab::query()->find($id);

        if (!$lab) {
            return;
        }

        $this->labFilter = $lab->id;
        $this->labSearch = (string) ($lab->name ?? '');
        $this->showLabDropdown = false;

        $this->resetPage();
    }

    public function clearLabFilter(): void
    {
        $this->labFilter = null;
        $this->labSearch = '';
        $this->showLabDropdown = true;

        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $this->resetForm();
        
        $location = AssetLocation::findOrFail($id);
        $this->editId = $id;
        $this->location_code = $location->location_code;
        $this->name = $location->name;
        $this->is_active = (bool)$location->is_active;
            $this->lab_id = $location->lab_id;
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->location_code = '';
        $this->name = '';
        $this->is_active = true;
        $this->lab_id = null;
    }

    public function save()
    {
        // Adjust unique rule for update
        $rules = $this->rules;
        if ($this->editId) {
            $rules['location_code'] = 'required|string|max:50|unique:asset_locations,location_code,' . $this->editId;
        }

        $this->validate($rules);

        if ($this->editId) {
            $location = AssetLocation::findOrFail($this->editId);
            $location->update([
                'location_code' => $this->location_code,
                'name' => $this->name,
                'is_active' => $this->is_active,
                            'lab_id' => $this->lab_id ?: null,
            ]);
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Asset Location updated successfully.']);
        } else {
            AssetLocation::create([
                                'lab_id' => $this->lab_id ?: null,
                'location_code' => $this->location_code,
                'name' => $this->name,
                'is_active' => $this->is_active,
            ]);
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Asset Location created successfully.']);
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        try {
            AssetLocation::findOrFail($id)->delete();
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Asset Location deleted successfully.']);
        } catch (\Exception $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Error deleting Asset Location: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $query = AssetLocation::query()->with('lab');

        if ($this->search) {
            $query->where(function ($subQuery) {
                $subQuery->where('location_code', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->directorateFilter)) {
            $query->whereHas('lab', function ($subQuery) {
                $subQuery->where('directorate_id', $this->directorateFilter);
            });
        }

        if (!empty($this->zoneFilter)) {
            $query->whereHas('lab', function ($subQuery) {
                $subQuery->where('zone_id', $this->zoneFilter);
            });
        }

        if (!empty($this->labFilter)) {
            $query->where('lab_id', $this->labFilter);
        }

        return view('livewire.equipment.assets.asset-location-manager', [
            'locations' => $query->orderBy('created_at', 'desc')->paginate((int) $this->perPage),
            'labs' => Lab::orderBy('name')->get(),
        ]);
    }
}
