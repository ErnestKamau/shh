<?php

namespace App\Livewire\Equipment\Depreciation;

use App\Jobs\Equipment\RecalculateDepreciationScheduleJob;
use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DepreciationListManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public int $perPage = 25;

    /** @var array<int, int> */
    public array $perPageOptions = [25, 50, 75, 100];

    protected $paginationTheme = 'bootstrap';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function recalculate(string $configId): void
    {
        if (! Auth::user()?->can('equipment.components.depreciation.recalculate')) {
            return;
        }

        RecalculateDepreciationScheduleJob::dispatch($configId, Auth::id(), 'manual_list');
        session()->flash('success', 'Recalculation queued.');
    }

    public function render()
    {
        $query = EquipmentDepreciationConfig::query()
            ->with(['equipment', 'method'])
            ->where('enable_depreciation', true)
            ->when($this->search, function ($q): void {
                $q->whereHas('equipment', function ($eq): void {
                    $eq->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('equipment_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('updated_at');

        return view('livewire.equipment.depreciation.depreciation-list-manager', [
            'configs' => $query->paginate($this->perPage),
        ]);
    }
}
