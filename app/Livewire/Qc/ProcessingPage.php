<?php

namespace App\Livewire\Qc;

use App\Models\QcModule\Data\QcResults;
use App\Services\Qc\QcStatisticsService;
use Livewire\Component;
use Livewire\WithPagination;

class ProcessingPage extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public int $perPage = 50;

    public function processResults(QcStatisticsService $qcStatisticsService): void
    {
        $processed = $qcStatisticsService->processAllUnprocessed();

        if ($processed === 0) {
            session()->flash('success', 'No unprocessed QC results were found.');

            return;
        }

        session()->flash('success', 'Unprocessed QC results have been processed successfully.');
        $this->resetPage();
    }

    public function render()
    {
        $results = QcResults::query()
            ->where('is_qc_processed', 0)
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.qc.processing-page', [
            'results' => $results,
        ]);
    }
}
