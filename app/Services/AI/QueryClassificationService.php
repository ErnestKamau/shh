<?php

namespace App\Services\AI;

/**
 * Classifies user queries to determine optimal retrieval strategy.
 * 
 * Classification Types:
 * - exact_code: Code patterns (SOP-104, batch ID, sample ID)
 * - semantic: Natural language questions/statements
 * - mixed: Both code + natural language
 * - ambiguous: Unable to classify with confidence
 */
class QueryClassificationService
{
    protected const CODE_PATTERNS = [
        'sop' => '/SOP[_-]?\d+/i',
        'batch' => '/batch[_-]?\d+/i',
        'sample' => '/sample[_-]?[A-Z0-9]+/i',
        'capa' => '/CAPA[_-]?\d+/i',
        'ticket' => '/ticket[_-]?[A-Z0-9]+/i',
        'form' => '/form[_-]?\d+/i',
        'document_no' => '/DOC[_-]?\d+/i',
    ];
    
    protected const SEMANTIC_PATTERNS = [
        'question' => '/(how|what|why|when|where|which|who|can|should|will|is|are|do|does)\b/i',
        'imperative' => '/\b(show|find|get|list|display|explain|describe)\b/i',
    ];

    /**
     * Classify a query to determine retrieval strategy
     */
    public function classify(string $query): QueryClassification
    {
        $query = trim($query);
        $lowerQuery = strtolower($query);
        $wordCount = str_word_count($query);
        
        $codeMatches = $this->extractCodePatterns($query);
        $hasSemanticSignals = $this->hasSemanticSignals($query);
        
        // Determine classification type
        // Check for mixed first (codes + semantic)
        if (!empty($codeMatches) && $hasSemanticSignals) {
            // Both codes and natural language
            $type = 'mixed';
            $confidence = 0.85;
            $weights = ['vector' => 0.5, 'keyword' => 0.5];
        } elseif (!empty($codeMatches) && $wordCount <= 5) {
            // Short query with only codes
            $type = 'exact_code';
            $confidence = 0.95;
            $weights = ['vector' => 0.2, 'keyword' => 0.8];
        } elseif ($hasSemanticSignals && empty($codeMatches)) {
            // Natural language question/statement
            $type = 'semantic';
            $confidence = 0.90;
            $weights = ['vector' => 0.8, 'keyword' => 0.2];
        } else {
            // Unable to classify with high confidence
            $type = 'ambiguous';
            $confidence = 0.60;
            $weights = ['vector' => 0.5, 'keyword' => 0.5];
        }
        
        // Determine routing strategy
        if ($type === 'exact_code') {
            $routingStrategy = 'keyword_heavy';
        } elseif ($type === 'semantic') {
            $routingStrategy = 'vector_heavy';
        } elseif ($type === 'mixed') {
            $routingStrategy = 'hybrid';
        } else {
            // Ambiguous queries benefit from hybrid + optional rerank
            $routingStrategy = 'hybrid_with_rerank';
        }
        
        return new QueryClassification(
            type: $type,
            confidence: $confidence,
            extraction: [
                'codes' => array_keys($codeMatches),
                'keyword' => $this->extractPrimaryKeyword($query),
            ],
            routingStrategy: $routingStrategy,
            weights: $weights,
        );
    }
    
    /**
     * Extract code patterns from query
     */
    protected function extractCodePatterns(string $query): array
    {
        $matches = [];
        
        foreach (self::CODE_PATTERNS as $type => $pattern) {
            if (preg_match_all($pattern, $query, $found)) {
                foreach ($found[0] as $code) {
                    $matches[$code] = $type;
                }
            }
        }
        
        return $matches;
    }
    
    /**
     * Check if query has semantic signals (question words, imperatives)
     */
    protected function hasSemanticSignals(string $query): bool
    {
        foreach (self::SEMANTIC_PATTERNS as $pattern) {
            if (preg_match($pattern, $query)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Extract primary keyword from query (for bias boosting)
     */
    protected function extractPrimaryKeyword(string $query): ?string
    {
        $words = preg_split('/\s+/', trim($query));
        
        // Filter stop words
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        
        foreach ($words as $word) {
            $clean = strtolower(trim($word, '.,!?;:'));
            if (strlen($clean) > 2 && !in_array($clean, $stopWords)) {
                return $clean;
            }
        }
        
        return null;
    }
}

/**
 * Immutable value object for query classification result
 */
class QueryClassification
{
    public readonly string $type; // exact_code, semantic, mixed, ambiguous
    public readonly float $confidence; // 0.0 to 1.0
    public readonly array $extraction; // codes, keyword
    public readonly string $routingStrategy; // keyword_heavy, vector_heavy, hybrid, hybrid_with_rerank
    public readonly array $weights; // vector => 0.0-1.0, keyword => 0.0-1.0

    public function __construct(
        string $type,
        float $confidence,
        array $extraction,
        string $routingStrategy,
        array $weights,
    ) {
        $this->type = $type;
        $this->confidence = $confidence;
        $this->extraction = $extraction;
        $this->routingStrategy = $routingStrategy;
        $this->weights = $weights;
    }
}
