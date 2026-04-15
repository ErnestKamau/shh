<?php

namespace App\Observers\AI;

use App\Services\AI\RagIndexingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * RAG Indexing Observer
 * 
 * Automatically syncs model changes to the RAG indexing service.
 * Attach to any model that should be indexed: Model::observe(RagIndexingObserver::class)
 */
class RagIndexingObserver
{
    /**
     * The RAG indexing service instance
     */
    protected RagIndexingService $service;

    /**
     * Create the observer
     */
    public function __construct(RagIndexingService $service = null)
    {
        $this->service = $service ?? app(RagIndexingService::class);
    }

    /**
     * Handle the created event
     */
    public function created(Model $model): void
    {
        if (!config('ai.rag.enable_realtime_sync', false)) {
            return;
        }

        if (!method_exists($model, 'toRagChunk')) {
            return;
        }

        try {
            $chunk = $model->toRagChunk();
            if ($chunk) {
                $this->service->index($chunk);
            }
        } catch (\Exception $e) {
            Log::error('RagIndexingObserver: Error indexing created model', [
                'model' => $model::class,
                'id' => $model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the updated event
     */
    public function updated(Model $model): void
    {
        if (!config('ai.rag.enable_realtime_sync', false)) {
            return;
        }

        if (!method_exists($model, 'toRagChunk')) {
            return;
        }

        try {
            // Get the RAG chunk representation
            $chunk = $model->toRagChunk();
            if ($chunk) {
                // Delete old chunks and index the new version
                $this->service->delete(
                    $chunk['entity_type'],
                    $chunk['entity_id']
                );
                $this->service->index($chunk);
            }
        } catch (\Exception $e) {
            Log::error('RagIndexingObserver: Error updating indexed model', [
                'model' => $model::class,
                'id' => $model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the deleted event
     */
    public function deleted(Model $model): void
    {
        if (!config('ai.rag.enable_realtime_sync', false)) {
            return;
        }

        if (!method_exists($model, 'toRagChunk')) {
            return;
        }

        try {
            // Get the RAG chunk representation to extract entity info
            $chunk = $model->toRagChunk();
            if ($chunk) {
                $this->service->delete(
                    $chunk['entity_type'],
                    $chunk['entity_id']
                );
            }
        } catch (\Exception $e) {
            Log::error('RagIndexingObserver: Error deleting indexed model', [
                'model' => $model::class,
                'id' => $model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the restored event (soft deletes)
     */
    public function restored(Model $model): void
    {
        // Treat restoration as a new indexing
        $this->created($model);
    }

    /**
     * Handle the force deleted event (soft deletes)
     */
    public function forceDeleted(Model $model): void
    {
        // Treat force deletion as a deletion
        $this->deleted($model);
    }
}
