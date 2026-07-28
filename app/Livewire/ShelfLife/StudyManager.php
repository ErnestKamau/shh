<?php

namespace App\Livewire\ShelfLife;

use App\Models\ShelfLife\ShelfLifeStudy;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\Services\ShelfLife\ShelfLifeStudyBootstrapService;
use Livewire\Component;
use Livewire\WithPagination;

class StudyManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $studyTypeFilter = '';

    public int $perPage = 15;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStudyTypeFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = ShelfLifeStudy::query()
            ->with(['customer', 'sampleHeader', 'pullPoints', 'parameterSpecs'])
            ->latest();

        if ($this->search !== '') {
            $query->where(function ($inner): void {
                $this->applyCaseInsensitiveSearch($inner, ['code', 'title', 'batch_lot_no'], (string) $this->search);
                $inner->orWhereHas('sampleHeader', function ($q): void {
                    $this->applyCaseInsensitiveSearch($q, ['batch_code'], (string) $this->search);
                })
                    ->orWhereHas('customer', function ($q): void {
                        $this->applyCaseInsensitiveSearch($q, ['name'], (string) $this->search);
                    });
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->studyTypeFilter !== '') {
            $query->where('study_type', $this->studyTypeFilter);
        }

        return view('livewire.shelf-life.study-manager', [
            'studies' => $query->paginate($this->perPage),
            'statusOptions' => [
                ShelfLifeStudy::STATUS_DRAFT => 'Draft',
                ShelfLifeStudy::STATUS_ACTIVE => 'Active',
                ShelfLifeStudy::STATUS_ON_HOLD => 'On hold',
                ShelfLifeStudy::STATUS_COMPLETED => 'Completed',
                ShelfLifeStudy::STATUS_ABORTED => 'Aborted',
            ],
            'studyTypeOptions' => [
                ShelfLifeStudy::TYPE_REAL_TIME => 'Real-time',
                ShelfLifeStudy::TYPE_ACCELERATED => 'Accelerated',
            ],
            'unassignedShelfLifeJobs' => $this->unassignedShelfLifeJobsCount(),
        ]);
    }

    private function unassignedShelfLifeJobsCount(): int
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('sample_headers', 'is_shelf_life')) {
            return 0;
        }

        return \App\SampleHeader::query()
            ->where('is_shelf_life', true)
            ->where('status', ShelfLifeStudyBootstrapService::BATCH_STATUS)
            ->whereDoesntHave('shelfLifeStudy')
            ->count();
    }
}
