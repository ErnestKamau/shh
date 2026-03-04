<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use App\BatchAttachment;
use App\Models\System\SystemConfiguration;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

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
        $query = BatchAttachment::where('batch_id', $this->batch->id)
            ->with('annotations'); // Eager load annotations for performance

        // Filter for clients: only show non-internal attachments
        if (Auth::user()->is_client == 1) {
            $query->where('is_internal', 0);
        }

        // Search functionality
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                    ->orWhereHas('uploader', function ($uploader) {
                        $uploader->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getAttachmentTypesProperty()
    {
        return SystemConfiguration::where('key', 'attachment_type')->get();
    }

    public function deleteAttachment($attachmentId)
    {
        $attachment = BatchAttachment::find($attachmentId);
        if ($attachment) {
            // Delete the physical file
            $relativePath = urldecode($attachment->attachment_url);
            $filePath = public_path($relativePath);

            if (!file_exists($filePath)) {
                $cleanPath = ltrim($relativePath, '/');
                if (strpos($cleanPath, 'storage/') === 0) {
                    $storageInternalPath = substr($cleanPath, 8);
                    $fallbackPath = storage_path('app/' . $storageInternalPath);
                    if (file_exists($fallbackPath)) {
                        $filePath = $fallbackPath;
                    }
                }
            }

            if (file_exists($filePath)) {
                try {
                    unlink($filePath);
                    \Log::info("Deleted attachment file: {$filePath}");
                } catch (\Exception $e) {
                    \Log::error("Failed to delete attachment file: {$filePath}. Error: " . $e->getMessage());
                }
            }

            $attachment->delete();
            $this->dispatch('attachmentsUpdated');
            session()->flash('success', 'Attachment deleted successfully!');
        } else {
            session()->flash('error', 'Attachment not found.');
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.attachments', [
            'attachments' => $this->attachments,
            'attachmentTypes' => $this->attachmentTypes
        ]);
    }
}
