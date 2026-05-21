<?php

namespace App\Repositories\Registry;

use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestStatusLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RegistryDashboardRepository
{
    public function kpiSummary(?Carbon $start = null, ?Carbon $end = null): array
    {
        $query = RegistryRequest::query()->forCompany();

        if ($start !== null) {
            $query->where('created_at', '>=', $start);
        }
        if ($end !== null) {
            $query->where('created_at', '<=', $end);
        }

        $base = clone $query;

        return [
            'total' => (clone $base)->count(),
            'open' => (clone $base)->open()->count(),
            'pending_approval' => (clone $base)->where('status', RegistryRequest::STATUS_PENDING_APPROVAL)->count(),
            'completed' => (clone $base)->where('status', RegistryRequest::STATUS_CLOSED)->count(),
            'delayed' => (clone $base)->open()->where('updated_at', '<', now()->subDays(7))->count(),
            'received_today' => RegistryRequest::query()->forCompany()->whereDate('received_at', today())->count(),
            'received_month' => RegistryRequest::query()->forCompany()->whereMonth('received_at', now()->month)->count(),
        ];
    }

    public function byCategory(?Carbon $start = null, ?Carbon $end = null): array
    {
        $query = RegistryRequest::query()->forCompany();

        if ($start !== null) {
            $query->where('created_at', '>=', $start);
        }
        if ($end !== null) {
            $query->where('created_at', '<=', $end);
        }

        return $query
            ->select('request_category_id', DB::raw('count(*) as total'))
            ->groupBy('request_category_id')
            ->with('category:id,name,code')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category?->name ?? 'Unknown',
                'total' => (int) $row->total,
            ])
            ->all();
    }

    public function recentActivity(int $limit = 10): array
    {
        return RegistryRequest::query()
            ->forCompany()
            ->with(['category', 'assignee'])
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function workflowStageMetrics(): array
    {
        return RegistryRequestStatusLog::query()
            ->select('stage_code', DB::raw('AVG(duration_seconds) as avg_seconds'), DB::raw('COUNT(*) as total'))
            ->whereNotNull('duration_seconds')
            ->groupBy('stage_code')
            ->orderByDesc('avg_seconds')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'stage' => $row->stage_code,
                'avg_hours' => round(((float) $row->avg_seconds) / 3600, 1),
                'total' => (int) $row->total,
            ])
            ->all();
    }
}
