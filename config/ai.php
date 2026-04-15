<?php

return [
    /*
     * RAG (Retrieval-Augmented Generation) Configuration - Phase 2: Secure Hybrid Retrieval
     */
    'rag' => [
        /**
         * PHASE 1: Core Retrieval Refactor
         */
        'enable_query_classification' => env('AI_RAG_ENABLE_QUERY_CLASSIFICATION', true),
        'enable_acl_filtering' => env('AI_RAG_ENABLE_ACL_FILTERING', true),
        
        /**
         * PHASE 2: Hybrid Search (Vector + Keyword/FTS)
         */
        'enable_keyword_search' => env('AI_RAG_ENABLE_KEYWORD_SEARCH', true),
        'enable_deduplication' => env('AI_RAG_ENABLE_DEDUPLICATION', true),
        'enable_bias_boosting' => env('AI_RAG_ENABLE_BIAS_BOOSTING', true),
        
        /**
         * PHASE 3: Re-ranking
         */
        'enable_reranking' => env('AI_RAG_ENABLE_RERANKING', false),
        'enable_cross_encoder_reranking' => env('AI_RAG_ENABLE_CROSS_ENCODER_RERANKING', false),
        'reranker_timeout_ms' => env('AI_RAG_RERANKER_TIMEOUT_MS', 300),
        'reranker_failure_threshold' => env('AI_RAG_RERANKER_FAILURE_THRESHOLD', 3),
        'reranker_endpoint' => env('AI_RAG_RERANKER_ENDPOINT', 'http://127.0.0.1:8080/v1/rerank'),
        
        /**
         * PHASE 4: Real-Time Synchronization
         */
        'enable_realtime_sync' => env('AI_RAG_ENABLE_REALTIME_SYNC', true),
        
        /**
         * PHASE 5: Monitoring & Diagnostics
         */
        'debug_rag' => env('AI_RAG_DEBUG', false),
        
        /**
         * Candidate Limiting
         */
        'candidate_limits' => [
            'vector_top_k' => env('AI_RAG_VECTOR_TOP_K', 20),
            'keyword_top_k' => env('AI_RAG_KEYWORD_TOP_K', 20),
            'final_top_k' => env('AI_RAG_FINAL_TOP_K', 8),
        ],
        
        /**
         * Legacy Configuration (kept for backward compatibility)
         */
        'similarity_threshold' => env('AI_RAG_SIMILARITY_THRESHOLD', 0.3),
        'dedup_threshold' => env('AI_RAG_DEDUP_THRESHOLD', 0.8),
        'age_penalty_months' => env('AI_RAG_AGE_PENALTY_MONTHS', 6),
        
        /**
         * Bias Boosting Configuration
         */
        'bias_boosts' => [
            'exact_code_match' => env('AI_RAG_BOOST_EXACT_CODE', 0.15), // +15%
            'title_match' => env('AI_RAG_BOOST_TITLE', 0.10), // +10%
            'recency_boost' => env('AI_RAG_BOOST_RECENCY', 0.05), // +5%
            'recency_days' => env('AI_RAG_BOOST_RECENCY_DAYS', 30),
        ],
    ],

    /**
     * AI Orchestration Mode: Controls the priority and availability of AI services.
     */
    'orchestration_mode' => env('AI_ORCHESTRATION_MODE', 'balanced'),
];
