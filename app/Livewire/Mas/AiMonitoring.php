<?php

namespace App\Livewire\Mas;

use App\Services\AI\PerformanceDashboardService;
use Livewire\Component;

class AiMonitoring extends BaseMasPage
{
    public array $stats = [];
    public array $modelComparison = [];
    public array $intentBreakdown = [];
    public array $costAnalysis = [];
    public array $driftAlerts = [];
    public array $recentLogs = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function refresh(): void
    {
        $this->loadData();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'AI Analytics refreshed successfully.'
        ]);
    }

    public function loadData(): void
    {
        $service = app(PerformanceDashboardService::class);
        
        $this->stats = $service->getDashboardOverview();
        $this->modelComparison = $service->getModelComparison();
        $this->intentBreakdown = $service->getIntentBreakdown();
        $this->costAnalysis = $service->getCostAnalysis();
        $this->driftAlerts = $service->getFeatureDriftAlerts();
        $this->recentLogs = $service->getRecentActivity();
    }

    public function render()
    {
        return view('livewire.mas.ai-monitoring', [
            'stats' => $this->stats,
            'modelComparison' => $this->modelComparison,
            'intentBreakdown' => $this->intentBreakdown,
            'costAnalysis' => $this->costAnalysis,
            'driftAlerts' => $this->driftAlerts,
        ])->layout('layouts.mas.layout.app');
    }
}
