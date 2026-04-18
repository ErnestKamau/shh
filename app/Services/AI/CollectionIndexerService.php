<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CollectionIndexerService
{
    protected string $connection = 'pgsql_ai';
    protected string $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = AiEndpointResolver::resolve();
    }

    /**
     * Index a piece of content into a specific collection.
     *
     * @param string $collectionName  The target collection (e.g., 'audits', 'sops')
     * @param string $content         The raw text to index
     * @param string|null $entityType The eloquent model class
     * @param int|null $entityId      The specific record ID
     * @param array $metadata         Extra context (tags, hierarchy, etc.)
     * @param string $requiredPermission The permission string required to view this chunk.
     */
    public function indexContent(
        string $collectionName,
        string $content,
        ?string $entityType = null,
        ?int $entityId = null,
        array $metadata = [],
        string $requiredPermission = 'General.View',
        ?int $manualDocId = null
    ): void {
        // Mark document as pending before starting
        if ($manualDocId !== null) {
            DB::connection($this->connection)
                ->table('ai.ai_manual_documents')
                ->where('id', $manualDocId)
                ->update(['indexing_status' => 'pending']);
        }

        try {
            // 1. Chunk the content
            $chunks = $this->chunkText($content);

            foreach ($chunks as $index => $chunk) {
                // 2. Generate Embedding
                $vector = $this->generateEmbedding($chunk);

                if (!$vector) {
                    Log::warning("CollectionIndexerService: Failed to generate embedding for chunk {$index}");
                    continue;
                }

                // 3. Store in Vector DB
                DB::connection($this->connection)
                    ->table('ai.ai_knowledge_chunks')
                    ->insert([
                        'collection_name' => $collectionName,
                        'entity_type'     => $entityType,
                        'entity_id'       => $entityId,
                        'content'         => $chunk,
                        'embedding'       => DB::raw("'" . json_encode($vector) . "'::vector"),
                        'required_permission' => $requiredPermission,
                        'metadata'        => json_encode(array_merge($metadata, [
                            'chunk_index' => $index,
                            'manual_doc_id' => $manualDocId
                        ])),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
            }

            // Mark document as successfully indexed
            if ($manualDocId !== null) {
                DB::connection($this->connection)
                    ->table('ai.ai_manual_documents')
                    ->where('id', $manualDocId)
                    ->update([
                        'indexing_status'  => 'indexed',
                        'last_indexed_at'  => now(),
                    ]);
            }
        } catch (\Throwable $e) {
            Log::error('CollectionIndexerService: indexContent failed', [
                'error' => $e->getMessage(),
                'collection' => $collectionName,
                'entity' => "{$entityType}:{$entityId}"
            ]);

            // Mark document as failed
            if ($manualDocId !== null) {
                DB::connection($this->connection)
                    ->table('ai.ai_manual_documents')
                    ->where('id', $manualDocId)
                    ->update(['indexing_status' => 'failed']);
            }
        }
    }

    /**
     * Hybrid Chunking Strategy:
     * Combines Structural (Paragraph/Sentence aware) with Sliding Window (Overlap).
     */
    protected function chunkText(string $text, int $chunkSize = 1000, int $overlap = 200): array
    {
        $chunks = [];
        $text = str_replace(["\r\n", "\r"], "\n", $text); // Normalize newlines
        $length = mb_strlen($text);
        
        if ($length <= $chunkSize) {
            return [trim($text)];
        }

        $start = 0;
        while ($start < $length) {
            $end = $start + $chunkSize;
            
            // 1. Structural Break Check (Prefer Paragraphs > Sentences)
            if ($end < $length) {
                $lookback = substr($text, $start, $chunkSize);
                
                // Try breaking at double newline (paragraph)
                $lastParagraph = strrpos($lookback, "\n\n");
                if ($lastParagraph !== false && $lastParagraph > ($chunkSize * 0.5)) {
                    $end = $start + $lastParagraph + 2;
                } else {
                    // Try breaking at single newline
                    $lastNewline = strrpos($lookback, "\n");
                    if ($lastNewline !== false && $lastNewline > ($chunkSize * 0.7)) {
                        $end = $start + $lastNewline + 1;
                    } else {
                        // Try breaking at sentence
                        $lastSentence = strrpos($lookback, ". ");
                        if ($lastSentence !== false && $lastSentence > ($chunkSize * 0.8)) {
                            $end = $start + $lastSentence + 2;
                        }
                    }
                }
            }

            $chunk = trim(mb_substr($text, $start, $end - $start));
            if (!empty($chunk)) {
                $chunks[] = $chunk;
            }
            
            // 2. Sliding Window (Move start point back by overlap)
            $start = $end - $overlap;
            if ($start >= $length) break;
        }

        return array_values(array_unique(array_filter($chunks)));
    }

    /**
     * Generate embedding vector via the AI microservice / OpenAI / Gemini.
     */
    protected function generateEmbedding(string $text): ?array
    {
        try {
            // Forwarding to our internal FastAPI which should wrap the actual LLM API
            $response = Http::timeout(10)
                ->post("{$this->apiBaseUrl}/ai/embeddings/generate", [
                    'text' => $text,
                ]);

            if ($response->successful()) {
                return $response->json('vector');
            }
            
            // Fallback for development if API is missing
            if (app()->environment('local')) {
                return array_fill(0, 1536, 0.0); // Mock zero vector
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('CollectionIndexerService: generateEmbedding failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Clear all chunks for a specific entity (useful before re-indexing).
     */
    public function clearCollection(string $entityType, int $entityId): void
    {
        DB::connection($this->connection)
            ->table('ai.ai_knowledge_chunks')
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->delete();
    }
}
