<?php

namespace App\Livewire\SkillsMatrix;

use App\Livewire\SkillsMatrix\Concerns\HasMatrixFlash;
use App\Livewire\SkillsMatrix\Concerns\HasMatrixListFilters;
use App\Models\SkillsMatrix\CapabilityMatrix;
use App\Models\SkillsMatrix\TrainingHeader;
use App\Services\SkillsMatrix\TrainingNeedsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TrainingNeedsManager extends Component
{
    use HasMatrixFlash;
    use HasMatrixListFilters;

    public bool $showModal = false;

    public string $name = '';

    public string $capabilityId = '';

    /** @var array<int, string> */
    public array $userRoleIds = [];

    public function openCreate(): void
    {
        $this->reset(['name', 'capabilityId', 'userRoleIds']);
        $this->showModal = true;
    }

    public function saveTrainingNeed(): void
    {
        $this->authorize('skills-matrix.components.training-needs.add');
        $this->validate([
            'name' => 'required',
            'capabilityId' => 'required',
            'userRoleIds' => 'required|array|min:1',
        ]);

        $capability = CapabilityMatrix::findOrFail($this->capabilityId);

        app(TrainingNeedsService::class)->createTrainingNeedForCapability(
            $capability,
            $this->name,
            Auth::user(),
            $this->userRoleIds,
        );

        $this->showModal = false;
        $this->flash('Training needs assessment created.');
    }

    public function render()
    {
        $query = TrainingHeader::query()
            ->with('capability')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                    ->orWhereHas('capability', fn ($sq) => $sq->where('name', 'like', $term));
            });
        }

        $trainings = $query->paginate($this->perPage);
        $capabilities = CapabilityMatrix::whereNull('deleted_at')->where('status', 1)->orderBy('name')->get();

        return view('livewire.skills-matrix.training-needs-manager', compact('trainings', 'capabilities'));
    }
}
