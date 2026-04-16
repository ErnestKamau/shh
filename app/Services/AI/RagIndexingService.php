<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class RagIndexingService
{
    protected string $connection = 'pgsql_ai';
    protected string $table = 'ai.ai_knowledge_chunks';
    protected string $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = AiEndpointResolver::resolve();
    }

    /**
     * Index a document/entity chunk to the RAG knowledge base
     *
     * This creates or updates a knowledge chunk with embedding and metadata.
     * Can be called synchronously (small batches) or queued for bulk operations.
     *
     * @param array $chunk Chunk data including:
     *   - collection_name: string (e.g., 'sops', 'capa', 'batches')
     *   - entity_type: string (e.g., 'Batch', 'SOP', 'CAPA')
     *   - entity_id: int|string unique identifier for the entity
     *   - content: string the full text content to index
     *   - document_title: string optional title for the chunk
     *   - required_permission: string optional permission (e.g., 'Audit.View')
     *   - metadata: array optional JSON metadata
     *   - embedding_version: string optional embedding model version (default: 'v1')
     * @param bool $async Queue instead of indexing synchronously
     * @return bool Success status
     */
    public function index(array $chunk, bool $async = false): bool
    {
        if (!config('ai.rag.enable_realtime_sync', false)) {
            Log::debug('RagIndexingService: Real-time sync disabled, skipping index', [
                'entity_type' => $chunk['entity_type'],
                'entity_id' => $chunk['entity_id'],
            ]);
            return true;
        }

        if ($async && config('queue.default') && config('queue.default') !== 'sync') {
            // Queue the indexing job for async processing
            Queue::push(\App\Jobs\AI\RagIndexJob::class, [
                'action' => 'index',
                'chunk' => $chunk,
            ]);
            return true;
        }

        // Synchronous indexing
        return $this->performIndex($chunk);
    }

    /**
     * Delete all knowledge chunks associated with an entity
     *
     * @param string $entityType Entity type (e.g., 'Batch', 'SOP')
     * @param int|string $entityId Entity ID
     * @param bool $async Queue instead of deleting synchronously
     * @return int Number of chunks deleted
     */
    public function delete(string $entityType, int|string $entityId, bool $async = false): int
    {
        if (!config('ai.rag.enable_realtime_sync', false)) {
            Log::debug('RagIndexingService: Real-time sync disabled, skipping delete', [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]);
            return 0;
        }

        if ($async && config('queue.default') && config('queue.default') !== 'sync') {
            Queue::push(\App\Jobs\AI\RagIndexJob::class, [
                'action' => 'delete',
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]);
            return 0; // Can't return actual count when queued
        }

        return $this->performDelete($entityType, $entityId);
    }

    /**
     * Perform actual indexing (synchronized or from queue)
     */
    protected function performIndex(array $chunk): bool
    {
        try {
            // Validate required fields
            if (!isset($chunk['collection_name']) || !isset($chunk['entity_type']) || !isset($chunk['entity_id']) || !isset($chunk['content'])) {
                Log::warning('RagIndexingService: Missing required chunk fields', ['chunk_keys' => array_keys($chunk)]);
                return false;
            }

            // Generate embedding from FastAPI service
            $embedding = $this->generateEmbedding($chunk['content']);
            if (!$embedding) {
                Log::warning('RagIndexingService: Failed to generate embedding', [
                    'entity_type' => $chunk['entity_type'],
                    'entity_id' => $chunk['entity_id'],
                ]);
                return false;
            }

            // Prepare chunk for database
            $chunkId = $this->generateChunkId(
                $chunk['entity_type'],
                $chunk['entity_id'],
                $chunk['collection_name']
            );

            $data = [
                'chunk_id' => $chunkId,
                'collection_name' => $chunk['collection_name'],
                'entity_type' => $chunk['entity_type'],
                'entity_id' => $chunk['entity_id'],
                'content' => $chunk['content'],
                'document_title' => $chunk['document_title'] ?? null,
                'embedding' => $embedding, // Vector type in PostgreSQL
                'embedding_version' => $chunk['embedding_version'] ?? 'v1',
                'required_permission' => $chunk['required_permission'] ?? null,
                'metadata' => !empty($chunk['metadata']) ? json_encode($chunk['metadata']) : null,
                'is_active' => $chunk['is_active'] ?? true,
                'document_status' => $chunk['document_status'] ?? 'published',
                'created_at' => $chunk['created_at'] ?? now(),
                'updated_at' => now(),
            ];

            // Upsert (update if exists, insert otherwise)
            DB::connection($this->connection)->table($this->table)->upsert(
                [$data],
                ['chunk_id'],
                array_diff(array_keys($data), ['chunk_id'])
            );

            Log::debug('RagIndexingService: Chunk indexed successfully', [
                'chunk_id' => $chunkId,
                'entity_type' => $chunk['entity_type'],
                'embedding_model' => $chunk['embedding_version'] ?? 'v1',
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('RagIndexingService: Indexing failed', [
                'entity_type' => $chunk['entity_type'] ?? 'unknown',
                'entity_id' => $chunk['entity_id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Perform actual deletion (synchronized or from queue)
     */
    protected function performDelete(string $entityType, int|string $entityId): int
    {
        try {
            $deleted = DB::connection($this->connection)
                ->table($this->table)
                ->where('entity_type', $entityType)
                ->where('entity_id', (string) $entityId)
                ->delete();

            Log::debug('RagIndexingService: Chunks deleted', [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'count' => $deleted,
            ]);

            return $deleted;
        } catch (\Throwable $e) {
            Log::error('RagIndexingService: Deletion failed', [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Generate embedding vector from FastAPI service
     *
     * @param string $text Text to embed
     * @return string|null Vector as JSON-encoded array, or null on failure
     */
    protected function generateEmbedding(string $text): ?string
    {
        try {
            $response = Http::timeout(10)
                ->post($this->apiBaseUrl . '/ai/embeddings/generate', [
                    'text' => $text,
                    'model' => 'openai-text-embedding-3-small',
                ]);

            if (!$response->successful()) {
                Log::warning('RagIndexingService: Embedding service error', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();
            $embedding = $data['embedding'] ?? null;

            if (!$embedding || !is_array($embedding)) {
                Log::warning('RagIndexingService: Invalid embedding response format');
                return null;
            }

            // Return as vector literal for PostgreSQL pgvector type
            return '[' . implode(',', $embedding) . ']';
        } catch (\Throwable $e) {
            Log::warning('RagIndexingService: Embedding generation failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate consistent chunk ID for an entity
     *
     * Deterministic ID allows safe upserts without checking existence first.
     *
     * @param string $entityType Entity type
     * @param int|string $entityId Entity ID
     * @param string $collectionName Collection name
     * @return string Chunk ID (hash-based)
     */
    protected function generateChunkId(string $entityType, int|string $entityId, string $collectionName): string
    {
        return hash('sha256', "$entityType:$entityId:$collectionName");
    }

    /**
     * Bulk reindex all chunks for an entity or collection
     *
     * Useful for batch operations or migration tasks.
     * Re-generates embeddings for all chunks.
     *
     * @param string|null $entityType Filter by entity type, or null for all
     * @param string|null $collectionName Filter by collection, or null for all
     * @return array Statistics: ['processed' => int, 'failed' => int, 'duration_ms' => float]
     */
    public function bulkReindex(?string $entityType = null, ?string $collectionName = null): array
    {
        $startTime = microtime(true);
        $processed = 0;
        $failed = 0;

        try {
            $query = DB::connection($this->connection)->table($this->table);

            if ($entityType) {
                $query->where('entity_type', $entityType);
            }

            if ($collectionName) {
                $query->where('collection_name', $collectionName);
            }

            // Process in batches to avoid memory issues
            $query->select(['id', 'content', 'chunk_id', 'entity_type', 'entity_id', 'collection_name', 'document_title', 'required_permission', 'metadata', 'is_active', 'document_status'])
                ->chunk(50, function ($chunks) use (&$processed, &$failed) {
                    foreach ($chunks as $chunk) {
                        $embedResult = $this->generateEmbedding($chunk->content);
                        if ($embedResult) {
                            DB::connection($this->connection)
                                ->table($this->table)
                                ->where('id', $chunk->id)
                                ->update(['embedding' => $embedResult, 'updated_at' => now()]);
                            $processed++;
                        } else {
                            $failed++;
                        }
                    }
                });

            $durationMs = (microtime(true) - $startTime) * 1000;

            Log::info('RagIndexingService: Bulk reindex completed', [
                'entity_type' => $entityType,
                'collection_name' => $collectionName,
                'processed' => $processed,
                'failed' => $failed,
                'duration_ms' => round($durationMs, 2),
            ]);

            return [
                'processed' => $processed,
                'failed' => $failed,
                'duration_ms' => round($durationMs, 2),
            ];
        } catch (\Throwable $e) {
            Log::error('RagIndexingService: Bulk reindex failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'processed' => $processed,
                'failed' => $failed,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ];
        }
    }

    /**
     * Check chunk indexing status
     *
     * @param string $entityType Entity type
     * @param int|string $entityId Entity ID
     * @return array Status info: ['indexed' => bool, 'chunk_count' => int, 'last_updated' => Carbon|null]
     */
    public function getIndexStatus(string $entityType, int|string $entityId): array
    {
        try {
            $chunks = DB::connection($this->connection)
                ->table($this->table)
                ->where('entity_type', $entityType)
                ->where('entity_id', (string) $entityId)
                ->select(['id', 'updated_at'])
                ->get();

            return [
                'indexed' => $chunks->isNotEmpty(),
                'chunk_count' => $chunks->count(),
                'last_updated' => $chunks->isNotEmpty() ? $chunks->last()->updated_at : null,
            ];
        } catch (\Throwable $e) {
            Log::warning('RagIndexingService: Failed to get index status', [
                'error' => $e->getMessage(),
            ]);

            return [
                'indexed' => false,
                'chunk_count' => 0,
                'last_updated' => null,
            ];
        }
    }
}
