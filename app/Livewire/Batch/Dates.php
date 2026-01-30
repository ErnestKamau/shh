<?php

namespace App\Livewire\Batch;

use App\SampleHeader;
use Livewire\Component;

class Dates extends Component
{
    public SampleHeader $batch;

    protected $listeners = ['batchUpdated' => '$refresh'];

    public function mount(SampleHeader $batch)
    {
        $this->batch = $batch;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.dates');
    }
}
