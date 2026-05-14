<?php

namespace App\Services\Documents;

use App\Models\DMS\Document;
use App\Services\AI\AiInferenceService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentKnowledgeService
{
    protected $inference;

    public function __construct(AiInferenceService $inference)
    {
        $this->inference = $inference;
    }

    /**
     * Index a document in the AI Knowledge Base.
     */
    public function indexDocument(Document $document): array
    {
        try {
            $content = $this->extractContent($document);
            
            if (!$content && !$document->kb_content) {
                return ['status' => 'error', 'message' => 'No content found to index.'];
            }

            $requiredPermission = $document->kb_required_permission ?: 'general.view';
            $collection = $document->kb_collection ?: 'General Documents';

            $payload = [
                'collection' => $collection,
                'content' => $content ?: $document->kb_content,
                'entity_type' => 'document',
                'entity_id' => $document->id,
                'metadata' => [
                    'title' => $document->title,
                    'document_number' => $document->document_number,
                    'permission' => $requiredPermission,
                    'company_id' => 1, // Defaulting as per current AI service patterns
                    'chunk_size' => $document->kb_chunk_size ?: 800,
                    'chunk_overlap' => $document->kb_chunk_overlap ?: 100,
                ],
                'chunk_size' => $document->kb_chunk_size ?: 800,
                'chunk_overlap' => $document->kb_chunk_overlap ?: 100,
            ];

            $result = $this->inference->indexKnowledge($payload);

            if ($result['status'] === 'ok') {
                $document->update([
                    'kb_indexing_status' => 'indexed',
                    'kb_last_indexed_at' => now(),
                ]);
            } else {
                $document->update(['kb_indexing_status' => 'failed']);
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('DocumentKnowledgeService: Indexing failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            $document->update(['kb_indexing_status' => 'failed']);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Remove a document from the AI Knowledge Base.
     */
    public function removeDocument(Document $document): array
    {
        try {
            $result = $this->inference->deleteKnowledge('document', $document->id);
            
            $document->update([
                'is_kb_indexed' => false,
                'kb_indexing_status' => null,
                'kb_last_indexed_at' => null,
            ]);

            return $result;
        } catch (\Throwable $e) {
            Log::error('DocumentKnowledgeService: Removal failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Extract text content from the document file.
     * In a real implementation, this might call the Python service's parser directly
     * or use a local parser. For now, we'll mimic the existing pattern of forwarding
     * to the Python service if it's a file.
     */
    protected function extractContent(Document $document): ?string
    {
        if (!$document->file_path) {
            return null;
        }

        // If it's a manual text entry with a file placeholder, we use kb_content
        if ($document->kb_content && str_contains($document->kb_content, '[Processing file')) {
            return null;
        }

        // For actual files, the Python service can handle parsing if we send it.
        // But the indexKnowledge API expects 'content' (the text).
        // If we want the Python service to parse the file, we should use the upload API pattern.
        
        // However, for centralized management, we might want a unified 'reindex' that 
        // works for both manual text and files already in storage.
        
        // Check if file exists in DMS storage
        if (!Storage::disk('dms')->exists($document->file_path)) {
            return null;
        }

        // For now, we'll return the kb_content if it exists, 
        // otherwise we might need a way to trigger the Python parser for an existing path.
        return $document->kb_content;
    }
}
