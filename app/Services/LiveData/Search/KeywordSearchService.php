<?php

namespace App\Services\LiveData\Search;

use App\User;
use App\Support\ReturnTypes\RagRetrievalResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Support\ScoreNormalizationService;

/**
 * Keyword-based search utility for RAG fallback and direct operational search.
 * Relocated to LiveData/Search to support standalone resilience.
 */
class KeywordSearchService
{
    protected string $connection = 'pgsql_ai';
    protected string $table = 'ai.ai_knowledge_chunks';

    public function __construct(protected ScoreNormalizationService $scoreNormalizer) {}

    public function search(string $query, ?User $user = null, ?int $limit = null): Collection
    {
        $limit = $limit ?? config('ai.rag.candidate_limits.keyword_top_k', 20);
        $preparedQuery = $this->prepareTsQuery($query);

        if (empty($preparedQuery)) return collect();

        try {
            $builder = DB::connection($this->connection)->table($this->table)
                ->whereRaw("tsvector_content @@ to_tsquery('english', ?)", [$preparedQuery])
                ->select([
                    'id', 'chunk_id', 'collection_name', 'entity_type', 
                    'entity_id', 'content', 'required_permission', 'metadata', 
                    'embedding_version',
                    DB::raw("ts_rank_cd(tsvector_content, to_tsquery('english', ?), 32) as ts_rank_score"),
                ], [$preparedQuery]);

            if ($user) {
                // Simplified ACL for the LiveData context
                $builder->where(function ($q) use ($user) {
                    $q->whereIn('required_permission', $user->getFlatPermissions())
                      ->orWhereNull('required_permission');
                });
            }

            $results = $builder->orderByDesc('ts_rank_score')->limit($limit)->get();

            return $results->map(function ($row) use ($preparedQuery) {
                $score = $this->scoreNormalizer->normalizeKeywordScore((float) ($row->ts_rank_score ?? 0));
                
                return RagRetrievalResult::fromKeywordResult([
                    'chunk_id' => $row->chunk_id ?? $row->id,
                    'document_id' => $row->entity_id,
                    'document_title' => $row->metadata['title'] ?? 'Operational Manual',
                    'content' => $row->content,
                    'source_type' => 'keyword_fts',
                    'score_keyword' => $score,
                    'score_keyword_normalized' => $score,
                    'rank_origin' => ['keyword_fts'],
                    'embedding_version' => $row->embedding_version ?? 'v1',
                    'metadata' => is_string($row->metadata) ? json_decode($row->metadata, true) : ($row->metadata ?? []),
                    'collection_name' => $row->collection_name,
                    'entity_type' => $row->entity_type,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('KeywordSearchService: search failed', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    protected function prepareTsQuery(string $query): string
    {
        $query = preg_replace('/[^\w\s\-"]/u', ' ', trim($query));
        $terms = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);
        
        if (empty($terms)) return '';
        
        return implode(' & ', array_unique($terms));
    }
}
