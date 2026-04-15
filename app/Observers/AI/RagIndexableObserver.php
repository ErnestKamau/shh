<?php

namespace App\Observers\AI;

use App\Services\AI\RagIndexingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Generic RAG indexing observer for model events
 *
 * Automatically indexes/deletes knowledge chunks when entities are created/updated/deleted
 * Respects the 'enable_realtime_sync' feature flag.
 */
class RagIndexableObserver
{
    protected RagIndexingService $indexingService;

    public function __construct()
    {
        $this->indexingService = new RagIndexingService();
    }

    /**
     * Handle model creation event
     *
     * Called when a new model is created. Indexes the entity to RAG knowledge base.
     */
    public function created(Model $model): void
    {
        if (!config('ai.rag.enable_realtime_sync', false)) {
            return;
        }

        try {
            // Extract RAG chunk data from model using toRagChunk method (if defined)
            if (method_exists($model, 'toRagChunk')) {
                $chunk = $model->toRagChunk();
                $this->indexingService->index($chunk, async: true);

                Log::debug('RagIndexableObserver: Entity indexed on create', [
                    'model' => class_basename($model),
                    'id' => $model->id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('RagIndexableObserver: Failed to index on create', [
                'model' => class_basename($model),
                'id' => $model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle model update event
     *
     * Called when a model is updated. Re-indexes the entity.
     */
    public function updated(Model $model): void
    {
        if (!config('ai.rag.enable_realtime_sync', false)) {
            return;
        }

        try {
            if (method_exists($model, 'toRagChunk')) {
                $chunk = $model->toRagChunk();
                $this->indexingService->index($chunk, async: true);

                Log::debug('RagIndexableObserver: Entity indexed on update', [
                    'model' => class_basename($model),
                    'id' => $model->id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('RagIndexableObserver: Failed to index on update', [
                'model' => class_basename($model),
                'id' => $model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle model deletion event
     *
     * Called when a model is deleted. Removes associated knowledge chunks from RAG.
     */
    public function deleted(Model $model): void
    {
        if (!config('ai.rag.enable_realtime_sync', false)) {
            return;
        }

        try {
            // Determine entity type from model class
            $entityType = class_basename($model);

            // Delete all knowledge chunks associated with this entity
            $this->indexingService->delete($entityType, $model->id, async: true);

            Log::debug('RagIndexableObserver: Entity deleted from index', [
                'model' => $entityType,
                'id' => $model->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('RagIndexableObserver: Failed to delete from index', [
                'model' => class_basename($model),
                'id' => $model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle model restoration event
     *
     * Called when a soft-deleted model is restored. Re-indexes the entity.
     */
    public function restored(Model $model): void
    {
        // Re-index as if it were created
        $this->created($model);
    }
}
