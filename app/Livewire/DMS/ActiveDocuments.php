<?php

namespace App\Livewire\DMS;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentType;
use App\Models\DMS\DocumentAuditLog;
use App\Services\DMS\DocumentNumberGenerator;
use App\Services\DMS\PermissionManager;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Auth\Role as SpatieRole;
use App\Models\Auth\Permission as SpatiePermission;
use App\Services\Documents\DocumentKnowledgeService;

class ActiveDocuments extends Component
{
    use WithPagination, WithFileUploads;

    public $showModal = false;
    public $editingDocument = null;
    public $file;
    
    public $documentForm = [
        'document_type_id' => null,
        'title' => '',
        'description' => '',
        'owner_id' => null,
        'expiry_date' => null,
        'tags' => [],
        'status' => 'draft',
        'permissions' => [
            'roles' => [],
            'users' => [],
        ],
        'is_kb_indexed' => false,
        'kb_collection' => 'General Documents',
        'kb_required_permission' => 'general.view',
        'kb_chunk_size' => 800,
        'kb_chunk_overlap' => 100,
        'kb_content' => '',
    ];

    public $search = '';
    public $typeFilter = null;
    public $ownerFilter = null;
    public $statusFilter = '';
    public $kbFilter = '';
    public $expiringFilter = false;
    public $perPage = 10;
    
    public $message = '';
    public $messageType = 'success';

    public $documentTypes = [];
    public $users = [];
    public $roles = [];
    public $spatiePermissions = [];
    public $availableUsers = [];
    public $selectedRole = null;
    public $selectedRoles = [];
    public $userPermissions = [];
    public $inheritedPermissions = [];

    public $perPageOptions = [10, 25, 50, 100];

    protected $numberGenerator;
    protected $permissionManager;
    protected $knowledgeService;

    public function boot(DocumentNumberGenerator $numberGenerator, PermissionManager $permissionManager, DocumentKnowledgeService $knowledgeService)
    {
        $this->numberGenerator = $numberGenerator;
        $this->permissionManager = $permissionManager;
        $this->knowledgeService = $knowledgeService;
    }

    public function mount(): void
    {
        $this->loadDocumentTypes();
        $this->loadUsers();
        $this->loadRoles();
        $this->loadAvailableUsers();
        $this->loadSpatiePermissions();
        $this->documentForm['owner_id'] = auth()->id();
    }

    public function loadDocumentTypes(): void
    {
        $this->documentTypes = DocumentType::active()->orderBy('name')->get();
    }

    public function loadUsers(): void
    {
        $this->users = User::where('active', true)->orderBy('name')->get();
    }

    public function loadRoles(): void
    {
        $roles = SpatieRole::query()
            ->where('active', true)
            ->orderBy('name');

        $company = getUserCompany();
        if ($company && isset($company->id)) {
            $roles->where('company_id', $company->id);
        }

        $this->roles = $roles->get();
    }

    public function loadAvailableUsers(): void
    {
        $this->availableUsers = User::where('active', true)->orderBy('name')->get();
    }

    public function loadSpatiePermissions(): void
    {
        $this->spatiePermissions = SpatiePermission::orderBy('name')->pluck('name')->toArray();
    }

