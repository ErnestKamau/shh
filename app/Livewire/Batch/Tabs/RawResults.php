<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use App\CapturedResult;
use Livewire\Component;

class RawResults extends Component
{
    public SampleHeader $batch;

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
    }

    public function render(): \Illuminate\View\View
    {
        // Get raw results for this batch (same query as in SampleWorkFlowController)
        $rawResults = CapturedResult::with(['sample', 'analysis_type', 'operator'])
            ->where('sample_header_id', $this->batch->id)
            ->orderBy('sample_detail_id', 'ASC')
            ->get();
        
        return view('livewire.batch.tabs.raw-results', [
            'rawResults' => $rawResults,
        ]);
    }
}
