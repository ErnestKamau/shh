<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use Livewire\Component;

class Samples extends Component
{
    public SampleHeader $batch;

    protected $listeners = ['samplesUpdated' => '$refresh'];

    public function mount(SampleHeader $batch)
    {
        $this->batch = $batch;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.samples');
    }
}
