<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use App\Services\Sampleworkflow\BatchCustodyTimelineBuilder;
use App\Services\Sampleworkflow\BatchWorkflowStageSyncService;
use Livewire\Component;
use Livewire\WithPagination;

class ChainOfCustody extends Component
{
    use WithPagination;

    public SampleHeader $batch;

    public string $search = '';

    public int $perPage = 10;

    protected $listeners = ['custodyUpdated' => '$refresh'];

    protected $paginationTheme = 'bootstrap';

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;

        // Heal rows skipped when workflow status changed without CoC (e.g. Livewire verification).
        if (app(BatchWorkflowStageSyncService::class)->ensureOpenCustodyMatchesWorkflow($this->batch)) {
            $this->batch->refresh();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getCustodyRecordsProperty()
    {
        $sorted = app(BatchCustodyTimelineBuilder::class)->build($this->batch, $this->search);

        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = $this->perPage;

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $sorted->forPage($currentPage, $perPage),
            $sorted->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.chain-of-custody', [
            'custodyRecords' => $this->custodyRecords,
        ]);
    }
}
