<?php

namespace App\Livewire\Registry;

use Livewire\Component;

class RegistryRecentActivity extends Component
{
    public array $activities = [];

    public function mount(array $activities = []): void
    {
        $this->activities = $activities;
    }

    public function render()
    {
        return view('livewire.registry.registry-recent-activity');
    }
}
