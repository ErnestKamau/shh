<?php

namespace App\Livewire\Mas;

use App\Services\Documents\Dashboards\EquipmentReliabilityDashboardService;

class Equipment extends BaseMasPage
{
    public array $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function refresh(): void
    {
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $this->stats = app(EquipmentReliabilityDashboardService::class)->getMetrics();
    }

    public function render()
    {
        return view('livewire.mas.equipment', ['stats' => $this->stats]);
    }
}
