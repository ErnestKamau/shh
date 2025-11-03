<?php

namespace App\Livewire\DMS;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DMS\DocumentType;
use App\Models\DMS\DocumentAuditLog;
use App\Services\DMS\DocumentNumberGenerator;
use App\Services\DMS\PermissionManager;
use App\User;
use App\Role;
use Illuminate\Validation\Rule;

class DocumentTypeManager extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingType = null;
    
    public $typeForm = [
        'name' => '',
        'code' => '',
        'description' => '',
        'parent_id' => null,
        'numbering_format' => '{TYPE_CODE}-{YEAR}-{SEQ}',
        'amendment_limit' => 5,
        'is_active' => true,
        'sort_order' => 0,
        'permissions' => [
            'roles' => [],
            'users' => [],
        ],
    ];

    public $search = '';
    public $statusFilter = '';
    public $parentFilter = null;
    public $message = '';
    public $messageType = 'success';

    public $parentTypes = [];
    public $roles = [];
    public $availableUsers = [];
    public $selectedRole = null;
    public $selectedRoles = [];
    public $userPermissions = [];
    public $inheritedPermissions = [];

    protected $numberGenerator;
    protected $permissionManager;

    public function boot(DocumentNumberGenerator $numberGenerator, PermissionManager $permissionManager)
    {
        $this->numberGenerator = $numberGenerator;
        $this->permissionManager = $permissionManager;
    }

    public function mount(): void
    {
        $this->loadParentTypes();
        $this->loadRoles();
        $this->loadAvailableUsers();
    }

    public function loadParentTypes(): void
    {
        try {
            $this->parentTypes = DocumentType::root()->active()->get();
        } catch (\Exception $e) {
            $this->parentTypes = new \Illuminate\Support\Collection();
        }
    }

    public function loadRoles(): void
    {
        $this->roles = Role::where('active', true)->orderBy('name')->get();
    }

    public function loadAvailableUsers(): void
    {
        $this->availableUsers = User::where('active', true)->orderBy('name')->get();
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

    public function getDocumentTypesProperty()
    {
        try {
            // Use pagination instead of loading all records
            $query = DocumentType::with(['parent:id,name', 'creator:id,name'])
                ->withCount('documents');

            if ($this->search) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->statusFilter !== '') {
                $query->where('is_active', $this->statusFilter === 'active');
            }

            if ($this->parentFilter) {
                $query->where('parent_id', $this->parentFilter);
            }

            return $query->orderBy('sort_order')->orderBy('name')->paginate(20);
        } catch (\Exception $e) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }
    }

    public function showCreateModal(): void
    {
        $this->resetForm();
        $this->editingType = null;
        $this->inheritedPermissions = [];
        $this->userPermissions = [];
        $this->showModal = true;
    }

    public function showEditModal($typeId): void
    {
        $type = DocumentType::findOrFail($typeId);
        
        // Get existing permissions
        $existingPermissions = $this->permissionManager->getPermissionsForDisplay($type);
        
        $this->editingType = $type->id;
        $this->typeForm = [
            'name' => $type->name,
            'code' => $type->code,
            'description' => $type->description,
            'parent_id' => $type->parent_id,
            'numbering_format' => $type->numbering_format,
            'amendment_limit' => $type->amendment_limit,
            'is_active' => $type->is_active,
            'sort_order' => $type->sort_order,
            'permissions' => $existingPermissions,
        ];
        
        // Load inherited permissions from parent type
        if ($type->parent) {
            $this->inheritedPermissions = $this->permissionManager->getInheritedPermissions($type);
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

    public function saveType(): void
    {
        $rules = [
            'typeForm.name' => 'required|string|max:255',
            'typeForm.code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('document_types', 'code')->ignore($this->editingType),
            ],
            'typeForm.description' => 'nullable|string',
            'typeForm.parent_id' => 'nullable|exists:document_types,id',
            'typeForm.numbering_format' => 'required|string|max:200',
            'typeForm.amendment_limit' => 'required|integer|min:1|max:100',
            'typeForm.is_active' => 'boolean',
            'typeForm.sort_order' => 'integer',
        ];

        $this->validate($rules);

        // Validate numbering format
        if (!$this->numberGenerator->validateFormat($this->typeForm['numbering_format'])) {
            $this->addError('typeForm.numbering_format', 'Invalid numbering format. Use tokens: {TYPE_CODE}, {YEAR}, {MONTH}, {SEQ}, {PARENT_CODE}');
            return;
        }

        try {
            if ($this->editingType) {
                $type = DocumentType::findOrFail($this->editingType);
                $oldValues = $type->toArray();
                $type->update($this->typeForm);

                DocumentAuditLog::log(
                    $type,
                    'updated',
                    $oldValues,
                    $type->fresh()->toArray(),
                    'Document type updated'
                );

                $this->message = 'Document type updated successfully';
            } else {
                $type = DocumentType::create(array_merge($this->typeForm, [
                    'created_by' => auth()->id(),
                ]));

                DocumentAuditLog::log(
                    $type,
                    'created',
                    null,
                    $type->toArray(),
                    'Document type created'
                );

                $this->message = 'Document type created successfully';
            }

            // Prepare and sync permissions
            $permissionsData = $this->preparePermissionsData();
            $this->permissionManager->syncPermissions(
                $type,
                $permissionsData,
                auth()->id()
            );

            $this->messageType = 'success';
            $this->closeModal();
            $this->loadParentTypes();

        } catch (\Exception $e) {
            $this->message = 'Error saving document type: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteType($typeId): void
    {
        try {
            $type = DocumentType::findOrFail($typeId);

            // Check if type has documents
            if ($type->documents()->count() > 0) {
                $this->message = 'Cannot delete document type with existing documents';
                $this->messageType = 'error';
                return;
            }

            // Check if type has children
            if ($type->children()->count() > 0) {
                $this->message = 'Cannot delete document type with child types';
                $this->messageType = 'error';
                return;
            }

            DocumentAuditLog::log(
                $type,
                'deleted',
                $type->toArray(),
                null,
                'Document type deleted'
            );

            $type->delete();

            $this->message = 'Document type deleted successfully';
            $this->messageType = 'success';
            $this->loadParentTypes();

        } catch (\Exception $e) {
            $this->message = 'Error deleting document type: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function updateOrder($orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            DocumentType::where('id', $id)->update(['sort_order' => $index]);
        }

        $this->message = 'Document types reordered successfully';
        $this->messageType = 'success';
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
        $this->typeForm = [
            'name' => '',
            'code' => '',
            'description' => '',
            'parent_id' => null,
            'numbering_format' => '{TYPE_CODE}-{YEAR}-{SEQ}',
            'amendment_limit' => 5,
            'is_active' => true,
            'sort_order' => 0,
            'permissions' => [
                'roles' => [],
                'users' => [],
            ],
        ];
        $this->selectedRole = null;
        $this->selectedRoles = [];
        $this->userPermissions = [];
        $this->inheritedPermissions = [];
        $this->resetValidation();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->parentFilter = null;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.dms.document-type-manager-component', [
            'documentTypes' => $this->documentTypes,
        ]);
    }
}

