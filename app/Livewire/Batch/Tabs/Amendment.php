<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use Livewire\Component;
use Livewire\WithPagination;

class Amendment extends Component
{
    use WithPagination;

    public SampleHeader $batch;
    public string $search = '';
    public int $perPage = 10;

    protected $paginationTheme = 'bootstrap';

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getAmendmentsProperty()
    {
        return $this->batch->batch_amendments()
            ->with(['creator'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('samples', 'like', '%' . $this->search . '%')
                      ->orWhere('reason', 'like', '%' . $this->search . '%')
                      ->orWhereHas('creator', function($creator) {
                          $creator->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.amendment', [
            'amendments' => $this->amendments
        ]);
    }
}
