<?php

namespace App\Livewire\Batch;

use App\SampleHeader;
use Livewire\Component;

class Related extends Component
{
    public SampleHeader $batch;
    public $relatedBatches;

    public function mount(SampleHeader $batch)
    {
        $this->batch = $batch;
        $this->relatedBatches = collect();
        
        if ($batch->hasSubmissionForm()) {
            $this->relatedBatches = $batch->submissionFormInstance->batches
                ->where('id', '!=', $batch->id);
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.related');
    }
}
