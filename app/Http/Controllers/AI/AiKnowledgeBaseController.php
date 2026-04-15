<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\AI\CollectionIndexerService;

class AiKnowledgeBaseController extends Controller
{
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
                    DB::raw("(metadata->>'manual_doc_id')::int as manual_doc_id"),
                    DB::raw('MAX(required_permission) as required_permission'),
                    DB::raw('COUNT(*) as chunk_count'),
                    DB::raw('MAX(created_at) as last_synced_at')
                )
                ->groupBy('collection_name', 'entity_type', 'entity_id', DB::raw("metadata->>'manual_doc_id'"))
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
            'permission' => 'nullable|string'
        ]);

        try {
            $id = DB::connection('pgsql_ai')->table('ai.ai_manual_documents')->insertGetId([
                'title' => $request->title,
                'collection_name' => $request->collection,
                'content' => $request->content,
                'required_permission' => $request->permission ?? 'General.View',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Trigger Indexing
            app(CollectionIndexerService::class)->indexContent(
                $request->collection,
                $request->content,
                null,
                null,
                ['manual' => true, 'title' => $request->title],
                $request->permission ?? 'General.View',
                $id
            );

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
            'permission' => 'nullable|string'
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
                'updated_at' => now(),
            ]);

            Log::info('Knowledge document updated', ['document_id' => $id, 'updated_by' => $user->id]);

            // 2. Clear old chunks
            DB::connection('pgsql_ai')->table('ai.ai_knowledge_chunks')
                ->whereRaw("metadata->>'manual_doc_id' = ?", [$id])
                ->delete();

            // 3. Re-index
            app(CollectionIndexerService::class)->indexContent(
                $request->collection,
                $request->content,
                null,
                null,
                ['manual' => true, 'title' => $request->title],
                $request->permission ?? 'General.View',
                $id
            );

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

            // 1. Clear chunks
            DB::connection('pgsql_ai')->table('ai.ai_knowledge_chunks')
                ->whereRaw("metadata->>'manual_doc_id' = ?", [$id])
                ->delete();

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

            // Re-index with existing content (CollectionIndexerService will set status)
            app(CollectionIndexerService::class)->indexContent(
                $doc->collection_name,
                $doc->content,
                null,
                null,
                ['manual' => true, 'title' => $doc->title],
                $doc->required_permission ?? 'General.View',
                (int) $id
            );

            return response()->json(['status' => 'ok', 'message' => 'Document queued for re-indexing.']);
        } catch (\Throwable $e) {
            Log::error('AiKnowledgeBaseController: reindex failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
