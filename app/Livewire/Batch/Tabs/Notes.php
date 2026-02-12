<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use Livewire\Component;
use Livewire\WithPagination;

class Notes extends Component
{
    use WithPagination;

    public SampleHeader $batch;
    public string $search = '';
    public int $perPage = 10;

    protected $listeners = ['notesUpdated' => '$refresh'];
    protected $paginationTheme = 'bootstrap';

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getCommentsProperty()
    {
        return $this->batch->comments()
            ->with(['creator', 'reminderRecipient'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('comments', 'like', '%' . $this->search . '%')
                      ->orWhere('comment_type', 'like', '%' . $this->search . '%')
                      ->orWhereHas('creator', function($creator) {
                          $creator->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('reminderRecipient', function($reminder) {
                          $reminder->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.notes', [
            'comments' => $this->comments
        ]);
    }
}
