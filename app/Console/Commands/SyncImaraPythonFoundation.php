<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use App\Models\AiRepository\OperationalSyncRun;
use App\Services\AI\AiEndpointResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncImaraPythonFoundation extends Command
{
    protected $signature = 'imara:sync-python-foundation
                            {tables?* : Optional configured source tables to sync}
                            {--chunk= : Override the default ETL chunk size}
                            {--all : Sync all configured tables}
                            {--test-connections : Test DB connectivity only}';

    protected $description = 'Run the Python ETL engine to sync operational foundation tables from MySQL into PostgreSQL';

    public function handle(): int
    {
        // ========== PHASE 0: ORPHAN CLEANUP ==========
        // Mark any stale RUNNING runs (> 2 hours) as FAILED
        try {
            $staleCount = DB::table('reporting.sync_runs')
                ->where('status', 'running')
                ->where('started_at', '<', now()->subHours(2))
                ->update([
                    'status' => 'failed',
                    'error_message' => 'Orphaned run detected on startup (marked by scheduler)',
                    'stage' => 'system-cleanup',
                    'finished_at' => now()
                ]);
            
            if ($staleCount > 0) {
                Log::channel('etl')->warning("Cleaned up {$staleCount} orphaned ETL runs");
            }
        } catch (\Throwable $e) {
            Log::channel('etl')->warning("Orphan cleanup failed: " . $e->getMessage());
        }
        
        // ========== PHASE 0: CONCURRENCY LOCK WITH OWNERSHIP ==========
        $lockName = 'etl:sync:python-foundation';
        $lockTimeout = (int) config('services.etl.lock_timeout_seconds', 300); // 5 minutes
        $ownerId = Str::uuid();
        
        $lock = Cache::lock($lockName, $lockTimeout);
        
        if (!$lock->get()) {
            $currentOwner = Cache::get('etl:lock:owner', 'unknown');
            $this->error("❌ ETL already running (owned by: {$currentOwner})");
            
            // Log failed attempt
            OperationalSyncRun::create([
                'sync_scope' => 'etl-full',
                'source_table' => 'imara_lims',
                'target_table' => 'reporting.*',
                'status' => 'failed',
                'error_message' => "ETL already running (owner: {$currentOwner})",
                'stage' => 'lock-acquisition',
                'lock_owner' => null
            ]);
            
            return self::FAILURE;
        }
        
        // Store lock owner for debugging
        Cache::put('etl:lock:owner', $ownerId, now()->addSeconds($lockTimeout));
        Log::channel('etl')->info("Lock acquired", ['owner_id' => $ownerId]);
        
        // ========== PHASE 0: CREATE PIPELINE RUN RECORD ==========
        $syncRun = OperationalSyncRun::create([
            'sync_scope' => 'etl-full',
            'source_table' => 'imara_lims',
            'target_table' => 'reporting.*',
            'status' => 'running',
            'stage' => 'init',
            'rows_synced' => 0,
            'rows_failed' => 0,
            'rows_quarantined' => 0,
            'lock_owner' => $ownerId,
            'started_at' => now()
        ]);
        
        $maxTimeout = (int) config('services.etl.max_timeout_seconds', 3600); // 60 minutes
        $idleTimeout = (int) config('services.etl.idle_timeout_seconds', 120); // 2 minutes
        
        $startTime = now();
        try {
            // ========== PHASE 0: CALL MICROSERVICE API ==========
            $aiBaseUrl = AiEndpointResolver::resolve();
            $syncUrl = rtrim($aiBaseUrl, '/') . '/etl/sync';
            
            $tables = $this->argument('tables') ?: [];
            $payload = [
                'tables' => !empty($tables) ? $tables : null,
            ];

            $this->line("Pinging AI Microservice at: {$syncUrl}");
            
            $response = Http::withHeaders([
                'X-Request-ID' => (string) $syncRun->id,
            ])->timeout(10)->post($syncUrl, $payload);

            if (!$response->successful()) {
                $errorDetail = $response->json('detail');
                $errorMsg = is_array($errorDetail) ? json_encode($errorDetail) : ($errorDetail ?? $response->body());
                throw new \RuntimeException("AI Service returned error: " . $errorMsg);
            }

            $this->info("✅ ETL sync request accepted by microservice.");
            $this->line("Response: " . json_encode($response->json()));

            // NOTE: Since the sync is now asynchronous (BackgroundTasks in FastAPI),
            // this command will exit early. The status will be updated by the 
            // AI service writing directly to the sharing database or via 
            // a callback. In this architecture, we rely on the DB shared state.
            
            $syncRun->update([
                'status' => 'pending', // Accepted by service but backgrounded
                'stage' => 'microservice-delegated',
            ]);

            return self::SUCCESS;
            
        } catch (\Throwable $exception) {
            // ========== FAILURE CASE ==========
            $duration = (int) abs(microtime(true) - $startTime->timestamp);
            
            $syncRun->update([
                'status' => 'failed',
                'stage' => 'error-handling',
                'finished_at' => now(),
                'duration_seconds' => $duration,
                'error_message' => $exception->getMessage()
            ]);

            Log::channel('etl')->error('ETL failed', [
                'run_id' => $syncRun->id,
                'duration_seconds' => $duration,
                'error' => $exception->getMessage()
            ]);

            $this->error("❌ ETL failed: " . $exception->getMessage());
            return self::FAILURE;
            
        } finally {
            // ========== ALWAYS RELEASE LOCK & CLEANUP ==========
            try {
                $lock->release();
                Cache::forget('etl:lock:owner');
                Log::channel('etl')->info("Lock released", ['owner_id' => $ownerId]);
            } catch (\Throwable $e) {
                Log::channel('etl')->warning("Failed to release lock: " . $e->getMessage());
            }
        }
    }
}
