<?php

namespace App\Livewire\System;

use App\Services\System\SystemAdminDashboardService;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminDashboard extends Component
{
    /** @var array<string, mixed> */
    public array $metrics = [];

    /** @var array<string, mixed> */
    public array $logStream = [];

    public function mount(SystemAdminDashboardService $service): void
    {
        $this->authorizeAction('system.dashboard.view');
        $this->reloadDashboard($service, false);
        $this->reloadLogStream($service);
    }

    public function refreshMetrics(SystemAdminDashboardService $service, bool $forceRefresh = true): void
    {
        $this->reloadDashboard($service, $forceRefresh);
        $this->reloadLogStream($service);
    }

    #[On('dashboard-metrics-refresh')]
    public function handleDashboardRefresh(SystemAdminDashboardService $service): void
    {
        $this->reloadDashboard($service, true);
        $this->reloadLogStream($service);
    }

    public function refreshLogStream(SystemAdminDashboardService $service): void
    {
        $this->reloadLogStream($service);
    }

    public function retryFailedJobs(): void
    {
        $this->authorizeAction('system.dashboard.actions');

        Artisan::call('queue:retry all');
        $service = app(SystemAdminDashboardService::class);
        $this->reloadDashboard($service, true);
        $this->reloadLogStream($service);

        session()->flash('success', __('system.failed_jobs_retry_triggered'));
    }

    public function runSchedulerNow(): void
    {
        $this->authorizeAction('system.dashboard.actions');

        Artisan::call('schedule:run');
        $service = app(SystemAdminDashboardService::class);
        $this->reloadDashboard($service, true);
        $this->reloadLogStream($service);

        session()->flash('success', __('system.scheduler_run_triggered'));
    }

    public function getCanManageActionsProperty(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin())
            || $user->can('system.dashboard.actions');
    }

    public function getCanExportProperty(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin())
            || $user->can('system.dashboard.export');
    }

    public function render()
    {
        return view('livewire.system.admin-dashboard');
    }

    private function reloadDashboard(SystemAdminDashboardService $service, bool $forceRefresh): void
    {
        $this->metrics = $service->snapshot($forceRefresh);
        $this->dispatch('system-dashboard-charts-refresh', chartData: $this->metrics['charts'] ?? []);
    }

    private function reloadLogStream(SystemAdminDashboardService $service): void
    {
        $this->logStream = $service->currentLogStream();
    }

    private function authorizeAction(string $permission): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if ((method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) || $user->can($permission)) {
            return;
        }

        abort(403);
    }
}
