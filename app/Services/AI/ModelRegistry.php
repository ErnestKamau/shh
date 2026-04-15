<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ModelRegistry
 * 
 * Centralized registry of available AI models with capability metadata.
 * Tracks:
 * - Model names and endpoints
 * - Capabilities (RAG, vision, structured output, etc)
 * - Cost metrics (tokens per request)
 * - Performance characteristics (latency, throughput)
 * - Availability/status
 * 
 * This enables transparent model selection and A/B testing.
 */
class ModelRegistry
{
    /**
     * Registered models: name => config
     */
    protected array $models = [];

    /**
     * Model metadata resolved at runtime
     */
    protected array $metadata = [];

    public function __construct()
    {
        $this->loadFromDatabase();
    }

    /**
     * Load models from the database registry
     * Falls back to default models if database is empty or unavailable
     */
    public function loadFromDatabase(): void
    {
        try {
            $dbModels = \Illuminate\Support\Facades\DB::connection('pgsql_ai')
                ->table('ai.ai_model_registry')
                ->where('is_deprecated', false)
                ->get();

            if ($dbModels->isEmpty()) {
                $this->initializeDefaultModels();
                return;
            }

            $this->models = [];
            foreach ($dbModels as $row) {
                $fullName = $row->model_name . ($row->version ? ':' . $row->version : '');
                
                // Decode JSON columns if they exist and are strings (depends on DB driver)
                $metrics = is_string($row->metrics) ? json_decode($row->metrics, true) : (array) $row->metrics;
                $params = is_string($row->hyperparameters) ? json_decode($row->hyperparameters, true) : (array) $row->hyperparameters;

                // Map database columns to registry format with intelligent defaults
                $this->models[$fullName] = [
                    'provider' => strtolower($row->framework ?? 'ollama'),
                    'endpoint' => env('OLLAMA_HOST', 'http://localhost:11434') . '/api/generate',
                    'context_size' => $params['context_size'] ?? 4096,
                    'max_tokens' => $params['max_tokens'] ?? 2048,
                    'capabilities' => [
                        'text_generation' => true,
                        'reasoning' => $params['capabilities']['reasoning'] ?? (str_contains($row->model_name, 'qwen')),
                        'structured_output' => true,
                        'vision' => $params['capabilities']['vision'] ?? false,
                        'function_calling' => false,
                    ],
                    'performance' => [
                        'avg_latency_ms' => $metrics['avg_latency_ms'] ?? 500,
                        'throughput_tokens_per_sec' => 50,
                        'cost_per_1k_input_tokens' => 0.0,
                        'cost_per_1k_output_tokens' => 0.0,
                    ],
                    'status' => $row->is_active ? 'available' : 'unavailable',
                    'tier' => $params['tier'] ?? (str_contains($row->version, '.8b') ? 'lightweight' : 'primary'),
                    'use_cases' => $params['use_cases'] ?? ['general_conversation'],
                ];
            }
        } catch (\Exception $e) {
            Log::warning("Could not load AI models from database, using defaults: " . $e->getMessage());
            $this->initializeDefaultModels();
        }
    }

    /**
     * Force a refresh of the registry from the database
     */
    public function refresh(): void
    {
        $this->loadFromDatabase();
    }

    /**
     * Initialize registry with default models
     */
    protected function initializeDefaultModels(): void
    {
        // Primary model: qwen2.5:3b (fast, accurate, moderate reasoning)
        $this->registerModel('qwen2.5:3b', [
            'provider' => 'ollama',
            'endpoint' => 'http://localhost:11434/api/generate',
            'context_size' => 32768,
            'max_tokens' => 4096,
            'capabilities' => [
                'text_generation' => true,
                'reasoning' => true,
                'structured_output' => true,
                'vision' => false,
                'function_calling' => false,
            ],
            'performance' => [
                'avg_latency_ms' => 800,
                'throughput_tokens_per_sec' => 50,
                'cost_per_1k_input_tokens' => 0.0,   // Local, no cost
                'cost_per_1k_output_tokens' => 0.0,
            ],
            'status' => 'available',
            'tier' => 'primary',
            'use_cases' => [
                'general_conversation',
                'rag_synthesis',
                'live_data_queries',
                'intent_detection',
            ],
        ]);

        // Secondary model: qwen3.5:0.8b (ultra-fast, lightweight)
        $this->registerModel('qwen3.5:0.8b', [
            'provider' => 'ollama',
            'endpoint' => 'http://localhost:11434/api/generate',
            'context_size' => 2048,
            'max_tokens' => 512,
            'capabilities' => [
                'text_generation' => true,
                'reasoning' => false,
                'structured_output' => true,
                'vision' => false,
                'function_calling' => false,
            ],
            'performance' => [
                'avg_latency_ms' => 200,
                'throughput_tokens_per_sec' => 200,
                'cost_per_1k_input_tokens' => 0.0,
                'cost_per_1k_output_tokens' => 0.0,
            ],
            'status' => 'available',
            'tier' => 'lightweight',
            'use_cases' => [
                'quick_responses',
                'classification',
                'simple_queries',
            ],
        ]);

        // Future: Vision-capable model (placeholder)
        $this->registerModel('vision-model-future', [
            'provider' => 'ollama',
            'endpoint' => 'http://localhost:11434/api/generate',
            'context_size' => 8192,
            'max_tokens' => 2048,
            'capabilities' => [
                'text_generation' => true,
                'reasoning' => true,
                'structured_output' => true,
                'vision' => true,
                'function_calling' => false,
            ],
            'performance' => [
                'avg_latency_ms' => 2000,
                'throughput_tokens_per_sec' => 20,
                'cost_per_1k_input_tokens' => 0.01,
                'cost_per_1k_output_tokens' => 0.01,
            ],
            'status' => 'planning',  // Not yet available
            'tier' => 'advanced',
            'use_cases' => [
                'document_analysis',
                'image_analysis',
                'multimodal_queries',
            ],
        ]);
    }

