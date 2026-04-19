<?php

namespace App\Livewire\Mas;

use App\Services\Dashboards\LabGeneralDashboardService;
use App\Services\Dashboards\QcDashboardService;

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
        $qcService      = app(QcDashboardService::class);
        $generalService = app(LabGeneralDashboardService::class);

        $this->stats = $qcService->getQcStabilityBoard();
        $this->stats['parameter_performance'] = $qcService->getParameterPerformanceData();
        $this->stats['testing_matrix'] = $generalService->getTestingMatrixData();
    }

    public function render()
    {
        return view('livewire.mas.lab-qc', ['stats' => $this->stats]);
    }
}
