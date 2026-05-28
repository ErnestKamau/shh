<?php

namespace App\Livewire\SkillsMatrix;

use App\Livewire\SkillsMatrix\Concerns\HasMatrixFlash;
use App\Livewire\SkillsMatrix\Concerns\HasMatrixListFilters;
use App\Models\SkillsMatrix\TrainingHeader;
use App\Models\SkillsMatrix\TrainingPlannerHeader;
use App\Services\SkillsMatrix\TrainingNeedsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TrainingPlanManager extends Component
{
    use HasMatrixFlash;
    use HasMatrixListFilters;

    public bool $showModal = false;

    public string $planName = '';

    public string $trainingNeedHeaderId = '';

    public function openCreate(): void
    {
        $this->planName = '';
        $this->trainingNeedHeaderId = '';
        $this->showModal = true;
    }

    public function savePlan(): void
    {
        $this->authorize('skills-matrix.components.training-plan.add');
        $this->validate(['planName' => 'required', 'trainingNeedHeaderId' => 'required']);

        $trainingNeed = TrainingHeader::findOrFail($this->trainingNeedHeaderId);

        app(TrainingNeedsService::class)->createTrainingPlanFromNeed(
            $trainingNeed,
            $this->planName,
            Auth::user(),
        );

        $this->showModal = false;
        $this->flash('Training plan created.');
    }

    public function render()
    {
        $query = TrainingPlannerHeader::query()
            ->with('trainneed')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                    ->orWhereHas('trainneed', fn ($sq) => $sq->where('name', 'like', $term));
            });
        }

        $plans = $query->paginate($this->perPage);
        $needs = TrainingHeader::whereNull('deleted_at')->orderBy('name')->get();

        return view('livewire.skills-matrix.training-plan-manager', compact('plans', 'needs'));
    }
}
