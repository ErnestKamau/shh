<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use Livewire\Component;
use Livewire\WithPagination;

class ChainOfCustody extends Component
{
    use WithPagination;

    public SampleHeader $batch;
    public string $search = '';
    public int $perPage = 10;

    protected $listeners = ['custodyUpdated' => '$refresh'];
    protected $paginationTheme = 'bootstrap';

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getCustodyRecordsProperty()
    {
        return $this->batch->custody()
            ->with(['started_by', 'completed_by'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('purpose', 'like', '%' . $this->search . '%')
                      ->orWhereHas('started_by', function($user) {
                          $user->where('name', 'like', '%'  . $this->search . '%');
                      })
                      ->orWhereHas('completed_by', function($user) {
                          $user->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.chain-of-custody', [
            'custodyRecords' => $this->custodyRecords
        ]);
    }
}
