<?php

namespace App\Jobs\AI;

use App\Services\AI\CollectionIndexerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RagIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $collectionName;
    protected string $content;
    protected ?string $entityType;
    protected ?int $entityId;
    protected array $metadata;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $collectionName,
        string $content,
        ?string $entityType = null,
        ?int $entityId = null,
        array $metadata = []
    ) {
        $this->collectionName = $collectionName;
        $this->content = $content;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->metadata = $metadata;
    }

    /**
     * Execute the job.
     */
    public function handle(CollectionIndexerService $indexer): void
    {
        try {
            // Before re-indexing, clear old chunks for this entity
            if ($this->entityType && $this->entityId) {
                $indexer->clearCollection($this->entityType, $this->entityId);
            }

            // Index new content
            $indexer->indexContent(
                $this->collectionName,
                $this->content,
                $this->entityType,
                $this->entityId,
                $this->metadata
            );
        } catch (\Throwable $e) {
            Log::error('RagIndexJob: Execution failed', [
                'error' => $e->getMessage(),
                'entity' => "{$this->entityType}:{$this->entityId}"
            ]);
            
            throw $e;
        }
    }
}
