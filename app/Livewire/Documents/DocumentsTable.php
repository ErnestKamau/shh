<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentFolder;
use App\User;
use Illuminate\Support\Facades\Auth;

class DocumentsTable extends Component
{
    
    use WithPagination;

    public $search = '';
    public $perPage = 15;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $showAdvancedFilters = false;
    
    // Folder properties
    public $currentNode = null;
    public $newFolderName = '';
    public $editingFolderId = null;
    public $editFolderName = '';
    public $isTypeView = false;
    
    // Advanced filters
    public $filters = [
        'document_type_id' => '',
        'status' => '',
        'created_by' => '',
        'version' => '',
        'created_date_from' => '',
        'created_date_to' => '',
        'updated_date_from' => '',
        'updated_date_to' => '',
        'validity_from' => '',
        'validity_to' => '',
        'expiry_status' => '',
        'publishing_status' => '',
    ];

    // Available options for filters
    public $availableDocumentTypes = [];
    public $availableUsers = [];
    public $availableStatuses = ['active', 'draft', 'archived', 'expired'];

    protected $queryString = [
        'search' => ['except' => ''],
        'filters' => ['except' => []],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
        'showAdvancedFilters' => ['except' => false],
        'currentNode' => ['except' => null]
    ];

    public function mount()
    {
        $this->loadFilterOptions();
        
        // Check if document_type_id is passed in the URL (from document types view)
        if (request()->has('document_type_id')) {
            $this->filters['document_type_id'] = request()->get('document_type_id');
        }
        
        // Check if status is passed in the URL (for direct status filtering)
        if (request()->has('status')) {
            $this->filters['status'] = request()->get('status');
        }
        
        // Check if expiry_status is passed in the URL (for direct expiry filtering)
        if (request()->has('expiry_status')) {
            $this->filters['expiry_status'] = request()->get('expiry_status');
        }
    }

    public function loadFilterOptions()
    {
        // Optimize filter options loading with distinct queries
        $this->availableDocumentTypes = DocumentType::where('is_active', true)->get();
        $this->availableUsers = User::select('id', 'name')->get();
    }

    // Remove the toggleAdvancedFilters method since we're using JavaScript for better performance

