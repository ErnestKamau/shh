<?php

namespace App\Console\Commands\AI;

use App\Services\AI\RagIndexingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * RAG Management Command
 * 
 * php artisan rag:manage {action} {--entity-type=} {--collection=}
 */
class RagManageCommand extends Command
{
    protected $signature = 'rag:manage
                            {action : Action to perform (stats, reindex, purge, validate)}
                            {--entity-type= : Entity type to process}
                            {--collection= : Collection to process}
                            {--force : Skip confirmation}';

    protected $description = 'Manage RAG system (statistics, reindex, purge, validate)';

    protected RagIndexingService $service;

    public function __construct(RagIndexingService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'stats' => $this->showStatistics(),
            'reindex' => $this->reindexContent(),
            'purge' => $this->purgeContent(),
            'validate' => $this->validateIndex(),
            default => $this->error("Unknown action: $action"),
        };
    }

    /**
     * Show RAG statistics
     */
    protected function showStatistics(): int
    {
        $this->info('RAG System Statistics');
        $this->line(str_repeat('-', 50));

        try {
            $total = DB::connection('pgsql_ai')
                ->table('ai.ai_knowledge_chunks')
                ->count();

            $collections = DB::connection('pgsql_ai')
                ->table('ai.ai_knowledge_chunks')
                ->selectRaw('collection_name, COUNT(*) as count')
                ->groupBy('collection_name')
                ->get();

            $entityTypes = DB::connection('pgsql_ai')
                ->table('ai.ai_knowledge_chunks')
                ->selectRaw('entity_type, COUNT(*) as count')
                ->groupBy('entity_type')
                ->get();

            $this->info("Total Chunks: $total");
            $this->line('');

            $this->info('Chunks by Collection:');
            foreach ($collections as $col) {
                $this->line("  {$col->collection_name}: {$col->count}");
            }

            $this->line('');
            $this->info('Chunks by Entity Type:');
            foreach ($entityTypes as $type) {
                $this->line("  {$type->entity_type}: {$type->count}");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Error retrieving statistics: {$e->getMessage()}");
            Log::error('RAG statistics command failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Reindex content
     */
    protected function reindexContent(): int
    {
        $entityType = $this->option('entity-type');

        if (!$entityType) {
            $this->error('--entity-type is required for reindex');
            return 1;
        }

        if (!$this->option('force')) {
            if (!$this->confirm("Reindex all '$entityType' chunks? This may take a while.")) {
                return 0;
            }
        }

        $this->info("Starting reindex of '$entityType'...");
        $this->output->progressStart();

        try {
            $stats = $this->service->bulkReindex($entityType);

            $this->output->progressFinish();

            $this->info('');
            $this->info('Reindex completed!');
            $this->line("Processed: {$stats['processed']}");
            $this->line("Failed: {$stats['failed']}");
            $this->line("Duration: {$stats['duration_ms']}ms");

            return 0;
        } catch (\Exception $e) {
            $this->output->progressFinish();
            $this->error("Reindex failed: {$e->getMessage()}");
            Log::error('RAG reindex command failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Purge content
     */
    protected function purgeContent(): int
    {
        $entityType = $this->option('entity-type');
        $collection = $this->option('collection');

        if (!$entityType && !$collection) {
            $this->error('Specify --entity-type or --collection');
            return 1;
        }

        $query = DB::connection('pgsql_ai')
            ->table('ai.ai_knowledge_chunks');

        if ($entityType) {
            $query->where('entity_type', $entityType);
        }

        if ($collection) {
            $query->where('collection_name', $collection);
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No chunks found to purge.');
            return 0;
        }

        if (!$this->option('force')) {
            if (!$this->confirm("Delete $count chunks? This cannot be undone.")) {
                return 0;
            }
        }

        try {
            $deleted = $query->delete();

            $this->info("Successfully deleted $deleted chunks.");
            return 0;
        } catch (\Exception $e) {
            $this->error("Purge failed: {$e->getMessage()}");
            Log::error('RAG purge command failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Validate index integrity
     */
    protected function validateIndex(): int
    {
        $this->info('Validating RAG Index...');

        try {
            $issues = 0;

            // Check for missing embeddings
            $missingEmbeddings = DB::connection('pgsql_ai')
                ->table('ai.ai_knowledge_chunks')
                ->whereNull('embedding')
                ->count();

            if ($missingEmbeddings > 0) {
                $this->warn("Found $missingEmbeddings chunks without embeddings");
                $issues++;
            }

            // Check for duplicate entity IDs in same collection
            $duplicates = DB::connection('pgsql_ai')
                ->selectRaw('collection_name, entity_type, entity_id, COUNT(*) as count')
                ->from('ai.ai_knowledge_chunks')
                ->groupBy('collection_name', 'entity_type', 'entity_id')
                ->having('count', '>', 1)
                ->get();

            if ($duplicates->count() > 0) {
                $this->warn("Found {$duplicates->count()} duplicate entity IDs");
                $issues++;
            }

            // Check for NULL required fields
            $nullableFields = ['content', 'entity_type', 'entity_id', 'collection_name'];
            foreach ($nullableFields as $field) {
                $nullCount = DB::connection('pgsql_ai')
                    ->table('ai.ai_knowledge_chunks')
                    ->whereNull($field)
                    ->count();

                if ($nullCount > 0) {
                    $this->warn("Found $nullCount chunks with NULL $field");
                    $issues++;
                }
            }

            if ($issues === 0) {
                $this->info('✓ Index validation passed!');
                return 0;
            } else {
                $this->line('');
                $this->warn("$issues validation issues found.");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Validation failed: {$e->getMessage()}");
            Log::error('RAG validation command failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
