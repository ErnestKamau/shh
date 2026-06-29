<?php

namespace App\Livewire\Crm\Complaint\Tabs;

use App\Models\CRM\Complaintnotes;
use Livewire\Attributes\On;
use App\Livewire\Crm\BaseCrmComponent;
use Illuminate\Support\Facades\Auth;
use App\Exports\CRM\ComplaintTabExport;

class ComplaintNotesTab extends BaseCrmComponent
{
    public $complaintId;
    public $perPage = 10;
    public $newNote = '';
    public $noteType = '';
    public $isPublic = false;
    public $editingNoteId = null;
    public $editingNote = '';
    public $availableTypes = [];
    public $customNoteType = '';
    public $showCustomType = false;

    public function mount($complaintId)
    {
        $this->initialize();
        $this->complaintId = $complaintId;
        $this->loadAvailableTypes();
    }

    public function loadAvailableTypes()
    {
        $defaultTypes = ['Note to Self', 'Non-Conformity Issue', 'Reminder'];
        $dbTypes = Complaintnotes::whereNotNull('type')
            ->distinct()
            ->pluck('type')
            ->toArray();
        
        $this->availableTypes = array_unique(array_merge($defaultTypes, $dbTypes));
        sort($this->availableTypes);
    }

    public function updatedNoteType($value)
    {
        $this->showCustomType = ($value === 'Other');
        if (!$this->showCustomType) {
            $this->customNoteType = '';
        }
    }

    public function getNotesProperty()
    {
        return Complaintnotes::where('complaint_id', $this->complaintId)
            ->where('is_delete', '!=', 1)
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
    }

    public function exportToExcel()
    {
        $this->checkPermission('CRM.permission');
        return (new ComplaintTabExport($this->complaintId, 'notes'))->download('complaint_notes_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function openAddModal()
    {
        $this->newNote = '';
        $this->noteType = '';
        $this->customNoteType = '';
        $this->showCustomType = false;
        $this->isPublic = false;
        $this->editingNoteId = null;
        $this->dispatch('show-note-modal', []);
        $this->dispatch('reset-quill');
    }

    public function addNote()
    {
        $this->checkPermission('CRM.components.Complaints.Edit');
        
        $rules = [
            'newNote' => 'required|string',
            'noteType' => 'required|string',
        ];

        if ($this->noteType === 'Other') {
            $rules['customNoteType'] = 'required|string';
        }

        $this->validate($rules);

        $finalType = ($this->noteType === 'Other') ? $this->customNoteType : $this->noteType;

        $note = new Complaintnotes();
        $note->notes = $this->newNote;
        $note->created_by = Auth::user()->name;
        $note->complaint_id = $this->complaintId;
        $note->type = $finalType;
        $note->is_public = $this->isPublic; // Map checkbox value to database
        $note->save();

        $this->newNote = '';
        $this->noteType = '';
        $this->customNoteType = '';
        $this->showCustomType = false;
        $this->isPublic = false; // Reset checkbox
        $this->loadAvailableTypes(); // Refresh types in case a new one was added
        $this->showSuccess('Note added successfully');
        $this->dispatch('close-note-modal');
        $this->dispatch('note-added');
    }

    public function editNote($noteId)
    {
        $note = Complaintnotes::find($noteId);
        if ($note) {
            $this->editingNoteId = $note->id;
            $this->editingNote = $note->notes;
            
            // Check if type is in available types, otherwise it's effectively a custom type
            $this->noteType = $note->type;
            $this->showCustomType = false;
            $this->customNoteType = '';

            $this->isPublic = $note->is_public;
            $this->dispatch('show-note-modal', [
                'id' => $note->id,
                'note' => $note->notes,
                'type' => $note->type,
            ]);
            $this->dispatch('set-quill-content', content: $note->notes);
        }
    }

    public function updateNote()
    {
        if ($this->editingNoteId) {
            $this->checkPermission('CRM.components.Complaints.Edit');
            
            $rules = [
                'editingNote' => 'required|string',
                'noteType' => 'required|string',
            ];

            if ($this->noteType === 'Other') {
                $rules['customNoteType'] = 'required|string';
            }

            $this->validate($rules);

            $finalType = ($this->noteType === 'Other') ? $this->customNoteType : $this->noteType;

            $note = Complaintnotes::find($this->editingNoteId);
            $note->notes = $this->editingNote;
            $note->type = $finalType;
            $note->is_public = $this->isPublic;
            $note->save();

            $this->editingNoteId = null;
            $this->editingNote = '';
            $this->noteType = '';
            $this->customNoteType = '';
            $this->showCustomType = false;
            $this->loadAvailableTypes();
            $this->showSuccess('Note updated successfully');
            $this->dispatch('close-note-modal');
            $this->dispatch('note-updated');
        }
    }

    public function deleteNote($noteId)
    {
        $this->checkPermission('CRM.components.Complaints.Edit');
        
        $note = Complaintnotes::find($noteId);
        if ($note) {
            $note->is_delete = 1;
            $note->save();
            
            $this->showSuccess('Note deleted successfully');
            $this->dispatch('note-deleted');
        }
    }

    #[On('note-added')]
    #[On('note-updated')]
    #[On('note-deleted')]
    public function refreshNotes()
    {
        $this->loadAvailableTypes();
    }

    public function render()
    {
        return view('livewire.crm.complaint.tabs.complaint-notes-tab', [
            'notes' => $this->notes,
        ]);
    }
}
