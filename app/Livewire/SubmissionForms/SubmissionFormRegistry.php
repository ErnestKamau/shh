<?php

namespace App\Livewire\SubmissionForms;

use App\Models\SubmissionForm;
use App\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class SubmissionFormRegistry extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $creatorId = '';

    public int $perPage = 15;

    /** @var array<int> */
    public array $perPageOptions = [10, 15, 25, 50, 100];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCreatorId(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->creatorId = '';
        $this->resetPage();
    }

    /**
     * Paginated submission forms listing (same filtering semantics as SubmissionFormController@index).
     */
    public function getFormsProperty(): LengthAwarePaginator
    {
        $query = SubmissionForm::query()
            ->with(['creator', 'sections', 'sampleAnalysisStages'])
            ->withCount(['sections', 'instances'])
            ->forCompany($this->currentCompanyId());

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($this->statusFilter !== '') {
            $status = $this->statusFilter;
            if ($status === 'published') {
                $query->where('is_published', true);
            } elseif ($status === 'draft') {
                $query->where('is_published', false);
            } elseif ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($this->creatorId !== '') {
            $query->where('created_by', $this->creatorId);
        }

        return $query->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    /**
     * @return Collection<int, User>
     */
    public function getCreatorOptionsProperty(): Collection
    {
        $ids = SubmissionForm::query()
            ->forCompany($this->currentCompanyId())
            ->whereNotNull('created_by')
            ->distinct()
            ->pluck('created_by');

        return User::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function currentCompanyId(): ?string
    {
        $companyId = function_exists('getUserCompany') ? getUserCompany() : null;

        if ($companyId === null || $companyId === '') {
            return null;
        }

        return (string) $companyId;
    }

    public function render(): View
    {
        return view('livewire.submission-forms.submission-form-registry');
    }
}
