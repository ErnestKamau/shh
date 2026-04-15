<?php

namespace App\Http\Controllers\API\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\RagIndexingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * RAG Chunk API Controller
 * 
 * Provides REST API endpoints for managing RAG chunks
 */
class RagChunkController extends Controller
{
    /**
     * The RAG indexing service
     */
    protected RagIndexingService $service;

    /**
     * Create controller instance
     */
    public function __construct(RagIndexingService $service)
    {
        $this->service = $service;
        $this->middleware('auth:api');
    }

    /**
     * List RAG chunks
     * 
     * GET /api/rag/chunks
     * Query parameters:
     *   - collection: collection name filter
     *   - entity_type: entity type filter
     *   - per_page: items per page (default: 15)
     *   - page: page number (default: 1)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DB::connection('pgsql_ai')
                ->table('ai.ai_knowledge_chunks')
                ->select('*');

            // Apply collection filter
            if ($request->has('collection')) {
                $query->where('collection_name', $request->input('collection'));
            }

            // Apply entity type filter
            if ($request->has('entity_type')) {
                $query->where('entity_type', $request->input('entity_type'));
            }

            // Apply status filter
            if ($request->has('status')) {
                $query->where('document_status', $request->input('status'));
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $chunks = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $chunks->items(),
                'meta' => [
                    'total' => $chunks->total(),
                    'per_page' => $chunks->perPage(),
                    'current_page' => $chunks->currentPage(),
                    'last_page' => $chunks->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error listing RAG chunks', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving chunks',
            ], 500);
        }
    }

    /**
     * Create or index a new chunk
     * 
     * POST /api/rag/chunks
     * Body:
     *   - collection_name: string (required)
     *   - entity_type: string (required)
     *   - entity_id: string|int (required)
     *   - content: string (required)
     *   - document_title: string (optional)
     *   - required_permission: string (optional)
     *   - metadata: object (optional)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'collection_name' => 'required|string|max:255',
                'entity_type' => 'required|string|max:255',
                'entity_id' => 'required|string|max:255',
                'content' => 'required|string',
                'document_title' => 'nullable|string|max:255',
                'required_permission' => 'nullable|string|max:255',
                'metadata' => 'nullable|array',
            ]);

            // Index the chunk
            $result = $this->service->index([
                'collection_name' => $validated['collection_name'],
                'entity_type' => $validated['entity_type'],
                'entity_id' => $validated['entity_id'],
                'content' => $validated['content'],
                'document_title' => $validated['document_title'] ?? null,
                'required_permission' => $validated['required_permission'] ?? null,
                'metadata' => $validated['metadata'] ?? null,
            ]);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to index chunk',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Chunk indexed successfully',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating RAG chunk', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error creating chunk',
            ], 500);
        }
    }

    /**
     * Search chunks by query
     * 
     * POST /api/rag/search
     * Body:
     *   - query: string (required) - search query
     *   - collection_name: string (optional)
     *   - limit: int (optional, default: 10)
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'query' => 'required|string|min:1',
                'collection_name' => 'nullable|string',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            $query = DB::connection('pgsql_ai')
                ->table('ai.ai_knowledge_chunks');

            // Filter by collection if provided
            if ($validated['collection_name'] ?? null) {
                $query->where('collection_name', $validated['collection_name']);
            }

            // Simple text search
            $searchTerm = '%' . $validated['query'] . '%';
            $results = $query
                ->where('content', 'ilike', $searchTerm)
                ->orWhere('document_title', 'ilike', $searchTerm)
                ->limit($validated['limit'] ?? 10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $results,
                'meta' => [
                    'total' => count($results),
                    'query' => $validated['query'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching RAG chunks', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error searching chunks',
            ], 500);
        }
    }

    /**
     * Update a chunk
     * 
     * PUT /api/rag/chunks/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'content' => 'nullable|string',
                'document_title' => 'nullable|string|max:255',
                'required_permission' => 'nullable|string|max:255',
                'metadata' => 'nullable|array',
            ]);

            // Get the existing chunk
            $chunk = DB::connection('pgsql_ai')
                ->table('ai.ai_knowledge_chunks')
                ->where('chunk_id', $id)
                ->first();

            if (!$chunk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chunk not found',
                ], 404);
            }

            // Re-index with updated content
            $this->service->delete($chunk->entity_type, $chunk->entity_id);
            
            $result = $this->service->index([
                'collection_name' => $chunk->collection_name,
                'entity_type' => $chunk->entity_type,
                'entity_id' => $chunk->entity_id,
                'content' => $validated['content'] ?? $chunk->content,
                'document_title' => $validated['document_title'] ?? $chunk->document_title,
                'required_permission' => $validated['required_permission'] ?? $chunk->required_permission,
                'metadata' => $validated['metadata'] ?? json_decode($chunk->metadata, true),
            ]);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update chunk',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Chunk updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating RAG chunk', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating chunk',
            ], 500);
        }
    }

    /**
     * Delete a chunk
     * 
     * DELETE /api/rag/chunks/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            // Get the chunk to find entity info
            $chunk = DB::connection('pgsql_ai')
                ->table('ai.ai_knowledge_chunks')
                ->where('chunk_id', $id)
                ->first();

            if (!$chunk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chunk not found',
                ], 404);
            }

            // Delete via service
            $deleted = $this->service->delete($chunk->entity_type, $chunk->entity_id);

            if ($deleted === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete chunk',
                ], 422);
            }

            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting RAG chunk', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error deleting chunk',
            ], 500);
        }
    }

    /**
     * Bulk import chunks
     * 
     * POST /api/rag/bulk-import
     */
    public function bulkImport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'chunks' => 'required|array',
                'chunks.*.collection_name' => 'required|string',
                'chunks.*.entity_type' => 'required|string',
                'chunks.*.entity_id' => 'required|string',
                'chunks.*.content' => 'required|string',
            ]);

            $imported = 0;
            $failed = 0;

            foreach ($validated['chunks'] as $chunk) {
                $result = $this->service->index($chunk);
                if ($result) {
                    $imported++;
                } else {
                    $failed++;
                }
            }

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'failed' => $failed,
            ]);
        } catch (\Exception $e) {
            Log::error('Error bulk importing RAG chunks', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error bulk importing chunks',
            ], 500);
        }
    }

    /**
     * Get RAG index statistics
     * 
     * GET /api/rag/statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $total = DB::connection('pgsql_ai')
                ->table('ai.ai_knowledge_chunks')
                ->count();

            $collections = DB::connection('pgsql_ai')
                ->table('ai.ai_knowledge_chunks')
                ->selectRaw('collection_name, COUNT(*) as count')
                ->groupBy('collection_name')
                ->pluck('count', 'collection_name')
                ->toArray();

            $entityTypes = DB::connection('pgsql_ai')
                ->table('ai.ai_knowledge_chunks')
                ->selectRaw('entity_type, COUNT(*) as count')
                ->groupBy('entity_type')
                ->pluck('count', 'entity_type')
                ->toArray();

            return response()->json([
                'success' => true,
                'total_chunks' => $total,
                'collections' => $collections,
                'entity_types' => $entityTypes,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting RAG statistics', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error getting statistics',
            ], 500);
        }
    }

    /**
     * List all collections
     * 
     * GET /api/rag/collections
     */
    public function collections(): JsonResponse
    {
        try {
            $collections = DB::connection('pgsql_ai')
                ->table('ai.ai_knowledge_chunks')
                ->selectRaw('collection_name, COUNT(*) as chunk_count')
                ->groupBy('collection_name')
                ->get()
                ->map(function ($col) {
                    return [
                        'name' => $col->collection_name,
                        'chunk_count' => $col->chunk_count,
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $collections,
            ]);
        } catch (\Exception $e) {
            Log::error('Error listing RAG collections', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error listing collections',
            ], 500);
        }
    }
}