    /**
     * Register a new model in the registry
     * 
     * @param string $modelName
     * @param array $config
     * @return void
     */
    public function registerModel(string $modelName, array $config): void
    {
        $this->models[$modelName] = $config;
    }

    /**
     * Get model configuration
     * 
     * @param string $modelName
     * @return array|null
     */
    public function getModel(string $modelName): ?array
    {
        return $this->models[$modelName] ?? null;
    }

    /**
     * Get all registered models
     * 
     * @return array
     */
    public function getAllModels(): array
    {
        return $this->models;
    }

    /**
     * Get available models (status = 'available')
     * 
     * @return array
     */
    public function getAvailableModels(): array
    {
        return array_filter($this->models, function ($config) {
            return $config['status'] === 'available';
        });
    }

    /**
     * Get models by capability
     * 
     * @param string $capability
     * @param bool $requireTrue
     * @return array
     */
    public function getModelsByCapability(string $capability, bool $requireTrue = true): array
    {
        $matching = [];

        foreach ($this->models as $name => $config) {
            $hasCapability = $config['capabilities'][$capability] ?? false;
            if ($requireTrue && $hasCapability) {
                $matching[$name] = $config;
            } elseif (!$requireTrue && !$hasCapability) {
                $matching[$name] = $config;
            }
        }

        return $matching;
    }

    /**
     * Get models by tier
     * 
     * @param string $tier ('primary', 'lightweight', 'advanced', etc)
     * @return array
     */
    public function getModelsByTier(string $tier): array
    {
        return array_filter($this->models, function ($config) use ($tier) {
            return $config['tier'] === $tier;
        });
    }

    /**
     * Get fastest model (lowest average latency)
     * 
     * @return array ['name' => string, 'config' => array]
     */
    public function getFastestModel(): array
    {
        $fastest = null;
        $fastestLatency = PHP_INT_MAX;

        foreach ($this->getAvailableModels() as $name => $config) {
            $latency = $config['performance']['avg_latency_ms'] ?? PHP_INT_MAX;
            if ($latency < $fastestLatency) {
                $fastest = ['name' => $name, 'config' => $config];
                $fastestLatency = $latency;
            }
        }

        return $fastest ?? ['name' => 'qwen2.5:3b', 'config' => $this->getModel('qwen2.5:3b')];
    }

    /**
     * Get most capable model
     * 
     * @return array ['name' => string, 'config' => array]
     */
    public function getMostCapableModel(): array
    {
        // Count capabilities
        $scores = [];
        foreach ($this->getAvailableModels() as $name => $config) {
            $scores[$name] = count(array_filter($config['capabilities']));
        }

        $best = array_key_first(array_reverse($scores, true));
        return ['name' => $best, 'config' => $this->getModel($best)];
    }

    /**
     * Check if model is available
     * 
     * @param string $modelName
     * @return bool
     */
    public function isAvailable(string $modelName): bool
    {
        $model = $this->getModel($modelName);
        return $model && $model['status'] === 'available';
    }

    /**
     * Get model status
     * 
     * @param string $modelName
     * @return string ('available', 'planning', 'deprecated', etc)
     */
    public function getStatus(string $modelName): string
    {
        return $this->models[$modelName]['status'] ?? 'unknown';
    }

    /**
     * Log model usage for analytics
     * 
     * @param string $modelName
     * @param string $intent
     * @param int $inputTokens
     * @param int $outputTokens
     * @param int $latencyMs
     * @return void
     */
    public function recordUsage(
        string $modelName,
        string $intent,
        int $inputTokens,
        int $outputTokens,
        int $latencyMs
    ): void {
        Log::info('model_usage', [
            'model' => $modelName,
            'intent' => $intent,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'latency_ms' => $latencyMs,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get estimated cost for a request
     * 
     * @param string $modelName
     * @param int $inputTokens
     * @param int $outputTokens
     * @return float Cost in dollars
     */
    public function estimateCost(string $modelName, int $inputTokens, int $outputTokens): float
    {
        $model = $this->getModel($modelName);
        if (!$model) {
            return 0.0;
        }

        $perf = $model['performance'] ?? [];
        $inputCost = ($inputTokens / 1000) * ($perf['cost_per_1k_input_tokens'] ?? 0);
        $outputCost = ($outputTokens / 1000) * ($perf['cost_per_1k_output_tokens'] ?? 0);

        return $inputCost + $outputCost;
    }
}
