<?php

namespace App\Livewire\Batch;

use App\SampleHeader;
use Livewire\Component;

class Tabs extends Component
{
    public SampleHeader $batch;
    public $activeTab = 'samples';
    
    // Additional data passed from controller
    public $not_captured;
    public $status;

    protected $listeners = ['batchUpdated' => '$refresh', 'samplesUpdated' => '$refresh'];

    public function mount(SampleHeader $batch, $not_captured = null, $status = null)
    {
        $this->batch = $batch;
        $this->not_captured = $not_captured;
        $this->status = $status;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs');
    }
}
