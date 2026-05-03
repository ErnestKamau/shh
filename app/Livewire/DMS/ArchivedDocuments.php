<?php

namespace App\Livewire\DMS;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentType;
use App\Models\DMS\DocumentAuditLog;
use App\User;
use Illuminate\Support\Facades\Gate;

class ArchivedDocuments extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = null;
    public $ownerFilter = null;
    public $perPage = 10;
    
    public $message = '';
    public $messageType = 'success';

    public $documentTypes = [];
    public $users = [];
    public $perPageOptions = [10, 25, 50, 100];

    public function mount(): void
    {
        $this->documentTypes = DocumentType::active()->orderBy('name')->get();
        $this->users = User::where('active', true)->orderBy('name')->get();
    }

    public function getDocumentsProperty()
    {
        $query = Document::with(['documentType', 'owner', 'archiver'])
            ->archived();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('document_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->typeFilter) {
            $query->where('document_type_id', $this->typeFilter);
        }

        if ($this->ownerFilter) {
            $query->where('owner_id', $this->ownerFilter);
        }

        return $query->orderBy('archived_at', 'desc')->paginate($this->perPage);
    }

    public function restoreDocument($documentId): void
    {
        try {
            $document = Document::findOrFail($documentId);

            if (!Gate::forUser(auth()->user())->allows('restore', $document)) {
                $this->message = 'You do not have permission to restore this document';
                $this->messageType = 'error';
                return;
            }

            $document->restore();

            DocumentAuditLog::log(
                $document,
                'restored',
                null,
                null,
                'Document restored from archive'
            );

            $this->message = 'Document restored successfully';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            $this->message = 'Error restoring document: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->typeFilter = null;
        $this->ownerFilter = null;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.dms.archived-documents-component', [
            'documents' => $this->documents,
        ]);
    }
}

