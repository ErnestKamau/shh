<?php

namespace App\Livewire\Registry;

use App\DTOs\Registry\RegistryFilterDTO;
use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestCategory;
use App\Repositories\Registry\RegistryRequestRepository;
use Livewire\Component;
use Livewire\WithPagination;

class RegistryApprovedRequestTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $categoryId = '';

    public string $direction = '';

    protected $queryString = ['search', 'categoryId', 'direction'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatingDirection(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoryId = '';
        $this->direction = '';
        $this->resetPage();
    }

    public function render(RegistryRequestRepository $repository)
    {
        $filter = new RegistryFilterDTO(
            status: RegistryRequest::STATUS_CLOSED,
            categoryId: $this->categoryId !== '' ? $this->categoryId : null,
            direction: $this->direction !== '' ? $this->direction : null,
            search: $this->search !== '' ? $this->search : null,
        );

        $requests = $repository->paginate($filter, 15);
        $categories = RegistryRequestCategory::query()->forCompany()->active()->orderBy('name')->get();

        return view('livewire.registry.registry-approved-request-table', compact('requests', 'categories'));
    }
}
