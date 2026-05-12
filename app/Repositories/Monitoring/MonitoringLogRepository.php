<?php

namespace App\Repositories\Monitoring;

use App\Models\Monitoring\MonitoringLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MonitoringLogRepository
{
    public function todayByScopeAndLab(string $scope, ?string $labId): Collection
    {
        return MonitoringLog::query()
            ->with(['template', 'equipment', 'executedBy'])
            ->where('monitoring_scope', $scope)
            ->whereDate('log_date', Carbon::today())
            ->when($labId, function ($query) use ($labId): void {
                $query->where('lab_id', $labId);
            })
            ->latest('executed_at')
            ->get();
    }

    public function groupedStatusTotals(string $scope, ?string $labId): array
    {
        $query = MonitoringLog::query()
            ->selectRaw('status, COUNT(*) as total')
            ->where('monitoring_scope', $scope)
            ->whereDate('log_date', Carbon::today())
            ->groupBy('status');

        if ($labId !== null && $labId !== '') {
            $query->where('lab_id', $labId);
        }

        return $query->pluck('total', 'status')->toArray();
    }
}
