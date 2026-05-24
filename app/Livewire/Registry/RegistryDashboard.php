<?php

namespace App\Livewire\Registry;

use App\Models\Registry\RegistryRequest;
use App\Services\Registry\RegistryDashboardService;
use Livewire\Component;

class RegistryDashboard extends Component
{
    public ?string $startDate = null;

    public ?string $endDate = null;

    public array $dashboardData = [];

    protected $listeners = ['refreshRegistryDashboard' => 'loadData'];

    public function mount(RegistryDashboardService $dashboardService): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->loadData($dashboardService);
    }

    public function updatedStartDate(): void
    {
        $this->loadData();
        $this->dispatch('registry-charts-updated');
    }

    public function updatedEndDate(): void
    {
        $this->loadData();
        $this->dispatch('registry-charts-updated');
    }

    public function loadData(?RegistryDashboardService $dashboardService = null): void
    {
        $service = $dashboardService ?? app(RegistryDashboardService::class);
        $this->dashboardData = $service->getDashboardData($this->startDate, $this->endDate);
    }

    public function render()
    {
        $pendingTasks = RegistryRequest::query()
            ->forCompany()
            ->with('category')
            ->whereIn('status', [RegistryRequest::STATUS_OPEN, RegistryRequest::STATUS_PENDING_APPROVAL])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        return view('livewire.registry.registry-dashboard', [
            'kpis' => $this->dashboardData['kpis'] ?? [],
            'byCategory' => $this->dashboardData['by_category'] ?? [],
            'activities' => $this->dashboardData['recent_activity'] ?? [],
            'stageMetrics' => $this->dashboardData['workflow_stage_metrics'] ?? [],
            'slaCompliance' => $this->dashboardData['sla_compliance'] ?? 100,
            'avgTatHours' => round(((float) ($this->dashboardData['average_tat_seconds'] ?? 0)) / 3600, 1),
            'pendingTasks' => $pendingTasks,
        ]);
    }
}
