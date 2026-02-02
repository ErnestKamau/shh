<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use Livewire\Component;
use Livewire\WithPagination;

class Attachments extends Component
{
    use WithPagination;

    public SampleHeader $batch;
    public string $search = '';
    public int $perPage = 10;

    protected $listeners = ['attachmentsUpdated' => '$refresh'];
    protected $paginationTheme = 'bootstrap';

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getAttachmentsProperty()
    {
        return $this->batch->batch_attachments()
            ->with(['uploader'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('file_name', 'like', '%' . $this->search . '%')
                      ->orWhere('file_type', 'like', '%' . $this->search . '%')
                      ->orWhereHas('uploader', function($uploader) {
                          $uploader->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.attachments', [
            'attachments' => $this->attachments
        ]);
    }
}
