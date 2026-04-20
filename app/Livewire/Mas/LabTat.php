<?php

namespace App\Livewire\Mas;

use App\Services\Dashboards\LabTatDashboardService;
use Livewire\Attributes\Url;

class LabTat extends BaseMasPage
{
    #[Url]
    public string $period = 'active';

    public string $activeGridTab = 'my_tasks';

    public array $stats = [];
    public array $detailed_logs = [];
    public int $detailedPage = 1;
    public int $perPage = 10;
    
    public int $gridPage = 1;
    public int $perGridPage = 20;

    public function mount(): void
    {
        $this->loadStats();
    }

    public function setPeriod(string $period): void
    {
        $allowed = ['active', 'week', 'month', 'year', 'lifetime'];
        if (in_array($period, $allowed, true)) {
            $this->period = $period;
            $this->detailedPage = 1;
            $this->gridPage = 1;
            $this->loadStats();
            $this->dispatch('period-changed', stats: $this->stats);
        }
    }

    private function loadStats(): void
    {
        $service = app(LabTatDashboardService::class);
        $this->stats = $service->getLabTatBoard($this->period);
        $this->stats['sections'] = $service->getLabSectionTatStats();
        $this->stats['analyst_performance'] = $service->getAnalystPerformanceStats($this->period);
        $this->stats['smart_grid'] = $service->getSmartActionGridData($this->activeGridTab);
        
        // Fetch top 50 detailed logs for the period
        $this->detailed_logs = $service->getDetailedAnalyteTatLogs($this->period, 50);
    }

    public function setDetailedPage(int $page): void
    {
        $this->detailedPage = $page;
    }

    public function getPaginatedDetailedLogs(): array
    {
        $offset = ($this->detailedPage - 1) * $this->perPage;
        return array_slice($this->detailed_logs, $offset, $this->perPage);
    }

    public function setGridTab(string $tab): void
    {
        $this->activeGridTab = $tab;
        $this->gridPage = 1;
        $this->stats['smart_grid'] = app(LabTatDashboardService::class)->getSmartActionGridData($tab);
    }

    public function setGridPage(int $page): void
    {
        $this->gridPage = $page;
    }

    public function getPaginatedGridData(): array
    {
        $offset = ($this->gridPage - 1) * $this->perGridPage;
        return array_slice($this->stats['smart_grid'] ?? [], $offset, $this->perGridPage);
    }

    public function getSmartGridData(string $tab): array
    {
        return app(LabTatDashboardService::class)->getSmartActionGridData($tab);
    }

    public function render()
    {
        return view('livewire.mas.lab-tat', ['stats' => $this->stats]);
    }
}
