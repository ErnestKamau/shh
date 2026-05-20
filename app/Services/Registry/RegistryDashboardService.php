<?php

namespace App\Services\Registry;

use App\Repositories\Registry\RegistryDashboardRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class RegistryDashboardService
{
    public function __construct(
        protected RegistryDashboardRepository $repository,
        protected RegistryTATService $tatService,
    ) {
    }

    public function getDashboardData(?string $startDate = null, ?string $endDate = null): array
    {
        $companyId = getUserCompany() ?? 'global';
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth();

        $cacheKey = sprintf('registry.dashboard.%s.%s.%s', $companyId, $start->toDateString(), $end->toDateString());

        return Cache::remember($cacheKey, 300, function () use ($start, $end) {
            $kpis = $this->repository->kpiSummary($start, $end);

            return [
                'kpis' => $kpis,
                'by_category' => $this->repository->byCategory($start, $end),
                'recent_activity' => $this->repository->recentActivity(15),
                'workflow_stage_metrics' => $this->repository->workflowStageMetrics(),
                'average_tat_seconds' => $this->tatService->averageTatSeconds($start, $end),
                'sla_compliance' => $this->calculateSlaCompliance(),
            ];
        });
    }

    public function forgetCache(): void
    {
        $companyId = getUserCompany() ?? 'global';
        Cache::forget('registry.dashboard.' . $companyId . '.*');
    }

    protected function calculateSlaCompliance(): float
    {
        $total = \App\Models\Registry\RegistryRequestStatusLog::query()->whereNotNull('exited_at')->count();
        if ($total === 0) {
            return 100.0;
        }

        $breached = \App\Models\Registry\RegistryRequestStatusLog::query()
            ->whereNotNull('exited_at')
            ->where('sla_breached', true)
            ->count();

        return round((($total - $breached) / $total) * 100, 1);
    }
}
