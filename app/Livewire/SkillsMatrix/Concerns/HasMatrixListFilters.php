<?php

namespace App\Livewire\SkillsMatrix\Concerns;

use Livewire\WithPagination;

trait HasMatrixListFilters
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 25;

    /** @var array<int, int> */
    public array $perPageOptions = [25, 50, 75, 100];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function clearListFilters(): void
    {
        $this->search = '';
        $this->resetPage();
        $this->afterClearListFilters();
    }

    protected function afterClearListFilters(): void
    {
        //
    }
}
