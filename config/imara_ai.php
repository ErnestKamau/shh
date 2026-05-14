<?php

return [
    'api_base_url' => env('IMARA_AI_ENDPOINT', env('AI_SERVICE_URL', 'http://127.0.0.1:8081')),
    'api_base_urls' => array_values(array_filter(array_map('trim', explode(',', env('AI_SERVICE_URLS', 'http://127.0.0.1:8081,http://127.0.0.1:8080'))))),

    'source_connection' => env('DB_CONNECTION', 'pgsql'),

    'repository_connection' => env('DB_CONNECTION', 'pgsql'),

    'schemas' => [
        'ai' => env('AI_SCHEMA', 'ai'),
        'reporting' => env('AI_REPORTING_SCHEMA', 'reporting'),
    ],

    'pgvector' => [
        'enabled' => filter_var(env('AI_PGVECTOR_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'extension' => env('AI_PGVECTOR_EXTENSION', 'vector'),
    ],

    'reporting' => [
        'marts' => [
            'lab_tat',
            'qc_stability',
            'ticket_sla',
            'equipment_reliability',
            'inventory_risk',
            'document_compliance',
        ],
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
        'default_model' => 'gemma3:1b',
        'chat_model'    => 'gemma3:1b',
        'heavy_model'   => 'qwen2.5:3b',
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
