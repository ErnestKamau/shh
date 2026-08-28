<?php

namespace App\Livewire\Batch\Tabs;

use App\Models\TestRequestReportRevision;
use App\SampleHeader;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class ProcessedResults extends Component
{
    use WithPagination;

    public SampleHeader $batch;
    public string $search = '';
    public int $perPage = 10;

    protected $paginationTheme = 'bootstrap';

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getProcessedResultsProperty()
    {
        if (! $this->batchHasOfficialTestReport()) {
            return new LengthAwarePaginator([], 0, $this->perPage, 1);
        }

        return \App\Result::with(['captured', 'captured.sample', 'captured.analysis_type', 'captured.operator'])
            ->where('sample_header_id', $this->batch->id)
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('result', 'like', '%' . $this->search . '%')
                      ->orWhere('remarks', 'like', '%' . $this->search . '%')
                      ->orWhereHas('captured.sample', function($sample) {
                          $sample->where('sample_code', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('captured.analysis_type', function($analysis) {
                          $analysis->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy('sample_detail_id', 'asc')
            ->paginate($this->perPage);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.processed-results', [
            'processedResults' => $this->processedResults,
            'hasOfficialTestReport' => $this->batchHasOfficialTestReport(),
        ]);
    }

    private function batchHasOfficialTestReport(): bool
    {
        $sequence = (int) SampleHeader::query()
            ->whereKey($this->batch->id)
            ->value('test_request_report_sequence');

        if ($sequence > 0) {
            return true;
        }

        return TestRequestReportRevision::query()
            ->where('batch_id', $this->batch->id)
            ->exists();
    }
}
