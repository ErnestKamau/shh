<?php

namespace App\Livewire\Personnel;

use App\ModulePreConfigs;
use Livewire\Component;
use Livewire\WithPagination;

class ConfigurationManager extends Component
{
    use WithPagination;

    public string $config = '';
    public string $module = '';
    public string $search = '';
    public int $perPage = 25;
    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];
    public bool $showConfigModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingConfigId = null;
    public string $configName = '';
    public string $configDescription = '';
    public string $message = '';
    public string $messageType = 'success';

    protected $paginationTheme = 'bootstrap';

    public function mount(string $config, string $module): void
    {
        $this->config = $config;
        $this->module = $module;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->editingConfigId = null;
        $this->configName = '';
        $this->configDescription = '';
        $this->showConfigModal = true;
    }

    public function openEditModal(int $configId): void
    {
        $item = ModulePreConfigs::query()
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->findOrFail($configId);

        $this->editingConfigId = $item->id;
        $this->configName = (string) $item->name;
        $this->configDescription = (string) ($item->description ?? '');
        $this->showConfigModal = true;
    }

    public function closeConfigModal(): void
    {
        $this->showConfigModal = false;
    }

    public function saveConfig(): void
    {
        $this->validate([
            'configName' => 'required|string|max:255',
            'configDescription' => 'nullable|string|max:1000',
        ]);

        if ($this->editingConfigId) {
            $item = ModulePreConfigs::query()
                ->where('inventory_location_id', getCurrentUserLocation()->id)
                ->findOrFail($this->editingConfigId);
        } else {
            $item = new ModulePreConfigs();
            $item->type = $this->config;
            $item->module = $this->module;
            $item->inventory_location_id = getCurrentUserLocation()->id;
        }

        $item->name = $this->configName;
        $item->description = $this->configDescription;
        $item->save();

        $this->showConfigModal = false;
        $this->message = $this->editingConfigId ? 'Configuration updated successfully.' : 'Configuration added successfully.';
        $this->messageType = 'success';
    }

    public function openDeleteModal(int $configId): void
    {
        $item = ModulePreConfigs::query()
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->findOrFail($configId);
        $this->editingConfigId = $item->id;
        $this->configName = (string) $item->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteConfig(): void
    {
        $this->validate(['editingConfigId' => 'required|integer']);
        $item = ModulePreConfigs::query()
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->findOrFail((int) $this->editingConfigId);
        $item->delete();
        $this->showDeleteModal = false;
        $this->message = 'Configuration deleted successfully.';
        $this->messageType = 'success';
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function getConfigItemsProperty()
    {
        $query = ModulePreConfigs::query()
            ->where('type', $this->config)
            ->where('module', $this->module)
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->orderBy('name');

        if ($this->search !== '') {
            $searchText = '%' . $this->search . '%';
            $query->where(function ($builder) use ($searchText): void {
                $builder->where('name', 'like', $searchText)
                    ->orWhere('description', 'like', $searchText);
            });
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.personnel.configuration-manager');
    }
}
