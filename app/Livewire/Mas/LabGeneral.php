<?php

namespace App\Livewire\Mas;

use App\Services\Dashboards\LabGeneralDashboardService;
use App\Services\Dashboards\LabTatDashboardService;
use Illuminate\Support\Facades\DB;

class LabGeneral extends BaseMasPage
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
        $tatService     = app(LabTatDashboardService::class);
        $generalService = app(LabGeneralDashboardService::class);

        $labBoard = $tatService->getLabTatBoard();
        
        $this->stats = [
            'sample_type_distribution' => $labBoard['sample_type_distribution'] ?? [],
            'geographic_data'          => $generalService->getLabGeographicData(),
            'monthly_trends'           => $generalService->getLabMonthlyTrends(),
            'top_clients'              => $generalService->getTopClientsData(8),
            'testing_matrix'           => $generalService->getTestingMatrixData(),
            'charts'                   => [
                'type_labels'    => $labBoard['charts']['type_labels'] ?? [],
                'type_counts'    => $labBoard['charts']['type_counts'] ?? [],
                'client_labels'  => $generalService->getTopClientsData(8)->pluck('name')->all(),
                'client_counts'  => $generalService->getTopClientsData(8)->pluck('total')->all(),
            ],
            'summary'                  => $labBoard['summary'] ?? [],
            'stage_summary'            => $labBoard['stage_summary'] ?? [],
            'stage_counts'             => $labBoard['stage_counts'] ?? [],
        ];
    }

    public function render()
    {
        return view('livewire.mas.lab-general', ['stats' => $this->stats]);
    }
}
