<?php

namespace App\Livewire\Personnel;

use App\Models\Lab\Qualification;
use App\Models\Personnel\RoleCertification;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleDetailManager extends Component
{
    public int $roleId;
    public string $activeTab = 'permissions';
    public int $activeModuleTab = 0;
    public array $permissionsState = [];
    public bool $showCertificationModal = false;
    public bool $showDeleteCertificationModal = false;
    public ?int $editingCertificationId = null;
    public ?int $certificationId = null;
    public bool $isMandatory = false;
    public string $message = '';
    public string $messageType = 'success';

    public function mount(string $roleId): void
    {
        $this->roleId = $roleId;
        $this->initializePermissionsState();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['permissions', 'certifications'], true) ? $tab : 'permissions';
    }

    public function setActiveModuleTab(int $tabIndex): void
    {
        if ($tabIndex < 0 || $tabIndex >= count($this->moduleNames)) {
            return;
        }

        $this->activeModuleTab = $tabIndex;
    }

    public function toggleModulePermission(string $module): void
    {
        $current = (bool) ($this->permissionsState[$module]['permission'] ?? false);
        $newValue = !$current;
        $this->permissionsState[$module]['permission'] = $newValue;

        $rules = getModulePermissions();
        $components = $rules[$module]['components'] ?? [];
        $actions = ['add', 'edit', 'view', 'delete'];

        foreach ($components as $component) {
            foreach ($actions as $action) {
                $this->permissionsState[$module]['components'][$component][$action] = $newValue;
            }
        }
    }

    public function toggleComponentAction(string $module, string $component, string $action): void
    {
        $current = (bool) ($this->permissionsState[$module]['components'][$component][$action] ?? false);
        $this->permissionsState[$module]['components'][$component][$action] = !$current;

        $rules = getModulePermissions();
        $components = $rules[$module]['components'] ?? [];
        $actions = ['add', 'edit', 'view', 'delete'];
        $hasAny = false;
        foreach ($components as $componentName) {
            foreach ($actions as $actionName) {
                if ((bool) ($this->permissionsState[$module]['components'][$componentName][$actionName] ?? false)) {
                    $hasAny = true;
                    break 2;
                }
            }
        }

        $this->permissionsState[$module]['permission'] = $hasAny;
    }

    public function savePermissions(): void
    {
        $role = $this->role;
        $permissionNames = [];

        foreach ($this->permissionsState as $module => $state) {
            if (!empty($state['permission'])) {
                $permissionNames[] = $module . '.permission';
            }

            foreach (($state['components'] ?? []) as $component => $actions) {
                foreach ($actions as $action => $value) {
                    if ($value) {
                        $permissionNames[] = $module . '.components.' . $component . '.' . $action;
                    }
                }
            }
        }

        $permissionNames = array_values(array_unique($permissionNames));

        foreach ($permissionNames as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $role->syncPermissions($permissionNames);

        $this->message = 'Role permissions were set successfully.';
        $this->messageType = 'success';
    }

    public function openAddCertificationModal(): void
    {
        $this->editingCertificationId = null;
        $this->certificationId = null;
        $this->isMandatory = false;
        $this->showCertificationModal = true;
    }

    public function openEditCertificationModal(int $roleCertificationId): void
    {
        $item = RoleCertification::query()->findOrFail($roleCertificationId);
        $this->editingCertificationId = $item->id;
        $this->certificationId = (int) $item->certification_id;
        $this->isMandatory = (int) $item->is_mandatory === 1;
        $this->showCertificationModal = true;
    }

    public function closeCertificationModal(): void
    {
        $this->showCertificationModal = false;
    }

    public function saveCertification(): void
    {
        $this->validate([
            'certificationId' => 'required|integer',
            'isMandatory' => 'boolean',
        ]);

        if ($this->editingCertificationId) {
            $item = RoleCertification::query()->findOrFail($this->editingCertificationId);
            $item->edited_by = (string) auth()->user()->name;
        } else {
            $item = new RoleCertification();
            $item->role_id = $this->roleId;
        }

        $item->certification_id = (int) $this->certificationId;
        $item->is_mandatory = $this->isMandatory ? 1 : 0;
        $item->save();

        $this->showCertificationModal = false;
        $this->message = $this->editingCertificationId ? 'Certification edited successfully.' : 'Role certification added successfully.';
        $this->messageType = 'success';
    }

    public function openDeleteCertificationModal(int $roleCertificationId): void
    {
        $this->editingCertificationId = $roleCertificationId;
        $this->showDeleteCertificationModal = true;
    }

    public function closeDeleteCertificationModal(): void
    {
        $this->showDeleteCertificationModal = false;
    }

    public function deleteCertification(): void
    {
        $this->validate(['editingCertificationId' => 'required|integer']);
        $item = RoleCertification::query()->findOrFail((int) $this->editingCertificationId);
        $item->status = 1;
        $item->edited_by = (string) auth()->user()->name;
        $item->save();

        $this->showDeleteCertificationModal = false;
        $this->message = 'Role certification deleted successfully.';
        $this->messageType = 'success';
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function getRoleProperty()
    {
        return Role::query()->where('guard_name', 'web')->findOrFail($this->roleId);
    }

    public function getModuleRulesProperty(): array
    {
        return getModulePermissions();
    }

    public function getModuleNamesProperty(): array
    {
        return array_keys($this->moduleRules);
    }

    public function getCertificationsListProperty()
    {
        return Qualification::query()->where('module_code', 1)->where('status', 0)->orderBy('name')->get();
    }

    public function getRoleCertificationsProperty()
    {
        return RoleCertification::query()
            ->where('role_id', $this->roleId)
            ->where('status', 0)
            ->orderByDesc('created_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.personnel.role-detail-manager');
    }

    private function initializePermissionsState(): void
    {
        $rules = getModulePermissions();
        $actions = ['add', 'edit', 'view', 'delete'];
        $rolePermissionNames = $this->role->permissions->pluck('name')->flip()->toArray();

        foreach ($rules as $moduleName => $rule) {
            $this->permissionsState[$moduleName] = [
                'permission' => isset($rolePermissionNames[$moduleName . '.permission']),
                'components' => [],
            ];

            foreach (($rule['components'] ?? []) as $component) {
                $this->permissionsState[$moduleName]['components'][$component] = [];
                foreach ($actions as $action) {
                    $permissionKey = $moduleName . '.components.' . $component . '.' . $action;
                    $this->permissionsState[$moduleName]['components'][$component][$action] = isset($rolePermissionNames[$permissionKey]);
                }
            }
        }

        $this->activeModuleTab = 0;
    }
}
