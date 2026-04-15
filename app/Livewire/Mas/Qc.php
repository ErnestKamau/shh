<?php

namespace App\Livewire\Mas;

use App\Services\AI\Repository\ReportingMartDashboardService;

class Qc extends BaseMasPage
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
        $this->stats = app(ReportingMartDashboardService::class)->getQcStabilityBoard();
    }

    public function render()
    {
        return view('livewire.mas.qc', ['stats' => $this->stats]);
    }
}
