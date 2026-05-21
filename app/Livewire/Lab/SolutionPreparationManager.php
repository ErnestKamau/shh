<?php

namespace App\Livewire\Lab;

use App\LabSubCategory;
use App\Models\SolutionPreparation;
use Livewire\Component;
use Livewire\WithPagination;

class SolutionPreparationManager extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = '';

    public $solutionFilter = '';

    public $perPage = 25;

    protected $queryString = ['search', 'statusFilter', 'solutionFilter'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getPreparationsProperty()
    {
        return SolutionPreparation::query()
            ->with(['solution', 'preparer'])
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('preparation_number', 'like', '%'.$this->search.'%')
                        ->orWhere('batch_number', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->solutionFilter, fn ($q) => $q->where('solution_id', $this->solutionFilter))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    public function getSolutionsProperty()
    {
        return LabSubCategory::where('active', 1)->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.lab.solution-preparation-manager');
    }
}
