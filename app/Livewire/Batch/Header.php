<?php

namespace App\Livewire\Batch;

use App\SampleHeader;
use App\CapturedResult;
use Livewire\Component;

class Header extends Component
{
    public SampleHeader $batch;
    public $workflows = [];
    public $workflowstages = [];
    public $status;
    public $defaultClient;
    public $clientPortal;
    public $notCaptured;

    protected $listeners = ['batchUpdated' => '$refresh'];

    public function mount(SampleHeader $batch, $workflows = [], $workflowstages = [], $status = null, $defaultClient = false, $clientPortal = false)
    {
        $this->batch = $batch;
        $this->workflows = $workflows;
        $this->workflowstages = $workflowstages;
        $this->status = $status;
        $this->defaultClient = $defaultClient;
        $this->clientPortal = $clientPortal;
        
        // Get uncaptured results for this batch
        $this->notCaptured = CapturedResult::whereNull('result')
            ->where('sample_header_id', $batch->id)
            ->get();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.header');
    }
}
