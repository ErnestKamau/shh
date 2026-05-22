<?php

namespace App\Livewire\Personnel\Tabs;

use App\Models\Personnel\PersonnelAttachment;
use App\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PersonnelAttachmentsTab extends Component
{
    use WithFileUploads, WithPagination;

    public User $user;
    public $attachmentFile;
    public $fileName = '';
    public $showUploadModal = false;

    protected $paginationTheme = 'bootstrap';
    
    protected $rules = [
        'attachmentFile' => 'required|file|max:10240', // 10MB Max
        'fileName' => 'required|string|max:255',
    ];

    public function mount(User $user)
    {
        $this->user = $user;
    }

    public function openUploadModal()
    {
        $this->reset(['attachmentFile', 'fileName']);
        $this->resetValidation();
        $this->showUploadModal = true;
    }

    public function closeUploadModal()
    {
        $this->showUploadModal = false;
        $this->reset(['attachmentFile', 'fileName']);
    }

    public function uploadAttachment()
    {
        $this->validate();

        $path = $this->attachmentFile->store('personnel-attachments', 'public');
        
        PersonnelAttachment::create([
            'user_id' => $this->user->id,
            'file_name' => $this->fileName,
            'file_path' => $path,
            'uploaded_by' => auth()->id(),
        ]);

        $this->closeUploadModal();
        session()->flash('message', 'Attachment uploaded successfully.');
        $this->resetPage();
    }

    public function deleteAttachment($id)
    {
        $attachment = PersonnelAttachment::where('user_id', $this->user->id)->findOrFail($id);
        
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();
        session()->flash('message', 'Attachment deleted successfully.');
    }

    public function render()
    {
        $attachments = PersonnelAttachment::where('user_id', $this->user->id)
            ->with('uploader')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.personnel.tabs.personnel-attachments-tab', [
            'attachments' => $attachments,
        ]);
    }
}
