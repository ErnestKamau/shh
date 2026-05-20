<?php

namespace App\Services\Dashboard;

use App\DTOs\Dashboard\AnalyticsDTO;
use App\DTOs\Dashboard\DashboardDTO;
use App\Jobs\Dashboard\RecalculateDashboardAnalyticsJob;
use App\Repositories\DashboardRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepository $repository,
        private readonly DashboardCacheService $cache,
    ) {}

    public function getDashboard(string $customerId, ?string $portalAccountId = null): DashboardDTO
    {
        $this->logAccess('dashboard.show', $customerId, $portalAccountId);

        return $this->cache->rememberSummary($customerId, fn (): DashboardDTO => $this->repository->buildDashboard($customerId));
    }

    public function getAnalytics(string $customerId, ?string $portalAccountId = null, bool $queueRecalculation = false): AnalyticsDTO
    {
        $this->logAccess('dashboard.analytics', $customerId, $portalAccountId);

        if ($queueRecalculation) {
            RecalculateDashboardAnalyticsJob::dispatch($customerId);
        }

        return $this->cache->rememberAnalytics(
            $customerId,
            fn (): AnalyticsDTO => $this->repository->buildAnalytics($customerId)
        );
    }

    public function getNotifications(string $customerId, int $page, int $perPage, ?string $portalAccountId = null): LengthAwarePaginator
    {
        $this->logAccess('dashboard.notifications', $customerId, $portalAccountId);

        return $this->cache->rememberNotifications($customerId, $page, function () use ($customerId, $perPage) {
            return $this->repository->paginateNotifications($customerId, $perPage);
        });
    }

    public function getReports(string $customerId, int $page, int $perPage, ?string $portalAccountId = null): LengthAwarePaginator
    {
        $this->logAccess('dashboard.reports', $customerId, $portalAccountId);

        return $this->cache->rememberList('reports', $customerId, $page, function () use ($customerId, $perPage) {
            return $this->repository->paginateReports($customerId, $perPage);
        });
    }

    public function getComplaints(string $customerId, int $page, int $perPage, ?string $portalAccountId = null): LengthAwarePaginator
    {
        $this->logAccess('dashboard.complaints', $customerId, $portalAccountId);

        return $this->cache->rememberList('complaints', $customerId, $page, function () use ($customerId, $perPage) {
            return $this->repository->paginateComplaints($customerId, $perPage);
        });
    }

    public function refreshAnalyticsCache(string $customerId): AnalyticsDTO
    {
        $this->cache->forgetCustomer($customerId);

        return $this->repository->buildAnalytics($customerId);
    }

    private function logAccess(string $action, string $customerId, ?string $portalAccountId): void
    {
        Log::channel('daily')->info('portal.dashboard.access', [
            'action' => $action,
            'customer_id' => $customerId,
            'portal_account_id' => $portalAccountId,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
