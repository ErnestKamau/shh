<?php

namespace App\Livewire\Qc;

use App\Models\QcModule\QCProcessedResults;
use Livewire\Component;
use Livewire\WithPagination;

class ReportsPage extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public int $perPage = 50;

    public function render()
    {
        $results = QCProcessedResults::query()
            ->with(['method', 'sampletype', 'analysistype', 'analyte', 'results'])
            ->orderByDesc('updated_at')
            ->paginate($this->perPage);

        return view('livewire.qc.reports-page', [
            'results' => $results,
        ]);
    }
}
