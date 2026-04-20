<?php

namespace App\Livewire\Mas;

use App\Services\Dashboards\InventoryDashboardService;

class Inventory extends BaseMasPage
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
        $this->stats = app(InventoryDashboardService::class)->getInventoryRiskBoard();
    }

    public function render()
    {
        return view('livewire.mas.inventory', ['stats' => $this->stats]);
    }
}
