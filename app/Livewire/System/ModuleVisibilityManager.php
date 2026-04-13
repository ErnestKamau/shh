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
        abort_unless(auth()->check() && auth()->user()->is_support_staff, 403);

        $this->modules = getSystemModules();
        $this->visibility = getSystemModuleVisibilityMap();
    }

    public function save(): void
    {
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
}
