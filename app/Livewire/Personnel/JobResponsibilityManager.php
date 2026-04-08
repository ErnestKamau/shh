<?php

namespace App\Livewire\Personnel;

use App\JobDescription;
use App\ModulePreConfigs;
use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Livewire\Component;
use Livewire\WithPagination;

class JobResponsibilityManager extends Component
{
    use WithPagination;

    public int $designationId;
    public string $activeTab = 'active';
    public string $search = '';
    public int $perPage = 25;
    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];
    public bool $showModal = false;
    public ?int $editingResponsibilityId = null;
    public ?int $selectedConfigId = null;
    public bool $isActive = true;
    public string $configSearch = '';
    public bool $showConfigDropdown = false;
    /** @var array<int, array{id:int,label:string}> */
    public array $filteredConfigs = [];
    public string $message = '';
    public string $messageType = 'success';

    protected $paginationTheme = 'bootstrap';

    public function mount(int $designationId): void
    {
        $this->designationId = $designationId;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['active', 'inactive'], true) ? $tab : 'active';
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->editingResponsibilityId = null;
        $this->selectedConfigId = null;
        $this->isActive = true;
        $this->filteredConfigs = $this->mapConfigs();
        $this->showModal = true;
    }

    public function openEditModal(int $responsibilityId): void
    {
        $item = JobDescription::query()
            ->where('job_id', $this->designationId)
            ->findOrFail($responsibilityId);

        $this->editingResponsibilityId = $item->id;
        $this->selectedConfigId = (int) $item->config_id;
        $this->isActive = (int) $item->active === 1;
        $this->filteredConfigs = $this->mapConfigs();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showConfigDropdown = false;
        $this->configSearch = '';
    }

    public function saveResponsibility(): void
    {
        $this->validate([
            'selectedConfigId' => 'required|integer',
            'isActive' => 'boolean',
        ]);

        $config = SystemConfiguration::query()->findOrFail((int) $this->selectedConfigId);

        if ($this->editingResponsibilityId) {
            $item = JobDescription::query()
                ->where('job_id', $this->designationId)
                ->findOrFail((int) $this->editingResponsibilityId);
            $item->edited_by = (int) auth()->id();
        } else {
            $item = new JobDescription();
            $item->job_id = $this->designationId;
        }

        $item->config_id = $config->id;
        $item->name = (string) $config->key;
        $item->description = (string) $config->value;
        $item->active = $this->isActive ? 1 : 0;
        $item->save();

        $this->showModal = false;
        $this->message = $this->editingResponsibilityId ? 'Responsibility updated successfully.' : 'Responsibility added successfully.';
        $this->messageType = 'success';
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function searchConfigs(): void
    {
        $this->showConfigDropdown = true;
        $search = trim($this->configSearch);
        $this->filteredConfigs = array_values(array_filter(
            $this->mapConfigs(),
            fn (array $item): bool => $search === '' || stripos($item['label'], $search) !== false
        ));
    }

    public function selectConfig(int $configId): void
    {
        $this->selectedConfigId = $configId;
        $this->showConfigDropdown = false;
        $this->configSearch = '';
    }

    public function clearConfig(): void
    {
        $this->selectedConfigId = null;
    }

    public function closeConfigDropdown(): void
    {
        $this->showConfigDropdown = false;
    }

    public function getDesignationProperty()
    {
        return ModulePreConfigs::query()->findOrFail($this->designationId);
    }

    public function getConfigsProperty()
    {
        $type = SystemConfigurationsType::query()
            ->where('configuration_type', 'Job Designation Responsibilities')
            ->first();

        if (!$type) {
            return collect();
        }

        return SystemConfiguration::query()
            ->where('configuration_type_id', $type->id)
            ->orderBy('key')
            ->get();
    }

    public function getResponsibilitiesProperty()
    {
        $query = JobDescription::query()
            ->where('job_id', $this->designationId)
            ->where('active', $this->activeTab === 'active' ? 1 : 0)
            ->orderByDesc('created_at');

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
        return view('livewire.personnel.job-responsibility-manager');
    }

    /** @return array<int, array{id:int,label:string}> */
    private function mapConfigs(): array
    {
        return $this->configs->map(fn ($config): array => [
            'id' => (int) $config->id,
            'label' => (string) ($config->key . ' (' . $config->value . ')'),
        ])->toArray();
    }
}
