<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\AI\AiInferenceService;

class AiKnowledgeBaseController extends Controller
{
    protected AiInferenceService $inference;

    public function __construct(AiInferenceService $inference)
    {
        $this->inference = $inference;
    }

    /**

     * Get an aggregated list of the AI's knowledge base.
     * Uses Row-Level Security to only return items the user has permission to see.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $flatPerms = $user ? $user->getFlatPermissions() : ['General.View'];

            // Query the ai_knowledge_chunks table and aggregate by entity
            // We want to group by collection_name, entity_type, and entity_id
            $query = DB::connection('pgsql_ai')
                ->table('ai.ai_knowledge_chunks')
                ->select(
                    'collection_name',
                    'entity_type',
                    'entity_id',
                    DB::raw("COALESCE(metadata->>'manual_doc_id', metadata->>'document_id')::int as manual_doc_id"),
                    DB::raw('MAX(required_permission) as required_permission'),
                    DB::raw('COUNT(*) as chunk_count'),
                    DB::raw('MAX(created_at) as last_synced_at')
                )
                ->groupBy('collection_name', 'entity_type', 'entity_id', DB::raw("COALESCE(metadata->>'manual_doc_id', metadata->>'document_id')"))
                ->orderBy('last_synced_at', 'desc');

            // Apply Row-Level Security
            if (!in_array('*', $flatPerms)) {
                $query->where(function ($q) use ($flatPerms) {
                    $q->whereNull('required_permission')
                      ->orWhereIn('required_permission', $flatPerms);
                });
            }

            $items = $query->get(); // Get all for simplified mapping or keep paginate(50)
            
            // Format for the frontend
            $formattedItems = collect($items)->map(function ($item) use ($user) {
                // Try to make entity_type more readable: App\Models\Audit -> Audit
                $readableType = $item->entity_type;
                if ($readableType && str_contains($readableType, '\\')) {
                    $parts = explode('\\', $readableType);
                    $readableType = end($parts);
                }

                if ($item->manual_doc_id) {
                    $readableType = 'Manual Entry';
                }

                $identifier = "ID: {$item->entity_id}";
                $canEdit = false;
                $canDelete = false;
                $createdBy = null;
                $createdAt = null;
                $indexingStatus = null;
                $lastIndexedAt  = null;
                
                $expiresAt = null;
                $isExpired = false;
                $tags = [];
                $externalSourceUrl = null;

                if ($item->manual_doc_id) {
                    // Try to get title and metadata for manual docs
                    $doc = DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->where('id', $item->manual_doc_id)->first();
                    $identifier = $doc ? $doc->title : "Manual Doc #{$item->manual_doc_id}";
                    
                    // Permission checks: user must have created it or be admin
                    if ($doc && $user) {
                        $createdBy = $doc->created_by;
                        $createdAt = $doc->created_at;
                        // Can edit/delete if user created it or if user has broad admin permission
                        $canEdit = ($doc->created_by === $user->id) || $user->hasPermissionTo('AI.Knowledge.Manage') || in_array('*', $user->getFlatPermissions());
                        $canDelete = ($doc->created_by === $user->id) || $user->hasPermissionTo('AI.Knowledge.Manage') || in_array('*', $user->getFlatPermissions());
                        
                        // Governance fields from manual doc
                        $expiresAt = $doc->expires_at;
                        $isExpired = $doc->expires_at && \Carbon\Carbon::parse($doc->expires_at)->isPast();
                        $metadata = json_decode($doc->metadata ?? '{}');
                        $tags = $metadata->tags ?? [];
                        $externalSourceUrl = $doc->external_source_url;
                    }

                    $indexingStatus = $doc->indexing_status ?? 'indexed';
                    $lastIndexedAt  = $doc->last_indexed_at  ?? null;
                } elseif (!$item->entity_id) {
                     $identifier = "General";
                }

                return [
                    'collection_name'  => ucfirst(str_replace('_', ' ', $item->collection_name)),
                    'entity_type'      => $readableType,
                    'identifier'       => $identifier,
                    'chunk_count'      => $item->chunk_count,
                    'last_synced_at'   => \Carbon\Carbon::parse($item->last_synced_at)->diffForHumans(),
                    'manual_doc_id'    => $item->manual_doc_id,
                    'can_edit'         => $canEdit,
                    'can_delete'       => $canDelete,
                    'created_by'       => $createdBy,
                    'created_at'       => $createdAt,
                    'indexing_status'  => $indexingStatus ?? 'system',
                    'last_indexed_at'  => $lastIndexedAt ? \Carbon\Carbon::parse($lastIndexedAt)->diffForHumans() : null,
                    'expires_at'       => $expiresAt,
                    'is_expired'       => $isExpired,
                    'tags'             => $tags,
                    'external_source_url' => $externalSourceUrl
                ];
            });

            return response()->json([
                'status' => 'ok',
                'data' => $formattedItems
            ]);

        } catch (\Throwable $e) {
            Log::error('AiKnowledgeBaseController: index failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to load knowledge base items.'], 500);
        }
    }
    /**
     * Store a new manual document.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'collection' => 'required|string',
            'content' => 'required|string',
            'permission' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'external_source_url' => 'nullable|url',
            'chunk_size' => 'nullable|integer|min:100|max:5000',
            'chunk_overlap' => 'nullable|integer|min:0|max:1000'
        ]);

        try {
            $id = DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->insertGetId([
                'title' => $request->title,
                'collection_name' => $request->collection,
                'content' => $request->content,
                'required_permission' => $request->permission ?? 'General.View',
                'expires_at' => $request->expires_at,
                'external_source_url' => $request->external_source_url,
                'metadata' => json_encode([
                    'tags' => $request->tags ?? [],
                    'chunk_size' => $request->chunk_size ?? 800,
                    'chunk_overlap' => $request->chunk_overlap ?? 100
                ]),
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Trigger Indexing via Python Service
            $this->inference->indexKnowledge([
                'collection' => $request->collection,
                'content' => $request->content,
                'metadata' => [
                    'title' => $request->title, 
                    'permission' => $request->permission ?? 'General.View',
                    'tags' => $request->tags ?? [],
                    'expires_at' => $request->expires_at,
                    'external_source_url' => $request->external_source_url,
                    'chunk_size' => $request->chunk_size ?? 800,
                    'chunk_overlap' => $request->chunk_overlap ?? 100
                ],
                'manual_doc_id' => (int) $id,
                'entity_type' => 'manual',
                'chunk_size' => $request->chunk_size ?? 800,
                'chunk_overlap' => $request->chunk_overlap ?? 100
            ]);

            return response()->json(['status' => 'ok', 'message' => 'Knowledge indexed successfully.']);
        } catch (\Throwable $e) {
            Log::error('AiKnowledgeBaseController: store failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show full content for editing.
     */
    public function show($id): JsonResponse
    {
        try {
            $user = auth()->user();
            $doc = DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->where('id', $id)->first();
            
            if (!$doc) {
                return response()->json(['status' => 'error', 'message' => 'Document not found.'], 404);
            }

            // Authorization: user must have created it or have admin permission
            if (!$user || !($doc->created_by === $user->id || $user->hasPermissionTo('AI.Knowledge.Manage') || in_array('*', $user->getFlatPermissions()))) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized to edit this document.'], 403);
            }

            return response()->json(['status' => 'ok', 'data' => $doc]);
        } catch (\Throwable $e) {
            Log::error('AiKnowledgeBaseController: show failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve document.'], 500);
        }
    }

    /**
     * Update manual document.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'collection' => 'required|string',
            'content' => 'required|string',
            'permission' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'external_source_url' => 'nullable|url',
            'chunk_size' => 'nullable|integer|min:100|max:5000',
            'chunk_overlap' => 'nullable|integer|min:0|max:1000'
        ]);

        try {
            $user = auth()->user();
            $doc = DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->where('id', $id)->first();

            if (!$doc) {
                return response()->json(['status' => 'error', 'message' => 'Document not found.'], 404);
            }

            // Authorization: user must have created it or have admin permission
            if (!$user || !($doc->created_by === $user->id || $user->hasPermissionTo('AI.Knowledge.Manage') || in_array('*', $user->getFlatPermissions()))) {
                Log::warning('Unauthorized KB edit attempt', ['user_id' => auth()->id(), 'document_id' => $id, 'doc_created_by' => $doc->created_by]);
                return response()->json(['status' => 'error', 'message' => 'Unauthorized to edit this document.'], 403);
            }

            // 1. Update text
            DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->where('id', $id)->update([
                'title' => $request->title,
                'collection_name' => $request->collection,
                'content' => $request->content,
                'required_permission' => $request->permission ?? 'General.View',
                'expires_at' => $request->expires_at,
                'external_source_url' => $request->external_source_url,
                'metadata' => json_encode([
                    'tags' => $request->tags ?? [],
                    'chunk_size' => $request->chunk_size ?? 800,
                    'chunk_overlap' => $request->chunk_overlap ?? 100
                ]),
                'updated_at' => now(),
            ]);

            Log::info('Knowledge document updated', ['document_id' => $id, 'updated_by' => $user->id]);

            // 2. Clear old chunks
            DB::connection('pgsql_ai')->table('ai.ai_knowledge_chunks')
                ->whereRaw("metadata->>'manual_doc_id' = ?", [$id])
                ->delete();

            // 3. Re-index via Python Service
            $this->inference->indexKnowledge([
                'collection' => $request->collection,
                'content' => $request->content,
                'metadata' => [
                    'title' => $request->title, 
                    'permission' => $request->permission ?? 'General.View',
                    'tags' => $request->tags ?? [],
                    'chunk_size' => $request->chunk_size ?? 800,
                    'chunk_overlap' => $request->chunk_overlap ?? 100
                ],
                'manual_doc_id' => (int) $id,
                'entity_type' => 'manual',
                'chunk_size' => $request->chunk_size ?? 800,
                'chunk_overlap' => $request->chunk_overlap ?? 100
            ]);

            return response()->json(['status' => 'ok', 'message' => 'Knowledge updated successfully.']);
        } catch (\Throwable $e) {
            Log::error('AiKnowledgeBaseController: update failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete manual document.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = auth()->user();
            $doc = DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->where('id', $id)->first();

            if (!$doc) {
                return response()->json(['status' => 'error', 'message' => 'Document not found.'], 404);
            }

            // Authorization: user must have created it or have admin permission
            if (!$user || !($doc->created_by === $user->id || $user->hasPermissionTo('AI.Knowledge.Manage') || in_array('*', $user->getFlatPermissions()))) {
                Log::warning('Unauthorized KB delete attempt', ['user_id' => auth()->id(), 'document_id' => $id, 'doc_created_by' => $doc->created_by]);
                return response()->json(['status' => 'error', 'message' => 'Unauthorized to delete this document.'], 403);
            }

            Log::info('Knowledge document deleted', ['document_id' => $id, 'document_title' => $doc->title, 'deleted_by' => $user->id]);

            // 1. Clear chunks via Python Service
            $this->inference->deleteKnowledge('manual', (string) $id);

            // 2. Delete source
            DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->where('id', $id)->delete();

            return response()->json(['status' => 'ok', 'message' => 'Knowledge removed successfully.']);
        } catch (\Throwable $e) {
             Log::error('AiKnowledgeBaseController: destroy failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Re-index an existing manual document without changing its content.
     */
    public function reindex($id): JsonResponse
    {
        try {
            $user = auth()->user();
            $doc  = DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->where('id', $id)->first();

            if (!$doc) {
                return response()->json(['status' => 'error', 'message' => 'Document not found.'], 404);
            }

            if (!$user || !($doc->created_by === $user->id || $user->hasPermissionTo('AI.Knowledge.Manage') || in_array('*', $user->getFlatPermissions()))) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
            }

            // Clear existing chunks
            DB::connection('pgsql_ai')->table('ai.ai_knowledge_chunks')
                ->whereRaw("metadata->>'manual_doc_id' = ?", [$id])
                ->delete();

            // Parse metadata for tuning params
            $meta = json_decode($doc->metadata ?? '{}');
            $chunkSize = $meta->chunk_size ?? 800;
            $chunkOverlap = $meta->chunk_overlap ?? 100;

            // Re-index via Python Service
            $result = $this->inference->indexKnowledge([
                'collection' => $doc->collection_name,
                'content' => $doc->content,
                'metadata' => [
                    'title' => $doc->title, 
                    'permission' => $doc->required_permission ?? 'General.View',
                    'tags' => $meta->tags ?? [],
                    'chunk_size' => $chunkSize,
                    'chunk_overlap' => $chunkOverlap
                ],
                'manual_doc_id' => (int) $id,
                'entity_type' => 'manual',
                'chunk_size' => $chunkSize,
                'chunk_overlap' => $chunkOverlap
            ]);

            return response()->json([
                'status' => 'ok', 
                'message' => 'Document queued for re-indexing.',
                'run_id' => $result['run_id'] ?? null
            ]);
        } catch (\Throwable $e) {
            Log::error('AiKnowledgeBaseController: reindex failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle file uploads (PDF, DOCX, CSV) for the Knowledge Base.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,docx,doc,csv,txt,md|max:20480', // 20MB limit
            'collection' => 'required|string',
            'permission' => 'nullable|string'
        ]);

        try {
            $file = $request->file('file');
            $title = $file->getClientOriginalName();
            
            // 1. Create a placeholder record in the database
            $id = DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->insertGetId([
                'title' => $title,
                'collection_name' => $request->collection,
                'content' => "[Processing file: $title]",
                'required_permission' => $request->permission ?? 'General.View',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Forward to Python Service for parsing and indexing
            $result = $this->inference->uploadKnowledgeFile($file, [
                'collection' => $request->collection,
                'permission' => $request->permission ?? 'General.View',
                'manual_doc_id' => (int) $id,
                'metadata' => [
                    'original_filename' => $title,
                    'file_type' => $file->getClientOriginalExtension(),
                    'company_id' => auth()->user()->company_id ?? 1,
                ]
            ]);

            if ($result['status'] === 'ok') {
                // Optionally update the content field with a summary or acknowledgment
                DB::connection('pgsql_ai')->table('ai.ai_manual_documents')
                    ->where('id', $id)
                    ->update(['content' => "[File indexed: $title. Contains {$result['indexed_count']} chunks.]"]);

                return response()->json([
                    'status' => 'ok', 
                    'message' => "File '{$title}' processed and indexed successfully.",
                    'data' => $result
                ]);
            }

            // Cleanup if failed
            DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->where('id', $id)->delete();
            return response()->json(['status' => 'error', 'message' => $result['message'] ?? 'Upload failed'], 500);

        } catch (\Throwable $e) {
            Log::error('AiKnowledgeBaseController: upload failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk delete manual documents.
     */
    public function batchDestroy(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);
        $ids = $request->ids;

        try {
            $user = auth()->user();
            foreach ($ids as $id) {
                $doc = DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->where('id', $id)->first();
                if (!$doc) continue;

                // Authorization check for each
                if ($doc->created_by !== $user->id && !$user->hasPermissionTo('AI.Knowledge.Manage') && !in_array('*', $user->getFlatPermissions())) {
                    continue; // Skip unauthorized
                }

                // 1. Clear chunks via Python Service
                $this->inference->deleteKnowledge('manual', (string) $id, $user->company_id ?? 0);

                // 2. Delete source
                DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->where('id', $id)->delete();
            }

            return response()->json(['status' => 'ok', 'message' => count($ids) . ' items processed.']);
        } catch (\Throwable $e) {
            Log::error('AiKnowledgeBaseController: batchDestroy failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk re-index manual documents.
     */
    public function batchReindex(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);
        $ids = $request->ids;

        try {
            $user = auth()->user();
            foreach ($ids as $id) {
                $doc = DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->where('id', $id)->first();
                if (!$doc) continue;

                // Authorization check
                if ($doc->created_by !== $user->id && !$user->hasPermissionTo('AI.Knowledge.Manage') && !in_array('*', $user->getFlatPermissions())) {
                    continue;
                }

                // Clear existing chunks
                DB::connection('pgsql_ai')->table('ai.ai_knowledge_chunks')
                    ->whereRaw("metadata->>'manual_doc_id' = ?", [$id])
                    ->delete();

                // Parse metadata for tuning params
                $meta = json_decode($doc->metadata ?? '{}');
                $chunkSize = $meta->chunk_size ?? 800;
                $chunkOverlap = $meta->chunk_overlap ?? 100;

                // Re-index via Python Service
                $this->inference->indexKnowledge([
                    'collection' => $doc->collection_name,
                    'content' => $doc->content,
                    'metadata' => [
                        'title' => $doc->title, 
                        'permission' => $doc->required_permission ?? 'General.View',
                        'tags' => $meta->tags ?? [],
                        'company_id' => $user->company_id ?? 1,
                        'chunk_size' => $chunkSize,
                        'chunk_overlap' => $chunkOverlap
                    ],
                    'manual_doc_id' => (int) $id,
                    'entity_type' => 'manual',
                    'chunk_size' => $chunkSize,
                    'chunk_overlap' => $chunkOverlap
                ]);
            }

            return response()->json(['status' => 'ok', 'message' => count($ids) . ' items queued for re-indexing.']);
        } catch (\Throwable $e) {
            Log::error('AiKnowledgeBaseController: batchReindex failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Test semantic search retrieval.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string']);

        try {
            $results = $this->inference->searchKnowledge($request->query, [
                'company_id' => auth()->user()->company_id ?? 1,
                'limit' => 5
            ]);

            return response()->json($results);
        } catch (\Throwable $e) {
            Log::error('AiKnowledgeBaseController: search failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload an image for the markdown editor.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:5120']); // 5MB limit

        try {
            $file = $request->file('image');
            $path = $file->store('ai/media', 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'status' => 'ok',
                'url' => $url
            ]);
        } catch (\Throwable $e) {
            Log::error('AiKnowledgeBaseController: uploadImage failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the full-page editor.
     */
    public function editor($id = null)
    {
        $doc = null;
        if ($id) {
            $doc = DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->where('id', $id)->first();
            if (!$doc) abort(404);
            
            $user = auth()->user();
            if ($doc->created_by !== $user->id && !$user->hasPermissionTo('AI.Knowledge.Manage') && !in_array('*', $user->getFlatPermissions())) {
                abort(403);
            }
        }

        return view('imara-ai.knowledge.editor', compact('doc'));
    }

    /**
     * Get real-time status of an indexing run.
     */
    public function getSyncStatus($runId): JsonResponse
    {
        try {
            $run = DB::connection('pgsql_ai')
                ->table('reporting.sync_runs')
                ->where('id', $runId)
                ->first();

            if (!$run) {
                return response()->json(['status' => 'error', 'message' => 'Run not found.'], 404);
            }

            return response()->json([
                'status' => 'ok',
                'data' => [
                    'id' => $run->id,
                    'sync_status' => $run->status,
                    'stage' => $run->stage,
                    'rows_synced' => $run->rows_synced,
                    'rows_failed' => $run->rows_failed,
                    'chunk_count' => $run->chunk_count,
                    'error_message' => $run->error_message,
                    'updated_at' => $run->updated_at
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('AiKnowledgeBaseController: getSyncStatus failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
