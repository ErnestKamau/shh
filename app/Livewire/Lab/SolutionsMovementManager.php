<?php

namespace App\Livewire\Lab;

use Livewire\Component;
use Livewire\WithPagination;
use App\LabSubCategory;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;

class SolutionsMovementManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination;

    // Search and Filter
    public $search = '';

    // UI State
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    public function getSubCategoriesProperty()
    {
        $query = LabSubCategory::with('category')
            ->where('active', 1);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $this->applyCaseInsensitiveSearch($q, ['name', 'description'], (string) $this->search);
                $q->orWhereHas('category', function ($catQ) {
                    $this->applyCaseInsensitiveSearch($catQ, ['name'], (string) $this->search);
                });
            });
        }

        return $query->orderBy('name', 'asc')->paginate($this->perPage);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.lab.solutions-movement-manager');
    }
}
