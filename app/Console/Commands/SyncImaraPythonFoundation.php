<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use App\Models\AiRepository\OperationalSyncRun;
use Illuminate\Support\Facades\DB;
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
            // ========== PHASE 0: BUILD PYTHON COMMAND ==========
            $pythonBinary = env('AI_PYTHON_BIN', 'python3');
            $scriptPath = base_path('python/py_etl/cli/sync_command.py');

            if (!file_exists($scriptPath)) {
                throw new \RuntimeException("Python ETL script not found at: {$scriptPath}");
            }

            $command = [$pythonBinary, $scriptPath, '--run-id', $syncRun->id];

            $tables = $this->argument('tables') ?: [];
            if (!empty($tables)) {
                foreach ($tables as $table) {
                    $command[] = $table;
                }
            }

            if ($this->option('chunk')) {
                $command[] = '--chunk';
                $command[] = (string) ((int) $this->option('chunk'));
            }

            if ($this->option('all')) {
                $command[] = '--all';
            }

            if ($this->option('test-connections')) {
                $command[] = '--test-connections';
            }

            $this->line('Executing Python ETL engine...');
            $this->line(implode(' ', array_map('strval', $command)));
            
            Log::channel('etl')->info('ETL starting', [
                'run_id' => $syncRun->id,
                'owner_id' => $ownerId,
                'max_timeout_seconds' => $maxTimeout,
                'idle_timeout_seconds' => $idleTimeout
            ]);

            // ========== PHASE 0: PROCESS TIMEOUT + HARD KILL ==========
            $process = new Process($command, base_path());
            $process->setTimeout($maxTimeout);      // 60 minutes max
            $process->setIdleTimeout($idleTimeout);  // 2 minutes idle max

            $startTime = microtime(true);
            $process->run(function ($type, $buffer) use ($syncRun) {
                $this->output->write($buffer);
                // Log output to file
                Log::channel('etl')->debug('ETL output', [
                    'run_id' => $syncRun->id,
                    'output' => trim($buffer)
                ]);
            });

            // ========== PHASE 0: CALCULATE DURATION ==========
            $duration = (int) abs(microtime(true) - $startTime);
            if ($duration > ($maxTimeout * 0.9)) {
                $this->warn("⚠️  ETL took {$duration}s (near timeout threshold of {$maxTimeout}s)");
            }

            // ========== PHASE 0: CHECK EXIT CODE ==========
            if ($process->getExitCode() !== 0) {
                throw new \RuntimeException(
                    "ETL process exited with code: {$process->getExitCode()}"
                );
            }

            // ========== SUCCESS CASE ==========
            $syncRun->update([
                'status' => 'completed',
                'stage' => 'finalize',
                'finished_at' => now(),
                'duration_seconds' => $duration
            ]);

            Log::channel('etl')->info('ETL completed successfully', [
                'run_id' => $syncRun->id,
                'duration_seconds' => $duration,
                'rows_synced' => $syncRun->rows_synced ?? 0,
                'rows_quarantined' => $syncRun->rows_quarantined ?? 0
            ]);

            $this->info("✅ Python ETL execution completed successfully ({$duration}s)");
            return self::SUCCESS;
            
        } catch (ProcessTimedOutException $e) {
            // ========== PHASE 0: HARD KILL ON TIMEOUT ==========
            $this->error("❌ Process timeout: ETL did not complete within timeout window");
            
            try {
                $process->stop(3);  // Force kill (SIGKILL)
            } catch (\Throwable $killError) {
                Log::channel('etl')->warning("Failed to force-kill process: " . $killError->getMessage());
            }
            
            $duration = now()->diffInSeconds($startTime);
            $syncRun->update([
                'status' => 'failed',
                'stage' => 'timeout-enforcement',
                'finished_at' => now(),
                'duration_seconds' => $duration,
                'error_message' => "Process timeout after {$maxTimeout} seconds (stopped at {$duration}s)"
            ]);
            
            Log::channel('etl')->error('ETL timeout', [
                'run_id' => $syncRun->id,
                'max_timeout' => $maxTimeout,
                'actual_duration' => $duration
            ]);
            
            return self::FAILURE;
            
        } catch (\Throwable $exception) {
            // ========== FAILURE CASE ==========
            $duration = (int) abs(microtime(true) - $startTime);
            
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
