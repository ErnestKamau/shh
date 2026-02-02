<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use Livewire\Component;
use Livewire\WithPagination;

class Approvals extends Component
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

    public function getApproversProperty()
    {
        return $this->batch->approvers()
            ->with(['lab_sections'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('approvername', 'like', '%' . $this->search . '%')
                      ->orWhere('title', 'like', '%' . $this->search . '%')
                      ->orWhere('batch_status', 'like', '%' . $this->search . '%')
                      ->orWhere('remark', 'like', '%' . $this->search . '%')
                      ->orWhere('workflow', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.approvals', [
            'approvers' => $this->approvers
        ]);
    }
}
