<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use App\InterLabLog;
use Livewire\Component;
use Livewire\WithPagination;

class InterlabLogs extends Component
{
    use WithPagination;
    
    public SampleHeader $batch;
    public $search = '';
    public $perPage = 10;

    protected $listeners = ['interlabLogsUpdated' => '$refresh'];
    
    protected $paginationTheme = 'bootstrap';

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function getInterlabLogsProperty()
    {
        // Get all samples for this batch
        $sampleIds = $this->batch->samples()->pluck('id');
        
        return InterLabLog::whereIn('sample_id', $sampleIds)
            ->with(['sample', 'from_lab', 'to_lab', 'submitter'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->whereHas('sample', function($sq) {
                        $sq->where('sample_code', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('from_lab', function($fl) {
                        $fl->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('to_lab', function($tl) {
                        $tl->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('submitter', function($sub) {
                        $sub->where('name', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->orderBy('date_submitted', 'desc')
            ->paginate($this->perPage);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.interlab-logs', [
            'interlabLogs' => $this->interlabLogs,
        ]);
    }
}
