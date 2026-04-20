<?php

namespace App\Livewire\Mas;

use App\Services\Dashboards\LabLogisticsDashboardService;

class LabLogistics extends BaseMasPage
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
        $this->stats = app(LabLogisticsDashboardService::class)->getLabLogisticsSummary();
    }

    public function render()
    {
        return view('livewire.mas.lab-logistics', ['stats' => $this->stats]);
    }
}
