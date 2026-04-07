<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Document;
use App\Models\DocumentType;
use App\User;
use Illuminate\Support\Facades\Auth;

class ExpiredDocumentsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;
    public $sortField = 'validity_period';
    public $sortDirection = 'asc';
    public $showAdvancedFilters = false;
    
    // Advanced filters
    public $filters = [
        'document_type_id' => '',
        'status' => '',
        'created_by' => '',
        'expired_date_from' => '',
        'expired_date_to' => '',
        'created_date_from' => '',
        'created_date_to' => '',
    ];

    // Available options for filters
    public $availableUsers = [];
    public $availableDocumentTypes = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'filters' => ['except' => []],
        'sortField' => ['except' => 'validity_period'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 15],
        'showAdvancedFilters' => ['except' => false]
    ];

    public function mount()
    {
        $this->loadFilterOptions();
    }

    public function loadFilterOptions()
    {
        $user = Auth::user();

        // Load users who have created documents accessible to this user
        $this->availableUsers = User::whereHas('documents', function($query) use ($user) {
            $query->published()
                  ->where(function($q) use ($user) {
                      $q->where('publish_scope', 'all_departments')
                        ->orWhere('publish_scope', 'all_roles')
                        ->orWhere(function($subQ) use ($user) {
                            $subQ->where('publish_scope', 'department')
                                 ->whereJsonContains('publish_targets', (string)$user->department_id);
                        })
                                                 ->orWhere(function($subQ) use ($user) {
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
        })->select('id', 'name')->distinct()->get();

        // Load document types
        $this->availableDocumentTypes = DocumentType::where('is_active', true)->get();
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

    public function clearFilters()
    {
        $this->filters = [
            'document_type_id' => '',
            'status' => '',
            'created_by' => '',
            'expired_date_from' => '',
            'expired_date_to' => '',
            'created_date_from' => '',
            'created_date_to' => '',
        ];
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $query = Document::currentVersion()
            ->expired()
            ->active()
            ->published()
            ->with(['documentType', 'department', 'creator']);

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

        // Apply search
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('document_number', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Apply advanced filters
        if ($this->filters['document_type_id']) {
            $query->where('document_type_id', $this->filters['document_type_id']);
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        if ($this->filters['created_by']) {
            $query->where('created_by', $this->filters['created_by']);
        }

        if ($this->filters['expired_date_from']) {
            $query->whereDate('validity_period', '>=', $this->filters['expired_date_from']);
        }

        if ($this->filters['expired_date_to']) {
            $query->whereDate('validity_period', '<=', $this->filters['expired_date_to']);
        }

        if ($this->filters['created_date_from']) {
            $query->whereDate('created_at', '>=', $this->filters['created_date_from']);
        }

        if ($this->filters['created_date_to']) {
            $query->whereDate('created_at', '<=', $this->filters['created_date_to']);
        }

        $documents = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.documents.expired-documents-table', [
            'documents' => $documents,
        ]);
    }
}
