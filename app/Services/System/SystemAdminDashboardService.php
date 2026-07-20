<?php

namespace App\Services\System;

use Cron\CronExpression;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SystemAdminDashboardService
{
    public function snapshot(bool $forceRefresh = false): array
    {
        $cacheKey = 'system-admin-dashboard:snapshot';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addSeconds(20), function (): array {
            $queue = $this->queueMetrics();
            $complaints = $this->complaintMetrics();
            $scheduler = $this->schedulerMetrics();
            $resolutionQueue = $this->complaintResolutionQueueMetrics();
            $commandLatency = $this->commandLatencyMetrics();
            $databaseConnections = $this->databaseConnectionsMetrics();
            $serviceIncidents = $this->serviceIncidentsMetrics($queue, $complaints, $resolutionQueue, $commandLatency, $databaseConnections);
            $charts = $this->charts($resolutionQueue, $commandLatency, $databaseConnections, $serviceIncidents);

            return [
                'generated_at' => now()->toDateTimeString(),
                'overview' => [
                    'users_total' => $this->countSafe('users'),
                    'active_modules' => $this->activeModulesCount(),
                    'pending_jobs' => Arr::get($queue, 'pending_jobs', 0),
                    'failed_jobs' => Arr::get($queue, 'failed_jobs', 0),
                    'open_complaints' => Arr::get($complaints, 'open_count', 0),
                    'scheduled_commands' => Arr::get($scheduler, 'total_events', 0),
                    'connected_databases' => Arr::get($databaseConnections, 'connected_count', 0),
                    'configured_databases' => Arr::get($databaseConnections, 'configured_count', 0),
                ],
                'queue' => $queue,
                'complaints' => $complaints,
                'complaint_resolution' => $resolutionQueue,
                'scheduler' => $scheduler,
                'command_latency' => $commandLatency,
                'database_connections' => $databaseConnections,
                'service_incidents' => $serviceIncidents,
                'charts' => $charts,
                'health' => $this->healthMetrics(Arr::get($queue, 'failed_jobs', 0), Arr::get($databaseConnections, 'disconnected_count', 0)),
            ];
        });
    }

    public function currentLogStream(int $maxLines = 120, int $maxBytes = 262144): array
    {
        $path = $this->resolveCurrentLogFilePath();

        if (!$path) {
            return [
                'status' => 'missing',
                'file_name' => null,
                'updated_at' => null,
                'lines' => [],
            ];
        }

        $updatedAt = $this->formatLogTimestamp($path);

        if (!is_readable($path)) {
            return [
                'status' => 'unreadable',
                'file_name' => basename($path),
                'updated_at' => $updatedAt,
                'lines' => [],
            ];
        }

        $tail = $this->readLogTail($path, $maxBytes);

        if ($tail === null) {
            return [
                'status' => 'unreadable',
                'file_name' => basename($path),
                'updated_at' => $updatedAt,
                'lines' => [],
            ];
        }

        $lines = preg_split('/\r\n|\r|\n/', $tail['contents']) ?: [];

        if ($tail['truncated'] && count($lines) > 0) {
            array_shift($lines);
        }

        if (!empty($lines) && end($lines) === '') {
            array_pop($lines);
        }

        return [
            'status' => 'ok',
            'file_name' => basename($path),
            'updated_at' => $updatedAt,
            'lines' => array_values(array_slice($lines, -$maxLines)),
        ];
    }

    private function queueMetrics(): array
    {
        $failedJobsCount = 0;
        $failedLastHour = 0;

        if (Schema::hasTable('failed_jobs')) {
            $failedJobsCount = DB::table('failed_jobs')->count();

            if (Schema::hasColumn('failed_jobs', 'failed_at')) {
                $failedLastHour = DB::table('failed_jobs')
                    ->where('failed_at', '>=', now()->subHour())
                    ->count();
            }
        }

        $pendingJobs = 0;
        $reservedJobs = 0;
        $queueBreakdown = [];

        if (Schema::hasTable('jobs')) {
            $pendingJobs = DB::table('jobs')->count();

            if (Schema::hasColumn('jobs', 'reserved_at')) {
                $reservedJobs = DB::table('jobs')->whereNotNull('reserved_at')->count();
            }

            if (Schema::hasColumn('jobs', 'queue')) {
                $queueBreakdown = DB::table('jobs')
                    ->select('queue', DB::raw('count(*) as total'))
                    ->groupBy('queue')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get()
                    ->map(fn ($row): array => [
                        'queue' => (string) ($row->queue ?: 'default'),
                        'total' => (int) $row->total,
                    ])
                    ->all();
            }
        }

        return [
            'failed_jobs' => $failedJobsCount,
            'failed_last_hour' => $failedLastHour,
            'pending_jobs' => $pendingJobs,
            'reserved_jobs' => $reservedJobs,
            'queue_breakdown' => $queueBreakdown,
        ];
    }

    private function schedulerMetrics(): array
    {
        $events = $this->scheduledEvents();
        $nextRuns = [];

        foreach (array_slice($events, 0, 6) as $event) {
            $expression = (string) ($event->expression ?? '* * * * *');
            $summary = method_exists($event, 'getSummaryForDisplay')
                ? (string) $event->getSummaryForDisplay()
                : (string) ($event->command ?? 'Scheduled command');

            $nextRun = null;

            try {
                $nextRun = CronExpression::factory($expression)
                    ->getNextRunDate(now())
                    ->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                $nextRun = null;
            }

            $nextRuns[] = [
                'summary' => $summary,
                'expression' => $expression,
                'next_run' => $nextRun,
            ];
        }

        return [
            'total_events' => count($events),
            'next_runs' => $nextRuns,
        ];
    }

    private function complaintMetrics(): array
    {
        if (!Schema::hasTable('complaints')) {
            return [
                'total_count' => 0,
                'open_count' => 0,
                'high_priority_open' => 0,
                'unresolved_older_48h' => 0,
                'recent' => [],
            ];
        }

        $baseQuery = DB::table('complaints');
        $openQuery = DB::table('complaints');

        if (Schema::hasColumn('complaints', 'deleted_at')) {
            $baseQuery->whereNull('deleted_at');
            $openQuery->whereNull('deleted_at');
        }

        $totalCount = $baseQuery->count();

        if (Schema::hasColumn('complaints', 'is_closed')) {
            $openQuery->where(function ($query): void {
                $query->whereNull('is_closed')->orWhere('is_closed', false);
            });
        }

        $openCount = $openQuery->count();

        $highPriorityOpen = 0;
        if (Schema::hasColumn('complaints', 'priority')) {
            $highPriorityOpen = (clone $openQuery)
                ->whereRaw("LOWER(COALESCE(priority, '')) in (?, ?, ?)", ['high', 'critical', 'urgent'])
                ->count();
        }

        $unresolvedOlder48h = 0;
        if (Schema::hasColumn('complaints', 'created_at')) {
            $unresolvedOlder48h = (clone $openQuery)
                ->where('created_at', '<=', now()->subHours(48))
                ->count();
        }

        $recent = [];
        if (Schema::hasColumn('complaints', 'created_at')) {
            $recent = DB::table('complaints')
                ->select(['id', 'complaint_id', 'priority', 'is_closed', 'created_at'])
                ->when(Schema::hasColumn('complaints', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
                ->orderByDesc('created_at')
                ->limit(6)
                ->get()
                ->map(fn ($row): array => [
                    'id' => (string) $row->id,
                    'complaint_id' => (string) ($row->complaint_id ?? $row->id),
                    'priority' => (string) ($row->priority ?? 'normal'),
                    'is_closed' => (bool) ($row->is_closed ?? false),
                    'created_at' => $row->created_at ? (string) $row->created_at : null,
                ])
                ->all();
        }

        return [
            'total_count' => $totalCount,
            'open_count' => $openCount,
            'high_priority_open' => $highPriorityOpen,
            'unresolved_older_48h' => $unresolvedOlder48h,
            'recent' => $recent,
        ];
    }

    private function complaintResolutionQueueMetrics(): array
    {
        if (!Schema::hasTable('complaints')) {
            return [
                'open_total' => 0,
                'stalled_24h' => 0,
                'overdue_sla' => 0,
                'escalated_open' => 0,
                'stage_breakdown' => [],
            ];
        }

        $openQuery = DB::table('complaints');
        if (Schema::hasColumn('complaints', 'deleted_at')) {
            $openQuery->whereNull('deleted_at');
        }
        $this->applyOpenComplaintScope($openQuery);

        $openTotal = (clone $openQuery)->count();

        $stalled24h = 0;
        if (Schema::hasColumn('complaints', 'created_at')) {
            $stalled24h = (clone $openQuery)
                ->where('created_at', '<=', now()->subDay())
                ->count();
        }

        $overdueSla = 0;
        if (Schema::hasColumn('complaints', 'resolution_sla_status')) {
            $overdueSla = (clone $openQuery)
                ->whereRaw("LOWER(COALESCE(resolution_sla_status, '')) similar to ?", ['%(breach|overdue|failed)%'])
                ->count();
        }

        $escalatedOpen = 0;
        if (Schema::hasColumn('complaints', 'escalated_to_user_id')) {
            $escalatedOpen = (clone $openQuery)
                ->whereNotNull('escalated_to_user_id')
                ->count();
        }

        $stageBreakdown = [];
        if (Schema::hasColumn('complaints', 'complaint_workflow')) {
            $stageBreakdown = (clone $openQuery)
                ->select('complaint_workflow', DB::raw('count(*) as total'))
                ->groupBy('complaint_workflow')
                ->orderBy('complaint_workflow')
                ->get()
                ->map(fn ($row): array => [
                    'stage' => is_null($row->complaint_workflow)
                        ? 'Unassigned'
                        : 'Stage ' . (string) $row->complaint_workflow,
                    'value' => (int) $row->total,
                ])
                ->all();
        }

        return [
            'open_total' => $openTotal,
            'stalled_24h' => $stalled24h,
            'overdue_sla' => $overdueSla,
            'escalated_open' => $escalatedOpen,
            'stage_breakdown' => $stageBreakdown,
        ];
    }

    private function commandLatencyMetrics(): array
    {
        $avgWaitSeconds = 0;
        $oldestPendingSeconds = 0;
        $backlogBuckets = [
            ['label' => '<1m', 'value' => 0],
            ['label' => '1-5m', 'value' => 0],
            ['label' => '5-15m', 'value' => 0],
            ['label' => '>15m', 'value' => 0],
        ];

        $nowTs = now()->timestamp;

        if (Schema::hasTable('jobs') && Schema::hasColumn('jobs', 'available_at')) {
            if (Schema::hasColumn('jobs', 'reserved_at')) {
                $avgWaitValue = DB::table('jobs')
                    ->whereNotNull('reserved_at')
                    ->whereRaw('reserved_at >= available_at')
                    ->selectRaw('avg(reserved_at - available_at) as avg_wait_seconds')
                    ->value('avg_wait_seconds');

                $avgWaitSeconds = (int) round((float) ($avgWaitValue ?? 0));
            }

            $oldestAvailableAt = DB::table('jobs')->min('available_at');
            if (is_numeric($oldestAvailableAt)) {
                $oldestPendingSeconds = max(0, $nowTs - (int) $oldestAvailableAt);
            }

            $bucketRow = DB::table('jobs')
                ->selectRaw(
                    'sum(case when (? - available_at) < 60 then 1 else 0 end) as lt_1m, '
                    . 'sum(case when (? - available_at) between 60 and 300 then 1 else 0 end) as btw_1_5m, '
                    . 'sum(case when (? - available_at) between 301 and 900 then 1 else 0 end) as btw_5_15m, '
                    . 'sum(case when (? - available_at) > 900 then 1 else 0 end) as gt_15m',
                    [$nowTs, $nowTs, $nowTs, $nowTs]
                )
                ->first();

            if ($bucketRow) {
                $backlogBuckets = [
                    ['label' => '<1m', 'value' => (int) ($bucketRow->lt_1m ?? 0)],
                    ['label' => '1-5m', 'value' => (int) ($bucketRow->btw_1_5m ?? 0)],
                    ['label' => '5-15m', 'value' => (int) ($bucketRow->btw_5_15m ?? 0)],
                    ['label' => '>15m', 'value' => (int) ($bucketRow->gt_15m ?? 0)],
                ];
            }
        }

        $failedLast24h = 0;
        if (Schema::hasTable('failed_jobs') && Schema::hasColumn('failed_jobs', 'failed_at')) {
            $failedLast24h = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDay())
                ->count();
        }

        $hourlyFailures = $this->failedJobsHourlySeries(12);

        return [
            'avg_wait_seconds' => $avgWaitSeconds,
            'oldest_pending_seconds' => $oldestPendingSeconds,
            'failed_last_24h' => $failedLast24h,
            'backlog_buckets' => $backlogBuckets,
            'hourly_failures' => $hourlyFailures,
        ];
    }

    private function databaseConnectionsMetrics(): array
    {
        $connections = config('database.connections', []);
        $connectionNames = array_values(array_keys(is_array($connections) ? $connections : []));

        $connected = [];
        $disconnected = [];

        $defaultConnection = config('database.default', 'pgsql');
        $defaultDriver = config("database.connections.{$defaultConnection}.driver", 'pgsql');

        // Only the connections we actually probe count toward the configured total.
        // Boilerplate stubs for other drivers are skipped and must not be reported
        // as "disconnected" databases.
        $probedNames = [];

        foreach ($connectionNames as $name) {
            $connectionConfig = config("database.connections.{$name}", []);
            $driver = (string) ($connectionConfig['driver'] ?? 'unknown');
            $exportSupport = $this->databaseExportSupport($driver);

            // Skip boilerplate connections of different drivers that are not explicitly configured
            if ($name !== $defaultConnection && $driver !== $defaultDriver && $driver !== 'sqlite') {
                continue;
            }

            // Skip sqlite boilerplate stubs that have no database file configured
            // (e.g. the default database/database.sqlite that was never created).
            if ($driver === 'sqlite') {
                $sqlitePath = (string) ($connectionConfig['database'] ?? '');

                if ($sqlitePath === '' || !file_exists($sqlitePath)) {
                    continue;
                }
            }

            $probedNames[] = $name;

            try {
                // Set a short connection/login timeout dynamically (2 seconds)
                if ($driver === 'pgsql') {
                    config(["database.connections.{$name}.connect_timeout" => 2]);
                } elseif ($driver === 'mysql') {
                    config(["database.connections.{$name}.options." . \PDO::ATTR_TIMEOUT => 2]);
                } elseif ($driver === 'sqlsrv') {
                    config(["database.connections.{$name}.LoginTimeout" => 2]);
                }

                // Purge the connection instance to ensure the new timeout configuration is picked up
                DB::purge($name);

                DB::connection($name)->select('select 1');

                $connected[] = [
                    'name' => $name,
                    'driver' => $driver,
                    'database' => (string) ($connectionConfig['database'] ?? 'n/a'),
                    'host' => (string) ($connectionConfig['host'] ?? '127.0.0.1'),
                    'port' => (string) ($connectionConfig['port'] ?? ''),
                    'export_supported' => $exportSupport['supported'],
                    'export_formats' => $exportSupport['formats'],
                ];
            } catch (\Throwable $e) {
                $disconnected[] = [
                    'name' => $name,
                    'driver' => $driver,
                    'database' => (string) ($connectionConfig['database'] ?? 'n/a'),
                    'host' => (string) ($connectionConfig['host'] ?? '127.0.0.1'),
                    'port' => (string) ($connectionConfig['port'] ?? ''),
                    'export_supported' => $exportSupport['supported'],
                    'export_formats' => $exportSupport['formats'],
                    'reason' => Str::limit($e->getMessage(), 120),
                ];
            }
        }

        return [
            'configured_count' => count($probedNames),
            'connected_count' => count($connected),
            'disconnected_count' => count($disconnected),
            'connected' => $connected,
            'connected_names' => collect($connected)->pluck('name')->values()->all(),
            'disconnected' => $disconnected,
        ];
    }

    /**
     * @return array{supported: bool, formats: array<int, string>}
     */
    private function databaseExportSupport(string $driver): array
    {
        $driver = strtolower(trim($driver));

        if ($driver === 'pgsql') {
            return [
                'supported' => true,
                'formats' => ['sql', 'dump'],
            ];
        }

        return [
            'supported' => false,
            'formats' => [],
        ];
    }

    private function serviceIncidentsMetrics(
        array $queue,
        array $complaints,
        array $resolutionQueue,
        array $commandLatency,
        array $databaseConnections
    ): array {
        $failedLastHour = (int) Arr::get($queue, 'failed_last_hour', 0);
        $highPriorityOpen = (int) Arr::get($complaints, 'high_priority_open', 0);
        $stalled24h = (int) Arr::get($resolutionQueue, 'stalled_24h', 0);
        $disconnectedDatabases = (int) Arr::get($databaseConnections, 'disconnected_count', 0);
        $oldestPendingSeconds = (int) Arr::get($commandLatency, 'oldest_pending_seconds', 0);

        $severityScore = ($failedLastHour * 3) + ($highPriorityOpen * 2) + $stalled24h + ($disconnectedDatabases * 5);
        if ($oldestPendingSeconds > 900) {
            $severityScore += 4;
        }

        $status = 'healthy';
        if ($severityScore >= 25) {
            $status = 'critical';
        } elseif ($severityScore >= 10) {
            $status = 'degraded';
        }

        $recent = [];
        if ($failedLastHour > 0) {
            $recent[] = [
                'type' => 'queue',
                'label' => 'Queue failures in last hour',
                'value' => $failedLastHour,
            ];
        }
        if ($highPriorityOpen > 0) {
            $recent[] = [
                'type' => 'complaint',
                'label' => 'High-priority complaints open',
                'value' => $highPriorityOpen,
            ];
        }
        if ($disconnectedDatabases > 0) {
            $recent[] = [
                'type' => 'database',
                'label' => 'Database connections unavailable',
                'value' => $disconnectedDatabases,
            ];
        }
        if ($oldestPendingSeconds > 900) {
            $recent[] = [
                'type' => 'latency',
                'label' => 'Queue backlog above 15 minutes',
                'value' => round($oldestPendingSeconds / 60),
            ];
        }

        return [
            'status' => $status,
            'severity_score' => $severityScore,
            'open_signals' => count($recent),
            'breakdown' => [
                ['label' => 'Queue failures', 'value' => $failedLastHour],
                ['label' => 'High-priority complaints', 'value' => $highPriorityOpen],
                ['label' => 'Stalled complaints', 'value' => $stalled24h],
                ['label' => 'Disconnected databases', 'value' => $disconnectedDatabases],
            ],
            'recent' => $recent,
        ];
    }

    private function charts(array $resolutionQueue, array $commandLatency, array $databaseConnections, array $serviceIncidents): array
    {
        $stageBreakdown = Arr::get($resolutionQueue, 'stage_breakdown', []);
        $hourlyFailures = Arr::get($commandLatency, 'hourly_failures', []);
        $incidentBreakdown = Arr::get($serviceIncidents, 'breakdown', []);

        return [
            'complaint_resolution_stages' => [
                'labels' => collect($stageBreakdown)->pluck('stage')->values()->all(),
                'values' => collect($stageBreakdown)->pluck('value')->map(fn ($value): int => (int) $value)->values()->all(),
            ],
            'command_failures_trend' => [
                'labels' => collect($hourlyFailures)->pluck('label')->values()->all(),
                'values' => collect($hourlyFailures)->pluck('value')->map(fn ($value): int => (int) $value)->values()->all(),
            ],
            'database_connections' => [
                'labels' => ['Connected', 'Disconnected'],
                'values' => [
                    (int) Arr::get($databaseConnections, 'connected_count', 0),
                    (int) Arr::get($databaseConnections, 'disconnected_count', 0),
                ],
            ],
            'incident_signals' => [
                'labels' => collect($incidentBreakdown)->pluck('label')->values()->all(),
                'values' => collect($incidentBreakdown)->pluck('value')->map(fn ($value): int => (int) $value)->values()->all(),
            ],
        ];
    }

    private function healthMetrics(int $failedJobsCount, int $disconnectedDatabases): array
    {
        $databaseReachable = true;

        try {
            DB::select('select 1');
        } catch (\Throwable) {
            $databaseReachable = false;
        }

        $storageWritable = is_writable(storage_path());
        $queueDriver = (string) config('queue.default');
        $broadcastDriver = (string) config('broadcasting.default');
        $cacheStore = (string) config('cache.default');

        $status = 'healthy';

        if (!$databaseReachable || !$storageWritable || $disconnectedDatabases > 0) {
            $status = 'degraded';
        }

        if ($failedJobsCount >= 10) {
            $status = 'critical';
        }

        return [
            'status' => $status,
            'database_reachable' => $databaseReachable,
            'storage_writable' => $storageWritable,
            'disconnected_databases' => $disconnectedDatabases,
            'queue_driver' => $queueDriver,
            'broadcast_driver' => $broadcastDriver,
            'cache_store' => $cacheStore,
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function scheduledEvents(): array
    {
        try {
            app(\Illuminate\Contracts\Console\Kernel::class);

            return app(Schedule::class)->events();
        } catch (\Throwable) {
            return [];
        }
    }

    private function countSafe(string $table): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        return DB::table($table)->count();
    }

    private function applyOpenComplaintScope($query): void
    {
        if (Schema::hasColumn('complaints', 'is_closed')) {
            $query->where(function ($inner): void {
                $inner->whereNull('is_closed')->orWhere('is_closed', false);
            });
        }

        if (Schema::hasColumn('complaints', 'status')) {
            $query->whereRaw("LOWER(COALESCE(status, '')) not in (?, ?, ?)", ['closed', 'resolved', 'completed']);
        }
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private function failedJobsHourlySeries(int $hours): array
    {
        $hours = max(1, $hours);
        $start = now()->subHours($hours - 1)->startOfHour();

        $series = [];
        $cursor = $start->copy();
        while ($cursor->lte(now())) {
            $series[$cursor->format('Y-m-d H:00:00')] = 0;
            $cursor->addHour();
        }

        if (Schema::hasTable('failed_jobs') && Schema::hasColumn('failed_jobs', 'failed_at')) {
            $rows = DB::table('failed_jobs')
                ->selectRaw("date_trunc('hour', failed_at) as bucket, count(*) as total")
                ->where('failed_at', '>=', $start)
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            foreach ($rows as $row) {
                $bucketTime = Carbon::parse((string) $row->bucket)->format('Y-m-d H:00:00');
                $series[$bucketTime] = (int) $row->total;
            }
        }

        return collect($series)
            ->map(fn (int $value, string $bucket): array => [
                'label' => Carbon::parse($bucket)->format('H:i'),
                'value' => $value,
            ])
            ->values()
            ->all();
    }

    private function activeModulesCount(): int
    {
        // Prefer the configured module visibility map so the count reflects
        // what administrators have actually enabled in System Settings.
        if (function_exists('getSystemModuleVisibilityMap')) {
            $visibility = getSystemModuleVisibilityMap();

            if (!empty($visibility)) {
                return collect($visibility)->filter(fn ($value): bool => (bool) $value === true)->count();
            }
        }

        // Fallback: count enabled package modules from modules_statuses.json.
        $file = base_path('modules_statuses.json');

        if (!is_file($file)) {
            return 0;
        }

        $json = file_get_contents($file);
        if ($json === false) {
            return 0;
        }

        $parsed = json_decode($json, true);
        if (!is_array($parsed)) {
            return 0;
        }

        return collect($parsed)->filter(fn ($value): bool => (bool) $value === true)->count();
    }

    private function resolveCurrentLogFilePath(): ?string
    {
        $files = glob(storage_path('logs/laravel*.log'));

        if (!is_array($files) || $files === []) {
            $files = glob(storage_path('logs/*.log'));
        }

        if (!is_array($files) || $files === []) {
            return null;
        }

        usort($files, function (string $left, string $right): int {
            return (int) (filemtime($right) ?: 0) <=> (int) (filemtime($left) ?: 0);
        });

        return $files[0] ?? null;
    }

    /**
     * @return array{contents: string, truncated: bool}|null
     */
    private function readLogTail(string $path, int $maxBytes): ?array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return null;
        }

        try {
            $fileSize = filesize($path);
            if ($fileSize === false) {
                return null;
            }

            $fileSize = (int) $fileSize;
            $maxBytes = max(1024, $maxBytes);
            $truncated = $fileSize > $maxBytes;

            if ($truncated) {
                fseek($handle, -$maxBytes, SEEK_END);
            }

            $contents = stream_get_contents($handle);
            if ($contents === false) {
                return null;
            }

            return [
                'contents' => $contents,
                'truncated' => $truncated,
            ];
        } finally {
            fclose($handle);
        }
    }

    private function formatLogTimestamp(string $path): ?string
    {
        $timestamp = filemtime($path);

        if ($timestamp === false) {
            return null;
        }

        return Carbon::createFromTimestamp($timestamp)->toDateTimeString();
    }
}
