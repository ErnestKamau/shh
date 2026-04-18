<?php

namespace App\Livewire\Mas;

use App\Services\AI\Repository\ReportingMartDashboardService;
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
        $service  = app(ReportingMartDashboardService::class);
        $labBoard = $service->getLabTatBoard();
        
        $this->stats = [
            'sample_type_distribution' => $labBoard['sample_type_distribution'] ?? [],
            'geographic_data'          => $service->getLabGeographicData(),
            'monthly_trends'           => $service->getLabMonthlyTrends(),
            'charts'                   => [
                'type_labels'    => $labBoard['charts']['type_labels'] ?? [],
                'type_counts'    => $labBoard['charts']['type_counts'] ?? [],
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
