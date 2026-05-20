<?php

namespace App\Services\Dashboard;

use Closure;
use Illuminate\Support\Facades\Cache;

class DashboardCacheService
{
    public function rememberSummary(string $customerId, Closure $callback): mixed
    {
        return $this->remember("dashboard:summary:{$customerId}", config('dashboard.cache.summary_ttl'), $callback);
    }

    public function rememberAnalytics(string $customerId, Closure $callback): mixed
    {
        return $this->remember("dashboard:analytics:{$customerId}", config('dashboard.cache.analytics_ttl'), $callback);
    }

    public function rememberNotifications(string $customerId, int $page, Closure $callback): mixed
    {
        return $this->remember(
            "dashboard:notifications:{$customerId}:page:{$page}",
            config('dashboard.cache.notifications_ttl'),
            $callback
        );
    }

    public function rememberList(string $type, string $customerId, int $page, Closure $callback): mixed
    {
        return $this->remember(
            "dashboard:{$type}:{$customerId}:page:{$page}",
            config('dashboard.cache.lists_ttl'),
            $callback
        );
    }

    public function forgetCustomer(string $customerId): void
    {
        $patterns = [
            "dashboard:summary:{$customerId}",
            "dashboard:analytics:{$customerId}",
        ];

        foreach ($patterns as $key) {
            Cache::store($this->store())->forget($key);
        }
    }

    private function remember(string $key, int $ttlSeconds, Closure $callback): mixed
    {
        return Cache::store($this->store())->remember($key, $ttlSeconds, $callback);
    }

    private function store(): string
    {
        return (string) config('dashboard.cache.store', config('cache.default', 'file'));
    }
}
