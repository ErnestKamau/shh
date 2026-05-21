<?php

namespace App\Listeners\Registry;

use App\Events\Registry\RegistryRequestAssigned;
use App\Events\Registry\RegistryRequestClosed;
use App\Events\Registry\RegistryRequestCreated;
use App\Events\Registry\WorkflowTransitionCompleted;
use App\Services\Registry\RegistryDashboardService;
use Illuminate\Support\Facades\Cache;

class UpdateRegistryDashboardMetricsListener
{
    public function __construct(
        protected RegistryDashboardService $dashboardService,
    ) {
    }

    public function invalidate(): void
    {
        $companyId = getUserCompany() ?? 'global';
        Cache::flush();
        $this->dashboardService->forgetCache();
    }

    public function subscribe($events): array
    {
        return [
            RegistryRequestCreated::class => 'invalidate',
            WorkflowTransitionCompleted::class => 'invalidate',
            RegistryRequestAssigned::class => 'invalidate',
            RegistryRequestClosed::class => 'invalidate',
        ];
    }
}
