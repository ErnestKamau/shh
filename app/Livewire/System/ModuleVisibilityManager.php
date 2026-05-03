<?php

namespace App\Livewire\System;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Throwable;
use Livewire\Component;

class ModuleVisibilityManager extends Component
{
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
        $this->authorizeAction('system.module-switching.view');

        try {
            $configType = SystemConfigurationsType::where('configuration_type', 'Module Visibility')->first();
            if (!$configType) {
                $configType = new SystemConfigurationsType();
                $configType->configuration_type = 'Module Visibility';
                $configType->status = 1;
                $configType->save();
            }

            foreach (array_keys($this->modules) as $moduleKey) {
                $configuration = SystemConfiguration::where('configuration_type_id', $configType->id)
                    ->where('key', 'system_module_visibility')
                    ->where('value', $moduleKey)
                    ->first();

                if (!$configuration) {
                    $configuration = new SystemConfiguration();
                    $configuration->configuration_type_id = $configType->id;
                    $configuration->key = 'system_module_visibility';
                    $configuration->value = $moduleKey;
                }

                $configuration->status = !empty($this->visibility[$moduleKey]) ? 1 : 0;
                $configuration->save();
            }

            session()->flash('success', 'Module visibility settings updated successfully.');
        } catch (Throwable $e) {
            report($e);
            session()->flash('error', 'Failed to save module settings. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.system.module-visibility-manager');
    }

    private function authorizeAction(string $permission): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if ((method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) || $user->can($permission)) {
            return;
        }

        abort(403);
    }
}
