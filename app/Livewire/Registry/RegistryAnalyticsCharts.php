<?php

namespace App\Livewire\Registry;

use Livewire\Component;

class RegistryAnalyticsCharts extends Component
{
    public array $byCategory = [];

    public function mount(array $byCategory = []): void
    {
        $this->byCategory = $byCategory;
    }

    public function render()
    {
        return view('livewire.registry.registry-analytics-charts');
    }
}