    public function getDocumentsProperty()
    {
        // Optimize with selective field loading and better relationships
        $query = Document::with([
                'documentType:id,name,code',
                'owner:id,name',
                'creator:id,name',
                'approver:id,name'
            ])
            ->select([
                'id', 'document_type_id', 'title', 'document_number', 
                'description', 'owner_id', 'created_by', 'approved_by',
                'status', 'expiry_date', 'created_at', 'updated_at',
                'is_kb_indexed', 'kb_indexing_status', 'kb_last_indexed_at'
            ])
            ->active();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('document_number', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->typeFilter) {
            $query->where('document_type_id', $this->typeFilter);
        }

        if ($this->ownerFilter) {
            $query->where('owner_id', $this->ownerFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->kbFilter !== '') {
            $query->where('is_kb_indexed', $this->kbFilter === 'indexed');
        }

        if ($this->expiringFilter) {
            $query->where('expiry_date', '<=', now()->addDays(30))
                  ->whereNotNull('expiry_date');
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }

    public function showCreateModal(): void
    {
        if (!Gate::forUser(auth()->user())->allows('create', Document::class)) {
            $this->message = 'You do not have permission to create documents';
            $this->messageType = 'error';
            return;
        }

        $this->resetForm();
        $this->editingDocument = null;
        $this->inheritedPermissions = [];
        $this->userPermissions = [];
        $this->showModal = true;
    }

    public function applyRole(): void
    {
        if (!$this->selectedRole) {
            return;
        }

        // Check if role is already added
        if (isset($this->selectedRoles[$this->selectedRole])) {
            $this->message = 'Role already added';
            $this->messageType = 'error';
            return;
        }

        // Add the role with default permissions
        $this->selectedRoles[$this->selectedRole] = [
            'role_id' => $this->selectedRole,
            'role_name' => $this->roles->firstWhere('id', $this->selectedRole)->name,
            'permissions' => [
                'view' => false,
                'add' => false,
                'edit' => false,
                'delete' => false,
                'amend' => false,
                'authorize_amendment' => false,
                'approve_amendment' => false,
            ]
        ];

        $this->selectedRole = null;
    }

    public function removeRole($roleId): void
    {
        unset($this->selectedRoles[$roleId]);
    }

    public function addUserPermission(): void
    {
        $this->userPermissions[] = [
            'user_id' => null,
            'permissions' => [
                'view' => false,
                'add' => false,
                'edit' => false,
                'delete' => false,
                'amend' => false,
                'authorize_amendment' => false,
                'approve_amendment' => false,
            ]
        ];
    }

    public function removeUserPermission($index): void
    {
        unset($this->userPermissions[$index]);
        $this->userPermissions = array_values($this->userPermissions);
    }

    public function showEditModal($documentId): void
    {
        $document = Document::findOrFail($documentId);

        if (!Gate::forUser(auth()->user())->allows('update', $document)) {
            $this->message = 'You do not have permission to edit this document';
            $this->messageType = 'error';
            return;
        }
        
        $this->editingDocument = $document->id;
        
        // Get existing permissions
        $existingPermissions = $this->permissionManager->getPermissionsForDisplay($document);
        
        $this->documentForm = [
            'document_type_id' => $document->document_type_id,
            'title' => $document->title,
            'description' => $document->description,
            'owner_id' => $document->owner_id,
            'expiry_date' => $document->expiry_date?->format('Y-m-d'),
            'tags' => $document->tags ?? [],
            'status' => $document->status,
            'permissions' => $existingPermissions,
            'is_kb_indexed' => $document->is_kb_indexed,
            'kb_collection' => $document->kb_collection ?: 'General Documents',
            'kb_required_permission' => $document->kb_required_permission ?: 'general.view',
            'kb_chunk_size' => $document->kb_chunk_size ?: 800,
            'kb_chunk_overlap' => $document->kb_chunk_overlap ?: 100,
            'kb_content' => $document->kb_content,
        ];
        
        // Load inherited permissions from document type
        if ($document->documentType) {
            $this->inheritedPermissions = $this->permissionManager->getInheritedPermissions($document->documentType);
        }
        
        // Convert role permissions to selectedRoles format
        $this->selectedRoles = [];
        if (isset($existingPermissions['roles'])) {
            foreach ($existingPermissions['roles'] as $roleId => $perms) {
                $role = $this->roles->firstWhere('id', $roleId);
                if ($role) {
                    $this->selectedRoles[$roleId] = [
                        'role_id' => $roleId,
                        'role_name' => $role->name,
                        'permissions' => $perms,
                    ];
                }
            }
        }
        
        // Convert user permissions to array format for display
        $this->userPermissions = [];
        if (isset($existingPermissions['users'])) {
            foreach ($existingPermissions['users'] as $userId => $perms) {
                $this->userPermissions[] = [
                    'user_id' => $userId,
                    'permissions' => $perms,
                ];
            }
        }
        
        $this->showModal = true;
    }

    public function saveDocument(): void
    {
        $rules = [
            'documentForm.document_type_id' => 'required|exists:document_types,id',
            'documentForm.title' => 'required|string|max:255',
            'documentForm.description' => 'nullable|string',
            'documentForm.owner_id' => 'required|exists:users,id',
            'documentForm.expiry_date' => 'nullable|date|after:today',
            'documentForm.status' => 'required|in:draft,pending_approval,approved',
        ];

        if (!$this->editingDocument) {
            $rules['file'] = 'required|file|max:51200'; // 50MB max
        } else {
            $rules['file'] = 'nullable|file|max:51200';
        }

        $this->validate($rules);

        try {
            if ($this->editingDocument) {
                $document = Document::findOrFail($this->editingDocument);

                if (!Gate::forUser(auth()->user())->allows('update', $document)) {
                    $this->message = 'You do not have permission to update this document';
                    $this->messageType = 'error';
                    return;
                }

                $oldValues = $document->toArray();

                // Update document metadata
                $document->update([
                    'title' => $this->documentForm['title'],
                    'description' => $this->documentForm['description'],
                    'owner_id' => $this->documentForm['owner_id'],
                    'expiry_date' => $this->documentForm['expiry_date'],
                    'status' => $this->documentForm['status'],
                    'tags' => $this->documentForm['tags'],
                    'is_kb_indexed' => $this->documentForm['is_kb_indexed'],
                    'kb_collection' => $this->documentForm['kb_collection'],
                    'kb_required_permission' => $this->documentForm['kb_required_permission'],
                    'kb_chunk_size' => $this->documentForm['kb_chunk_size'],
                    'kb_chunk_overlap' => $this->documentForm['kb_chunk_overlap'],
                    'kb_content' => $this->documentForm['kb_content'],
                ]);

                // Handle file upload if provided
                if ($this->file) {
                    $this->uploadFile($document);
                }

                DocumentAuditLog::log(
                    $document,
                    'updated',
                    $oldValues,
                    $document->fresh()->toArray(),
                    'Document updated'
                );

                $this->message = 'Document updated successfully';
            } else {
                if (!Gate::forUser(auth()->user())->allows('create', Document::class)) {
                    $this->message = 'You do not have permission to create this document';
                    $this->messageType = 'error';
                    return;
                }

                // Create new document
                $documentType = DocumentType::findOrFail($this->documentForm['document_type_id']);
                $documentNumber = $this->numberGenerator->generate($documentType);

                $document = Document::create([
                    'document_type_id' => $this->documentForm['document_type_id'],
                    'document_number' => $documentNumber,
                    'title' => $this->documentForm['title'],
                    'description' => $this->documentForm['description'],
                    'owner_id' => $this->documentForm['owner_id'],
                    'created_by' => auth()->id(),
                    'expiry_date' => $this->documentForm['expiry_date'],
                    'status' => $this->documentForm['status'],
                    'tags' => $this->documentForm['tags'],
                    'is_kb_indexed' => $this->documentForm['is_kb_indexed'],
                    'kb_collection' => $this->documentForm['kb_collection'],
                    'kb_required_permission' => $this->documentForm['kb_required_permission'],
                    'kb_chunk_size' => $this->documentForm['kb_chunk_size'],
                    'kb_chunk_overlap' => $this->documentForm['kb_chunk_overlap'],
                    'kb_content' => $this->documentForm['kb_content'],
                    'file_path' => '',
                    'file_name' => '',
                ]);

                $this->uploadFile($document);

                DocumentAuditLog::log(
                    $document,
                    'created',
                    null,
                    $document->toArray(),
                    'Document created'
                );

                $this->message = 'Document created successfully';
            }

            // Prepare and sync permissions
            $permissionsData = $this->preparePermissionsData();
            $this->permissionManager->syncPermissions(
                $document,
                $permissionsData,
                auth()->id()
            );

            $this->messageType = 'success';
            
            // Handle AI Indexing if requested
            if ($document->is_kb_indexed) {
                $this->knowledgeService->indexDocument($document);
            } else if ($this->editingDocument) {
                // If it was indexed but now unchecked, remove it
                $this->knowledgeService->removeDocument($document);
            }

            $this->closeModal();

        } catch (\Exception $e) {
            $this->message = 'Error saving document: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    protected function uploadFile($document): void
    {
        $path = Storage::disk('dms')->putFile(
            "documents/{$document->document_type_id}/{$document->id}",
            $this->file
        );

        $document->update([
            'file_path' => $path,
            'file_name' => $this->file->getClientOriginalName(),
            'file_size' => $this->file->getSize(),
            'mime_type' => $this->file->getMimeType(),
        ]);
    }

    public function deleteDocument($documentId): void
    {
        try {
            $document = Document::findOrFail($documentId);

            if (!Gate::forUser(auth()->user())->allows('delete', $document)) {
                $this->message = 'You do not have permission to delete this document';
                $this->messageType = 'error';
                return;
            }

            DocumentAuditLog::log(
                $document,
                'deleted',
                $document->toArray(),
                null,
                'Document deleted'
            );

            // Delete file from storage
            if ($document->file_path) {
                Storage::disk('dms')->delete($document->file_path);
            }

            $document->delete();

            $this->message = 'Document deleted successfully';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            $this->message = 'Error deleting document: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function archiveDocument($documentId, $reason = 'Archived by user'): void
    {
        try {
            $document = Document::findOrFail($documentId);

            if (!Gate::forUser(auth()->user())->allows('archive', $document)) {
                $this->message = 'You do not have permission to archive this document';
                $this->messageType = 'error';
                return;
            }

            $document->archive($reason);

            DocumentAuditLog::log(
                $document,
                'archived',
                null,
                ['archive_reason' => $reason],
                'Document archived'
            );

            $this->message = 'Document archived successfully';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            $this->message = 'Error archiving document: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function approveDocument($documentId): void
    {
        try {
            $document = Document::findOrFail($documentId);

            if (!Gate::forUser(auth()->user())->allows('approveAmendment', $document)) {
                $this->message = 'You do not have permission to approve this document';
                $this->messageType = 'error';
                return;
            }

            $document->approve();

            DocumentAuditLog::log(
                $document,
                'approved',
                null,
                null,
                'Document approved'
            );

            $this->message = 'Document approved successfully';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            $this->message = 'Error approving document: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function toggleAIIndexing($documentId): void
    {
        try {
            $document = Document::findOrFail($documentId);
            
            if ($document->is_kb_indexed) {
                $result = $this->knowledgeService->removeDocument($document);
                $this->message = 'Document removed from AI Knowledge Base';
            } else {
                $document->update(['is_kb_indexed' => true]);
                $result = $this->knowledgeService->indexDocument($document);
                $this->message = 'Document sent for AI indexing';
            }

            $this->messageType = $result['status'] === 'ok' ? 'success' : 'error';
            if ($result['status'] !== 'ok') {
                $this->message = 'AI Action failed: ' . ($result['message'] ?? 'Unknown error');
            }

        } catch (\Exception $e) {
            $this->message = 'Error toggling AI indexing: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function preparePermissionsData(): array
    {
        $permissions = [
            'roles' => [],
            'users' => [],
        ];

        // Convert selectedRoles to the format expected by PermissionManager
        foreach ($this->selectedRoles as $roleData) {
            if (isset($roleData['role_id'])) {
                $permissions['roles'][$roleData['role_id']] = $roleData['permissions'] ?? [];
            }
        }

        // Convert userPermissions array to the format expected by PermissionManager
        foreach ($this->userPermissions as $userPerm) {
            if (isset($userPerm['user_id']) && $userPerm['user_id']) {
                $permissions['users'][$userPerm['user_id']] = $userPerm['permissions'] ?? [];
            }
        }

        return $permissions;
    }

    public function resetForm(): void
    {
        $this->documentForm = [
            'document_type_id' => null,
            'title' => '',
            'description' => '',
            'owner_id' => auth()->id(),
            'expiry_date' => null,
            'tags' => [],
            'status' => 'draft',
            'permissions' => [
                'roles' => [],
                'users' => [],
            ],
            'is_kb_indexed' => false,
            'kb_collection' => 'General Documents',
            'kb_required_permission' => 'general.view',
            'kb_chunk_size' => 800,
            'kb_chunk_overlap' => 100,
            'kb_content' => '',
        ];
        $this->file = null;
        $this->selectedRole = null;
        $this->selectedRoles = [];
        $this->userPermissions = [];
        $this->inheritedPermissions = [];
        $this->resetValidation();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->typeFilter = null;
        $this->ownerFilter = null;
        $this->statusFilter = '';
        $this->kbFilter = '';
        $this->expiringFilter = false;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function render()
    {
        // Enrich documents with computed properties to avoid PHP logic in Blade
        $documents = $this->documents;
        $enrichedDocuments = $documents->map(function($document) {
            $statusClass = match($document->status) {
                'approved' => 'success',
                'pending_approval' => 'warning',
                'rejected' => 'danger',
                'draft' => 'secondary',
                default => 'secondary'
            };
            
            $expiryData = [
                'has_expiry' => !is_null($document->expiry_date),
                'days_remaining' => null,
                'expiry_class' => null,
                'show_warning' => false
            ];
            
            if ($document->expiry_date) {
                $daysRemaining = $document->expiry_date->diffInDays(now());
                $expiryData['days_remaining'] = $daysRemaining;
                $expiryData['expiry_class'] = $daysRemaining <= 7 ? 'danger' : ($daysRemaining <= 30 ? 'warning' : 'success');
                $expiryData['show_warning'] = $daysRemaining <= 30;
            }
            
            return [
                'document' => $document,
                'status_class' => $statusClass,
                'expiry' => $expiryData
            ];
        });
        
        return view('livewire.dms.active-documents-component', [
            'documents' => $documents,
            'enrichedDocuments' => $enrichedDocuments,
        ]);
    }
}

