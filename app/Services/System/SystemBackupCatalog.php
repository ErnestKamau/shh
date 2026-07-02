<?php

namespace App\Services\System;

class SystemBackupCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $targets = [
            [
                'key' => 'system-settings',
                'label' => 'System Settings',
                'description' => 'Configuration types, configuration values, and the system settings catalog.',
                'tables' => ['system_configuration_types', 'system_configurations'],
            ],
            [
                'key' => 'users',
                'label' => 'Users',
                'description' => 'Application user accounts and login profiles.',
                'tables' => ['users'],
            ],
            [
                'key' => 'companies',
                'label' => 'Companies',
                'description' => 'Company master data and account details.',
                'tables' => ['companies'],
            ],
            [
                'key' => 'module-pre-configs',
                'label' => 'Module Pre-configurations',
                'description' => 'Shared module lookup values and configurable reference data.',
                'tables' => ['module_pre_configs'],
            ],
            [
                'key' => 'translations',
                'label' => 'Translations',
                'description' => 'Languages and translation strings used by the platform.',
                'tables' => ['languages', 'language_lines'],
            ],
            [
                'key' => 'access-control',
                'label' => 'Access Control',
                'description' => 'Roles, permissions, and assignment mappings.',
                'tables' => ['roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions'],
            ],
        ];

        foreach ($this->activeModuleTargets() as $target) {
            $targets[] = $target;
        }

        return $targets;
    }

    /**
     * @param array<int, string> $keys
     * @return array<int, array<string, mixed>>
     */
    public function selected(array $keys): array
    {
        $selectedKeys = array_values(array_unique(array_filter(array_map('strval', $keys), static fn (string $key): bool => $key !== '')));

        if ($selectedKeys === []) {
            return [];
        }

        $lookup = [];
        foreach ($this->all() as $item) {
            $lookup[$item['key']] = $item;
        }

        $selected = [];
        foreach ($selectedKeys as $key) {
            if (!isset($lookup[$key])) {
                continue;
            }

            $selected[] = $lookup[$key];
        }

        return $selected;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function activeModuleTargets(): array
    {
        if (!function_exists('getSystemModules') || !function_exists('getSystemModuleVisibilityMap')) {
            return [];
        }

        $modules = getSystemModules();
        $visibility = getSystemModuleVisibilityMap();

        $targets = [];
        foreach ($modules as $moduleKey => $module) {
            if (empty($visibility[$moduleKey])) {
                continue;
            }

            $moduleName = (string) ($module['name'] ?? ucfirst((string) $moduleKey));
            $route = (string) ($module['route'] ?? '');

            $targets[] = [
                'key' => 'module:' . $moduleKey,
                'label' => $moduleName,
                'description' => 'Active module backup target' . ($route !== '' ? ' (' . $route . ')' : '') . '.',
                // Wildcard marker: exported as full module snapshot (all application tables) unless specific mapping is introduced.
                'tables' => ['*'],
            ];
        }

        return $targets;
    }
}