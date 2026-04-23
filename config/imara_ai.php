<?php

return [
    'api_base_url' => env('IMARA_AI_ENDPOINT', env('AI_SERVICE_URL', 'http://127.0.0.1:8081')),
    'api_base_urls' => array_values(array_filter(array_map('trim', explode(',', env('AI_SERVICE_URLS', 'http://127.0.0.1:8080,http://127.0.0.1:8081'))))),

    'source_connection' => env('AI_SOURCE_CONNECTION', env('DB_CONNECTION', 'mysql')),

    'repository_connection' => env('AI_REPOSITORY_CONNECTION', 'pgsql_ai'),

    'schemas' => [
        'ai' => env('AI_SCHEMA', 'ai'),
    ],

    'pgvector' => [
        'enabled' => filter_var(env('AI_PGVECTOR_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'extension' => env('AI_PGVECTOR_EXTENSION', 'vector'),
    ],

    'etl' => [
        'chunk_size' => (int) env('AI_ETL_CHUNK_SIZE', 500),
        'tables' => [
            // Only tables targeted for AI analysis/Vectorization remain
            'qc_results' => [
                'source_table' => 'qc_results',
                'target_table' => 'qc_results_ai',
                'schema' => 'ai',
                'primary_key' => 'id',
            ],
        ],
    ],

    'reporting' => [
        'refresh_interval_minutes' => (int) env('REPORTING_REFRESH_INTERVAL_MINUTES', 60),
        'sync_before_refresh' => filter_var(env('REPORTING_SYNC_BEFORE_REFRESH', true), FILTER_VALIDATE_BOOLEAN),
        'marts' => [
            'lab_tat',
            'qc_stability',
            'ticket_sla',
            'equipment_reliability',
            'inventory_risk',
            'document_compliance',
        ],
    ],

    'ai_analytics' => [
        'auto_refresh' => filter_var(env('AI_ANALYTICS_AUTO_REFRESH', false), FILTER_VALIDATE_BOOLEAN),
        'sync_before_analysis' => filter_var(env('AI_ANALYTICS_SYNC_BEFORE', true), FILTER_VALIDATE_BOOLEAN),
    ],

    /*
    |--------------------------------------------------------------------------
    | Imarachat AI System Defaults
    |--------------------------------------------------------------------------
    | Master defaults used as fallbacks when dynamic settings are missing or
    | when explicitly resetting configurations in the UI.
    */
    'defaults' => [
        'agent_name'    => 'Imarachat AI',
        'tone_of_voice' => 'Professional',
        'default_model' => 'qwen2.5:3b',
        'temperature'   => 0.3,
        'max_tokens'    => 2048,
        'top_p'         => 0.9,
        'streaming'     => true,
        'system_prompt' => "You are Imarachat AI Assistant, a specialized intelligence layer for laboratory and inventory management.

Your responsibilities:
- Provide accurate, contextual responses based on conversation history
- Reference prior messages when appropriate
- Maintain consistency across multi-turn conversations
- Explain limitations transparently
- Format structured data clearly

Context Rules:
- Use conversation history to understand references and context
- If asked about \"the first one\" or \"that sample\", infer from prior messages
- Preserve accuracy over completeness
- When uncertain, ask clarifying questions

Response Format:
- Begin with direct answer to the question
- Reference prior context when relevant
- Provide supporting details or examples
- End with follow-up suggestions if appropriate",
    ],
];
