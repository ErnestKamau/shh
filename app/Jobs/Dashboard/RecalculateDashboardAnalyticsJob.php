<?php

namespace App\Jobs\Dashboard;

use App\Services\Dashboard\DashboardCacheService;
use App\Services\Dashboard\DashboardService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RecalculateDashboardAnalyticsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $customerId,
    ) {}

    public function handle(DashboardService $dashboardService, DashboardCacheService $cache): void
    {
        $cache->forgetCustomer($this->customerId);

        $analytics = $dashboardService->refreshAnalyticsCache($this->customerId);

        Cache::store((string) config('dashboard.cache.store', config('cache.default', 'file')))->put(
            "dashboard:analytics:{$this->customerId}",
            $analytics->toArray(),
            config('dashboard.cache.analytics_ttl', 120)
        );
    }
}
