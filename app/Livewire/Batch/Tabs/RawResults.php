<?php

namespace App\Livewire\Batch\Tabs;

use App\CapturedResult;
use App\SampleHeader;
use App\Services\StandardLimitDisplayService;
use Livewire\Component;

class RawResults extends Component
{
    public SampleHeader $batch;

    protected $listeners = ['resultsUpdated' => '$refresh'];

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
    }

    public function render(): \Illuminate\View\View
    {
        $limitDisplay = app(StandardLimitDisplayService::class);

        $rawResults = CapturedResult::with(['sample', 'analysis_type', 'operator'])
            ->where('sample_header_id', $this->batch->id)
            ->orderBy('sample_detail_id', 'ASC')
            ->get()
            ->each(function (CapturedResult $result) use ($limitDisplay): void {
                $result->setAttribute(
                    'standard_limit_display',
                    $limitDisplay->forCapturedResult($result)
                );
            });

        return view('livewire.batch.tabs.raw-results', [
            'rawResults' => $rawResults,
        ]);
    }
}
