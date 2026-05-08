<?php

namespace App\Livewire\Mas;

use App\Services\Dashboards\LabTatDashboardService;

class LabTat extends BaseMasPage
{
    private const FIXED_PERIOD = 'active';

    public ?int $selectedLabId = null;
    public ?int $selectedAnalystId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    public string $activeGridTab = 'my_tasks';

    public array $stats = [];
    public array $available_sections = [];
    public array $available_analysts = [];
    public int $detailedPage = 1;
    public int $perPage = 10;

    public int $gridPage = 1;
    public int $perGridPage = 20;

    public function mount(): void
    {
        $this->available_sections = app(LabTatDashboardService::class)->getLabSectionOptions();
        $this->initializeDefaultLabSelection();
        $this->refreshAvailableAnalysts();
        $this->loadPayload();
    }

    public function loadPayload(): void
    {
        $filters = [
            'lab_id' => $this->selectedLabId,
            'analyst_id' => $this->selectedAnalystId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];

        $this->stats = app(LabTatDashboardService::class)->getTatAnalysisPayload(
            $filters,
            $this->activeGridTab,
            $this->detailedPage,
            $this->perPage,
            self::FIXED_PERIOD,
            $this->gridPage,
            $this->perGridPage
        );

        $this->dispatch('mas-lab-tat-chart-updated', ['slaRate' => (int) ($this->stats['summary']['sla_compliance_rate'] ?? 0)]);
    }

    public function handleLabSelectionChange(): void
    {
        $this->selectedAnalystId = null;
        $this->refreshAvailableAnalysts();
        $this->loadPayload();
    }

    public function applyFilters(): void
    {
        $this->refreshAvailableAnalysts();

        if ($this->selectedAnalystId !== null) {
            $validIds = array_column($this->available_analysts, 'analyst_id');
            if (!in_array($this->selectedAnalystId, $validIds, true)) {
                $this->selectedAnalystId = null;
            }
        }

        $this->detailedPage = 1;
        $this->gridPage = 1;
        $this->loadPayload();
    }

    public function updateDateRange(?string $start, ?string $end): void
    {
        $this->startDate = $start;
        $this->endDate = $end;
        $this->applyFilters();
    }

    public function resetFilters(): void
    {
        $this->selectedAnalystId = null;
        $this->startDate = null;
        $this->endDate = null;
        $this->initializeDefaultLabSelection();
        $this->refreshAvailableAnalysts();
        $this->detailedPage = 1;
        $this->gridPage = 1;
        $this->loadPayload();
    }

    public function export(string $type, bool $preview = false): void
    {
        $params = [
            'module' => 'lab-tat',
            'lab_id' => $this->selectedLabId,
            'analyst_id' => $this->selectedAnalystId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'export_type' => $type,
            'preview' => $preview ? 'true' : 'false',
        ];

        $url = route('mas.export', $params);

        if ($preview) {
            $this->dispatch('open-new-tab', ['url' => $url]);
        } else {
            $this->redirect($url);
        }
    }

    public function setDetailedPage(int $page): void
    {
        $this->detailedPage = $page;
        $this->loadPayload();
    }

    public function setGridTab(string $tab): void
    {
        $this->activeGridTab = $tab;
        $this->gridPage = 1;
        $this->loadPayload();
    }

    public function setGridPage(int $page): void
    {
        $this->gridPage = $page;
        $this->loadPayload();
    }

    public function render()
    {
        return view('livewire.mas.lab-tat', ['stats' => $this->stats])
            ->layout('layouts.mas.layout.app');
    }

    private function refreshAvailableAnalysts(): void
    {
        $this->available_analysts = app(LabTatDashboardService::class)->getAvailableAnalysts(
            $this->selectedLabId,
            self::FIXED_PERIOD,
            [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
            ]
        );
    }

    private function initializeDefaultLabSelection(): void
    {
        $this->selectedLabId = null;
    }
}
