<?php

namespace App\Livewire\Mas;

use App\Services\AI\Repository\ReportingMartDashboardService;

class LabQc extends BaseMasPage
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
        $service = app(ReportingMartDashboardService::class);
        $this->stats = $service->getQcStabilityBoard();
        $this->stats['parameter_performance'] = $service->getParameterPerformanceData();
        $this->stats['testing_matrix'] = $service->getTestingMatrixData();
    }

    public function render()
    {
        return view('livewire.mas.lab-qc', ['stats' => $this->stats]);
    }
}
