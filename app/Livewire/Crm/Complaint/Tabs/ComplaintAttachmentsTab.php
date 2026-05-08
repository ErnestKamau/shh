<?php

namespace App\Livewire\Crm\Complaint\Tabs;

use App\Models\CRM\Complaintattachment;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Livewire\Crm\BaseCrmComponent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Exports\CRM\ComplaintTabExport;

class ComplaintAttachmentsTab extends BaseCrmComponent
{
    use WithFileUploads;

    public $complaintId;
    public $perPage = 10;
    public $attachmentFile;
    public $title = '';
    public $type = '';
    public $isPublic = false;
    public $description = '';
    public $customType = '';
    public $editingAttachmentId = null;
    public $attachmentIdToDelete = null;
    public $typeSearch = '';
    public $showTypeDropdown = false;
    
    public $attachmentTypes = [
        'Closure Report',
        'Supporting Document',
        'Evidence Photo',
        'Email Correspondence',
    ];

    public $availableAttachmentTypes = [];

    public function mount($complaintId)
    {
        $this->initialize();
        $this->complaintId = $complaintId;
        $this->loadAvailableAttachmentTypes();
    }

    public function loadAvailableAttachmentTypes()
    {
        $dbTypes = Complaintattachment::whereNotNull('type')
            ->where('is_delete', '!=', 1)
            ->distinct()
            ->pluck('type')
            ->toArray();
        
        $this->availableAttachmentTypes = array_unique(array_merge($this->attachmentTypes, $dbTypes));
        sort($this->availableAttachmentTypes);
        $this->dispatch('attachment-types-updated');
    }

    public function getSelectedTypeProperty()
    {
        if (empty($this->type)) {
            return null;
        }

        return $this->type;
    }

    public function getFilteredTypeOptionsProperty()
    {
        $search = strtolower(trim($this->typeSearch));
        $options = array_values(array_unique(array_merge($this->availableAttachmentTypes, ['Other'])));

        return collect($options)
            ->filter(function ($option) use ($search) {
                if ($option === $this->type) {
                    return false;
                }

                if ($search === '') {
                    return true;
                }

                return str_contains(strtolower($option), $search);
            })
            ->values();
    }

    public function selectType($value)
    {
        $this->type = $value;
        $this->typeSearch = '';
        $this->showTypeDropdown = false;

        if ($value !== 'Other') {
            $this->customType = '';
        }
    }

    public function clearType()
    {
        $this->type = '';
        $this->customType = '';
        $this->typeSearch = '';
        $this->showTypeDropdown = false;
    }

    public function getAttachmentsProperty()
    {
        return Complaintattachment::where('complaint_id', $this->complaintId)
            ->where('is_delete', '!=', 1)
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
    }

    public function exportToExcel()
    {
        $this->checkPermission('crm.permission');
        return (new ComplaintTabExport($this->complaintId, 'attachments'))->download('complaint_attachments_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function openAttachmentModal()
    {
        $this->reset(['title', 'type', 'customType', 'description', 'attachmentFile', 'isPublic', 'editingAttachmentId', 'typeSearch', 'showTypeDropdown']);
        $this->dispatch('show-attachment-modal');
    }

    public function editAttachment($id)
    {
        $this->checkPermission('crm.components.complaints.edit');
        
        $attachment = Complaintattachment::find($id);
        
        if ($attachment) {
            $this->editingAttachmentId = $attachment->id;
            $this->title = $attachment->title;
            
            if (in_array($attachment->type, $this->attachmentTypes)) {
                $this->type = $attachment->type;
                $this->customType = '';
            } else {
                $this->type = 'Other';
                $this->customType = $attachment->type;
            }
            
            $this->description = $attachment->description;
            $this->isPublic = (bool)$attachment->is_public;
            $this->typeSearch = '';
            $this->showTypeDropdown = false;
            
            $this->dispatch('show-attachment-modal');
        }
    }

    public function uploadAttachment()
    {
        $this->checkPermission('crm.components.complaints.edit');
        
        $this->validate([
            'attachmentFile' => 'required|file|max:5120',
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'customType' => 'required_if:type,Other|nullable|string|max:255',
        ]);

        $attachment = new Complaintattachment();
        $attachment->title = $this->title;
        $attachment->type = ($this->type === 'Other') ? $this->customType : $this->type;
        $attachment->description = $this->description;
        $attachment->complaint_id = $this->complaintId;
        $attachment->posted_by = Auth::user()->name;
        $attachment->is_public = $this->isPublic;

        if ($this->attachmentFile) {
            $path = $this->attachmentFile->store('complaints', 'public');
            $fname = '/storage/' . $path;
            $attachment->file_path = $fname;
        }

        $attachment->save();

        $this->reset(['title', 'type', 'customType', 'description', 'attachmentFile', 'isPublic', 'editingAttachmentId', 'typeSearch', 'showTypeDropdown']);
        $this->loadAvailableAttachmentTypes();
        
        $this->showSuccess('Attachment added successfully');
        $this->dispatch('close-attachment-modal');
        $this->dispatch('attachment-added');
    }

    public function updateAttachment()
    {
        $this->checkPermission('crm.components.complaints.edit');
        
        $this->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'customType' => 'required_if:type,Other|nullable|string|max:255',
            'attachmentFile' => 'nullable|file|max:5120', 
        ]);

        $attachment = Complaintattachment::find($this->editingAttachmentId);

        if (!$attachment) {
            return;
        }

        $attachment->title = $this->title;
        $attachment->type = ($this->type === 'Other') ? $this->customType : $this->type;
        $attachment->description = $this->description;
        $attachment->is_public = $this->isPublic;

        if ($this->attachmentFile) {
            if ($attachment->file_path && Storage::disk('public')->exists(str_replace('/storage/', '', $attachment->file_path))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $attachment->file_path));
            }

            $path = $this->attachmentFile->store('complaints', 'public');
            $fname = '/storage/' . $path;
            $attachment->file_path = $fname;
        }

        $attachment->save();

        $this->reset(['title', 'type', 'customType', 'description', 'attachmentFile', 'isPublic', 'editingAttachmentId', 'typeSearch', 'showTypeDropdown']);
        $this->loadAvailableAttachmentTypes();

        $this->showSuccess('Attachment updated successfully');
        $this->dispatch('close-attachment-modal');
        $this->dispatch('attachment-added');
    }

    public function confirmDelete($id)
    {
        $this->checkPermission('crm.components.complaints.edit');
        $this->attachmentIdToDelete = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function deleteAttachment()
    {
        $this->checkPermission('crm.components.complaints.edit');
        
        $attachment = Complaintattachment::find($this->attachmentIdToDelete);
        if ($attachment) {
            if ($attachment->file_path && Storage::disk('public')->exists(str_replace('/storage/', '', $attachment->file_path))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $attachment->file_path));
            }
            $attachment->is_delete = 1;
            $attachment->save();
            
            $this->showSuccess('Attachment deleted successfully');
            $this->dispatch('close-delete-confirmation');
            $this->dispatch('attachment-deleted');
            $this->attachmentIdToDelete = null;
        }
    }

    #[On('attachment-added')]
    #[On('attachment-deleted')]
    public function refreshAttachments()
    {
        $this->loadAvailableAttachmentTypes();
    }

    public function render()
    {
        return view('livewire.crm.complaint.tabs.complaint-attachments-tab', [
            'attachments' => $this->attachments,
        ]);
    }
}
