<?php

namespace App\Livewire\System;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;
use Livewire\Component;

class ModuleVisibilityManager extends Component
{
    /**
     * Map dashboard module keys to nwidart package module names where applicable.
     * Keys not present here will be resolved heuristically (e.g. studly-case).
     */
    private const PACKAGE_MODULE_KEY_MAP = [
        'quality_control' => 'QualityControl',
        'qualitycontrol' => 'QualityControl',
        'template_engine' => 'TemplateEngine',
        'templateengine' => 'TemplateEngine',
        'prp' => 'Prp',
    ];

    public array $modules = [];

    public array $visibility = [];

    public function mount(): void
    {
        $this->authorizeAction('system.module-switching.view');

        $this->modules = getSystemModules();
        $this->visibility = getSystemModuleVisibilityMap();
    }

    public function save(): void
    {
        $this->authorizeAction('system.module-switching.edit');

        try {
            $configType = SystemConfigurationsType::firstOrCreate(
                ['configuration_type' => 'Module Visibility'],
                [
                    'description' => 'Controls which application modules are visible and enabled',
                    'status' => true,
                ]
            );

            $errors = [];
            $packageChanges = [];
            $normalizedVisibility = [];

            foreach (array_keys($this->modules) as $moduleKey) {
                $normalizedVisibility[$moduleKey] = !empty($this->visibility[$moduleKey]);
            }

            // Force System Settings to always be visible
            $normalizedVisibility['settings'] = true;

            $configuration = SystemConfiguration::where('configuration_type_id', $configType->id)
                ->where('key', 'system_module_visibility')
                ->first();

            if (!$configuration) {
                $configuration = new SystemConfiguration();
                $configuration->configuration_type_id = $configType->id;
                $configuration->key = 'system_module_visibility';
            }

            $configuration->value = json_encode($normalizedVisibility, JSON_UNESCAPED_UNICODE);
            $configuration->status = 1;
            $configuration->save();

            foreach (array_keys($this->modules) as $moduleKey) {
                $isVisible = $normalizedVisibility[$moduleKey] ?? false;

                $packageResult = $this->syncPackageModuleStatus($moduleKey, $isVisible);

                if (!empty($packageResult['error'])) {
                    $errors[] = (string) $packageResult['error'];
                    continue;
                }

                if (!empty($packageResult['changed']) && !empty($packageResult['module'])) {
                    $packageChanges[] = sprintf('%s (%s)', $packageResult['module'], $isVisible ? 'enabled' : 'disabled');
                }
            }

            if (!empty($errors)) {
                $message = 'Module settings saved with package activation errors: '.implode(' | ', $errors);
                session()->flash('error', $message);
                $this->dispatch('module-visibility-save-failed', message: $message);
                return;
            }

            $message = 'Module visibility settings updated successfully.';

            if (!empty($packageChanges)) {
                $message .= ' Package modules updated: '.implode(', ', $packageChanges).'.';
            }

            session()->flash('success', $message);
            $this->dispatch('module-visibility-saved');
        } catch (Throwable $e) {
            report($e);
            $message = 'Failed to save module settings. Please try again. Error: '.$e->getMessage();
            session()->flash('error', $message);
            $this->dispatch('module-visibility-save-failed', message: $message);
        }
    }

    public function render()
    {
        return view('livewire.system.module-visibility-manager');
    }

    private function authorizeAction(string $permission): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        if ((method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) || $user->can($permission)) {
            return;
        }

        abort(403);
    }

    /**
     * @return array{changed: bool, module: string|null, error: string|null}
     */
    private function syncPackageModuleStatus(string $moduleKey, bool $shouldEnable): array
    {
        $packageModuleName = null;

        try {
            $packageModuleName = $this->resolvePackageModuleName($moduleKey);

            if ($packageModuleName === null) {
                return ['changed' => false, 'module' => null, 'error' => null];
            }

            $modules = app('modules');
            $module = $modules->find($packageModuleName);

            if (!$module) {
                return ['changed' => false, 'module' => $packageModuleName, 'error' => "Package module '{$packageModuleName}' was not found."];
            }

            $isEnabled = $module->isEnabled();

            if ($shouldEnable && !$isEnabled) {
                $modules->enable($packageModuleName);
                return ['changed' => true, 'module' => $packageModuleName, 'error' => null];
            }

            if (!$shouldEnable && $isEnabled) {
                $modules->disable($packageModuleName);
                return ['changed' => true, 'module' => $packageModuleName, 'error' => null];
            }

            return ['changed' => false, 'module' => $packageModuleName, 'error' => null];
        } catch (Throwable $e) {
            report($e);
            return [
                'changed' => false,
                'module' => $packageModuleName,
                'error' => "Failed to update package module '{$packageModuleName}': {$e->getMessage()}",
            ];
        }
    }

    private function resolvePackageModuleName(string $moduleKey): ?string
    {
        if (!app()->bound('modules')) {
            return null;
        }

        $modules = app('modules');

        $candidates = array_values(array_unique(array_filter([
            self::PACKAGE_MODULE_KEY_MAP[$moduleKey] ?? null,
            $moduleKey,
            Str::studly($moduleKey),
            Str::studly(str_replace(['-', '_'], ' ', $moduleKey)),
        ])));

        foreach ($candidates as $candidate) {
            if ($modules->has($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