    public function clearFilters()
    {
        $this->filters = [
            'document_type_id' => '',
            'status' => '',
            'created_by' => '',
            'version' => '',
            'created_date_from' => '',
            'created_date_to' => '',
            'updated_date_from' => '',
            'updated_date_to' => '',
            'validity_from' => '',
            'validity_to' => '',
            'expiry_status' => '',
            'publishing_status' => '',
        ];
        $this->search = '';
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function updatedFilters($value, $key)
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function navigateNode($nodeId)
    {
        $this->currentNode = $nodeId;
        $this->resetPage();
    }

    public function navigateUp()
    {
        if ($this->currentNode) {
            if (str_starts_with($this->currentNode, 'folder_')) {
                $folderId = substr($this->currentNode, 7);
                $folder = DocumentFolder::find($folderId);
                if ($folder) {
                    if ($folder->parent_id) {
                        $this->currentNode = 'folder_' . $folder->parent_id;
                    } else {
                        $this->currentNode = 'type_' . $folder->document_type_id;
                    }
                } else {
                    if (!$this->isTypeView) {
                        $this->currentNode = null;
                    }
                }
            } else {
                if (!$this->isTypeView) {
                    $this->currentNode = null;
                }
            }
            $this->resetPage();
        }
    }

    public function navigateHome()
    {
        if (!$this->isTypeView) {
            $this->currentNode = null;
        } else {
            // Find base type if inside folder
            if (str_starts_with($this->currentNode, 'folder_')) {
                $folderId = substr($this->currentNode, 7);
                $folder = DocumentFolder::find($folderId);
                if ($folder) {
                    $this->currentNode = 'type_' . $folder->document_type_id;
                }
            }
        }
        $this->resetPage();
    }

    public function createFolder()
    {
        $this->validate([
            'newFolderName' => 'required|string|max:255',
        ]);

        if (!$this->currentNode) {
            session()->flash('error', 'You must be inside a Document Type to create a folder.');
            return;
        }

        $user = Auth::user();

        $documentTypeId = null;
        $parentId = null;

        if (str_starts_with($this->currentNode, 'type_')) {
            $documentTypeId = substr($this->currentNode, 5);
        } elseif (str_starts_with($this->currentNode, 'folder_')) {
            $parentId = substr($this->currentNode, 7);
            $parentFolder = DocumentFolder::find($parentId);
            $documentTypeId = $parentFolder->document_type_id;
        }

        DocumentFolder::create([
            'name' => $this->newFolderName,
            'parent_id' => $parentId,
            'document_type_id' => $documentTypeId,
            'department_id' => $user->department_id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->newFolderName = '';
        $this->dispatch('hide-create-folder-modal');
        session()->flash('success', 'Folder created successfully.');
    }

    public function editFolder($folderId)
    {
        $folder = DocumentFolder::find($folderId);
        if ($folder) {
            // Ensure the user has permission to edit this folder (must be Admin or belong to the same department)
            $user = Auth::user();
            if ($user->hasRole('Admin') || $folder->department_id === $user->department_id) {
                $this->editingFolderId = $folder->id;
                $this->editFolderName = $folder->name;
                $this->dispatch('show-edit-folder-modal');
            } else {
                session()->flash('error', 'You do not have permission to edit this folder.');
            }
        }
    }

    public function updateFolder()
    {
        $this->validate([
            'editFolderName' => 'required|string|max:255',
        ]);

        if ($this->editingFolderId) {
            $folder = DocumentFolder::find($this->editingFolderId);
            if ($folder) {
                // Double check permissions
                $user = Auth::user();
                if ($user->hasRole('Admin') || $folder->department_id === $user->department_id) {
                    $folder->name = $this->editFolderName;
                    $folder->updated_by = $user->id;
                    $folder->save();
                    
                    $this->editingFolderId = null;
                    $this->editFolderName = '';
                    $this->dispatch('hide-edit-folder-modal');
                    session()->flash('success', 'Folder updated successfully.');
                }
            }
        }
    }

    public function deleteFolder($folderId)
    {
        $folder = DocumentFolder::with(['children', 'documents'])->find($folderId);
        
        if ($folder) {
            $user = Auth::user();
            
            // Check permissions
            if (!$user->hasRole('Admin') && $folder->department_id !== $user->department_id) {
                session()->flash('error', 'You do not have permission to delete this folder.');
                return;
            }

            // Check if folder is empty (no children, no documents)
            if ($folder->children->count() > 0) {
                session()->flash('error', 'Cannot delete this folder because it contains subfolders.');
                return;
            }

            // Check if there are any ACTIVE documents in this folder
            $hasActiveDocuments = Document::where('document_folder_id', $folder->id)
                ->where('is_active', true)
                ->exists();

            if ($hasActiveDocuments) {
                session()->flash('error', 'Cannot delete this folder because it contains active documents.');
                return;
            }

            $folder->delete();
            session()->flash('success', 'Folder deleted successfully.');
        }
    }

    public function getFolderPathProperty()
    {
        $path = collect();
        if ($this->currentNode === null) {
            return $path;
        }

        if (str_starts_with($this->currentNode, 'folder_')) {
            $folderId = substr($this->currentNode, 7);
            $folder = DocumentFolder::find($folderId);
            
            while ($folder) {
                $path->prepend((object)[
                    'id' => 'folder_' . $folder->id,
                    'name' => $folder->name
                ]);
                if ($folder->parent_id) {
                    $folder = $folder->parent;
                } else {
                    $type = DocumentType::find($folder->document_type_id);
                    if ($type) {
                        $path->prepend((object)[
                            'id' => 'type_' . $type->id,
                            'name' => $type->name
                        ]);
                    }
                    $folder = null;
                }
            }
        } elseif (str_starts_with($this->currentNode, 'type_')) {
            $typeId = substr($this->currentNode, 5);
            $type = DocumentType::find($typeId);
            if ($type) {
                $path->prepend((object)[
                    'id' => 'type_' . $type->id,
                    'name' => $type->name
                ]);
            }
        }
        
        return $path;
    }

    public function render()
    {
        $user = Auth::user();

        // Start with base query - show only published documents accessible to the user
        $query = Document::with(['documentType:id,name', 'department:id,name', 'creator:id,name', 'folder.parent'])
            ->published(); // Only show published documents

        // Filter documents based on publish scope and targets
        // Admin users can see all documents
        if (!$user->hasRole('Admin')) {
            $query->where(function($q) use ($user) {
                $q->where('publish_scope', 'all_departments')
                  ->orWhere('publish_scope', 'all_roles')
                  ->orWhere(function($subQ) use ($user) {
                      // Department-specific documents
                      $subQ->where('publish_scope', 'department')
                           ->whereJsonContains('publish_targets', (string)$user->department_id);
                  })
                  ->orWhere(function($subQ) use ($user) {
                      // Role-specific documents
                      $userRoleIds = $user->roles->pluck('role_id')->toArray();
                      if (!empty($userRoleIds)) {
                          $subQ->where('publish_scope', 'role')
                               ->where(function($roleQ) use ($userRoleIds) {
                                   foreach ($userRoleIds as $roleId) {
                                       $roleQ->orWhereJsonContains('publish_targets', (string)$roleId);
                                   }
                               });
                      }
                  })
                  ->orWhere(function($subQ) use ($user) {
                      // Mixed scope documents
                      $userRoleIds = $user->roles->pluck('role_id')->toArray();
                      $subQ->where('publish_scope', 'mixed')
                           ->where(function($mixedQ) use ($user, $userRoleIds) {
                               $mixedQ->whereJsonContains('publish_targets->departments', (string)$user->department_id)
                                      ->orWhere(function($roleQ) use ($userRoleIds) {
                                          if (!empty($userRoleIds)) {
                                              foreach ($userRoleIds as $roleId) {
                                                  $roleQ->orWhereJsonContains('publish_targets->roles', (string)$roleId);
                                              }
                                          }
                                      });
                           });
                  });
            });
        }

        // If viewing by document type, show all versions but order by latest
        if ($this->filters['document_type_id']) {
            $query->where('document_type_id', $this->filters['document_type_id'])
                  ->orderBy('name')
                  ->orderBy('version', 'desc'); // Order by version descending (latest first)
        }
        
        // Apply folder filter
        if ($this->currentNode !== null) {
            if (str_starts_with($this->currentNode, 'type_')) {
                $typeId = substr($this->currentNode, 5);
                $query->where('document_type_id', $typeId)->whereNull('document_folder_id');
            } elseif (str_starts_with($this->currentNode, 'folder_')) {
                $folderId = substr($this->currentNode, 7);
                $query->where('document_folder_id', $folderId);
            }
        } else {
            // At root level, show all documents across all types and folders
        }
        // Removed currentVersion() scope since we're using published/unpublished criteria

        // Apply search with optimized query
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('document_number', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Apply advanced filters efficiently
        // Note: document_type_id filter is already applied above when viewing by type

        if ($this->filters['status']) {
            if ($this->filters['status'] === 'expired') {
                // For expired status, check validity_period instead of status field
                $query->expired();
            } else {
                // For other statuses, use the status field
                $query->where('status', $this->filters['status']);
            }
        }

        if ($this->filters['created_by']) {
            $query->where('created_by', $this->filters['created_by']);
        }

        if ($this->filters['version']) {
            $query->where('version', 'like', '%' . $this->filters['version'] . '%');
        }

        if ($this->filters['created_date_from']) {
            $query->whereDate('created_at', '>=', $this->filters['created_date_from']);
        }

        if ($this->filters['created_date_to']) {
            $query->whereDate('created_at', '<=', $this->filters['created_date_to']);
        }

        if ($this->filters['updated_date_from']) {
            $query->whereDate('updated_at', '>=', $this->filters['updated_date_from']);
        }

        if ($this->filters['updated_date_to']) {
            $query->whereDate('updated_at', '<=', $this->filters['updated_date_to']);
        }

        if ($this->filters['validity_from']) {
            $query->whereDate('validity_period', '>=', $this->filters['validity_from']);
        }

        if ($this->filters['validity_to']) {
            $query->whereDate('validity_period', '<=', $this->filters['validity_to']);
        }

        // Apply publishing status filter
        if ($this->filters['publishing_status']) {
            switch ($this->filters['publishing_status']) {
                case 'published':
                    $query->published();
                    break;
                case 'unpublished':
                    $query->unpublished();
                    break;
            }
        } else {
            // Default: show only published documents
            $query->published();
        }

        // Apply expiry status filter
        if ($this->filters['expiry_status']) {
            switch ($this->filters['expiry_status']) {
                case 'expired':
                    $query->expired();
                    break;
                case 'expiring_soon':
                    $query->expiringSoon(30);
                    break;
                case 'valid':
                    $query->valid();
                    break;
            }
        }

        $documents = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        // Fetch folders or document types for the current directory level
        if ($this->currentNode === null) {
            // At root, show Document Types
            $nodesQuery = DocumentType::where('is_active', true);
            $folders = $nodesQuery->orderBy('name')->get()->map(function($type) {
                return (object)[
                    'id' => 'type_' . $type->id,
                    'name' => $type->name,
                    'is_type' => true
                ];
            });
        } elseif (str_starts_with($this->currentNode, 'type_')) {
            $typeId = substr($this->currentNode, 5);
            $nodesQuery = DocumentFolder::where('document_type_id', $typeId)->whereNull('parent_id');
            if (!$user->hasRole('Admin')) {
                $nodesQuery->where('department_id', $user->department_id);
            }
            $folders = $nodesQuery->orderBy('name')->get()->map(function($folder) {
                return (object)[
                    'id' => 'folder_' . $folder->id,
                    'name' => $folder->name,
                    'is_type' => false
                ];
            });
        } elseif (str_starts_with($this->currentNode, 'folder_')) {
            $folderId = substr($this->currentNode, 7);
            $nodesQuery = DocumentFolder::where('parent_id', $folderId);
            if (!$user->hasRole('Admin')) {
                $nodesQuery->where('department_id', $user->department_id);
            }
            $folders = $nodesQuery->orderBy('name')->get()->map(function($folder) {
                return (object)[
                    'id' => 'folder_' . $folder->id,
                    'name' => $folder->name,
                    'is_type' => false
                ];
            });
        } else {
            $folders = collect();
        }

        return view('livewire.documents.documents-table', [
            'documents' => $documents,
            'folders' => $folders,
        ]);
    }
}
