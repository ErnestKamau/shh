<?php

namespace App\Livewire\Lab;

use Livewire\Component;
use Livewire\WithPagination;
use App\LabSubCategory;

class SolutionsMovementManager extends Component
{
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
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('category', function($catQ) {
                      $catQ->where('name', 'like', '%' . $this->search . '%');
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
