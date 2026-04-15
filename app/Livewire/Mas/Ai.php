<?php

namespace App\Livewire\Mas;

use App\Services\AI\PerformanceDashboardService;
use App\Services\AI\Repository\ReportingMartDashboardService;

class Ai extends BaseMasPage
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
        $perfService      = app(PerformanceDashboardService::class);
        $reportingService = app(ReportingMartDashboardService::class);

        $govData    = $perfService->getMLModelMetrics();
        $driftAlerts = $perfService->getFeatureDriftAlerts();
        $intents    = $perfService->getIntentBreakdown();
        $labTat     = $reportingService->getLabTatBoard();
        $qcStability = $reportingService->getQcStabilityBoard();
        $inventoryRisk = $reportingService->getInventoryRiskBoard();

        $this->stats = [
            'performance' => $govData['overview'] ?? [],
            'models'      => [
                'models'   => $govData['performance'] ?? [],
                'registry' => $govData['models'] ?? [],
            ],
            'alerts'      => array_merge(
                $govData['alerts'] ?? [],
                array_map(static fn ($drift): array => [
                    'severity' => 'danger',
                    'type'     => 'drift',
                    'message'  => $drift['alert'] ?? 'Feature drift detected',
                ], $driftAlerts)
            ),
            'intents'      => $intents,
            'lims_insights' => [
                'tat'       => $labTat,
                'qc'        => $qcStability,
                'inventory' => $inventoryRisk,
            ],
        ];
    }

    public function render()
    {
        return view('livewire.mas.ai', ['stats' => $this->stats]);
    }
}
