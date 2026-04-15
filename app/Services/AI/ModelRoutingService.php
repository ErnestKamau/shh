<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

/**
 * ModelRoutingService
 * 
 * Deterministic rule-based model routing engine.
 * Routes to appropriate models based on:
 * - Detected intent
 * - Query complexity
 * - Required capabilities
 * - Performance constraints
 * - Cost optimization
 * 
 * NO LLM-based routing decision - all logic is explicit, auditable, and maintainable.
 */
class ModelRoutingService
{
    protected ModelRegistry $registry;
    protected RoutingConfidenceCalculator $confidenceCalculator;

    /**
     * Routing rules by intent
     * Format: intent => [primary_model, secondary_model, fallback_model]
     */
    protected array $intentRules = [];

    public function __construct(
        ModelRegistry $registry,
        RoutingConfidenceCalculator $confidenceCalculator
    ) {
        $this->registry = $registry;
        $this->confidenceCalculator = $confidenceCalculator;
        $this->initializeRoutingRules();
    }

    /**
     * Resolve a model name from a tier or capability, falling back to a safe default.
     */
    protected function resolveModel(string $tier, string $intent = 'general'): string
    {
        $models = $this->registry->getModelsByTier($tier);
        $available = array_filter($models, fn($m) => $m['status'] === 'available');

        if (!empty($available)) {
            return array_key_first($available);
        }

        // Fallback to fastest available if primary/requested tier is missing
        $fastest = $this->registry->getFastestModel();
        return $fastest['name'] ?? 'qwen2.5:3b';
    }

    /**
     * Initialize routing rules
     * These are defined by tier requirements rather than hardcoded versions
     */
    protected function initializeRoutingRules(): void
    {
        $primary = $this->resolveModel('primary');
        $lightweight = $this->resolveModel('lightweight');
        $advanced = $this->resolveModel('advanced');

        // Rule set 1: General conversation
        $this->intentRules['general_conversation'] = [
            'primary' => $primary,
            'secondary' => $lightweight,
            'fallback' => $primary,
            'weights' => ['accuracy' => 0.7, 'speed' => 0.3],
        ];

        // Rule set 2: RAG synthesis (requires reasoning + context)
        $this->intentRules['rag_synthesis'] = [
            'primary' => $primary,
            'secondary' => $primary,
            'fallback' => $primary,
            'weights' => ['accuracy' => 0.9, 'speed' => 0.1],
        ];

        // Rule set 3: Live data queries
        $this->intentRules['live_data_query'] = [
            'primary' => $primary,
            'secondary' => $lightweight,
            'fallback' => $primary,
            'weights' => ['accuracy' => 0.85, 'speed' => 0.15],
        ];

        // Rule set 4: Intent detection itself
        $this->intentRules['intent_detection'] = [
            'primary' => $lightweight,
            'secondary' => $primary,
            'fallback' => $primary,
            'weights' => ['accuracy' => 0.8, 'speed' => 0.2],
        ];

        // Rule set 5: Classification
        $this->intentRules['classification'] = [
            'primary' => $lightweight,
            'secondary' => $primary,
            'fallback' => $primary,
            'weights' => ['accuracy' => 0.85, 'speed' => 0.15],
        ];

        // Rule set 6: Vision tasks
        $this->intentRules['vision_analysis'] = [
            'primary' => $advanced !== $primary ? $advanced : 'vision-model-future',
            'secondary' => $primary,
            'fallback' => $primary,
            'weights' => ['accuracy' => 0.95, 'speed' => 0.05],
        ];
    }

    /**
     * Route a request to appropriate model
     * 
     * @param string $intent The detected request intent
     * @param array $context Request context and constraints
     * @param array $options Routing options (force_model, debug_mode, etc)
     * @return array {
     *     'model': string model name,
     *     'reason': string explanation,
     *     'confidence': 0-1 float,
     *     'alternative_models': [string],
     *     'cost_estimate': float,
     *     'debug_info': array (if debug enabled)
     * }
     */
    public function route(
        string $intent,
        array $context = [],
        array $options = []
    ): array {
        $debugMode = $options['debug_mode'] ?? config('ai.routing_debug_mode', false);
        $forceModel = $options['force_model'] ?? null;

        // If user explicitly forces a model, use it (administrative override)
        if ($forceModel && $this->registry->isAvailable($forceModel)) {
            Log::info('routing_forced', [
                'intent' => $intent,
                'forced_model' => $forceModel,
            ]);

            return [
                'model' => $forceModel,
                'reason' => "Administratively forced to {$forceModel}",
                'confidence' => 1.0,
                'alternative_models' => [],
                'cost_estimate' => $this->estimateCost($forceModel, $context),
                'debug_info' => $debugMode ? ['override' => 'admin_force'] : null,
            ];
        }

        // Get routing rules for this intent
        $rules = $this->intentRules[$intent] ?? $this->getDefaultRules();

        // Get available models in priority order
        $candidates = [
            'primary' => $rules['primary'],
            'secondary' => $rules['secondary'],
            'fallback' => $rules['fallback'],
        ];

        // Filter to only available models
        $available = array_filter(
            $candidates,
            fn($model) => $this->registry->isAvailable($model)
        );

        if (empty($available)) {
            Log::error('no_available_models', [
                'intent' => $intent,
                'candidates' => $candidates,
            ]);
            $model = 'qwen2.5:3b';  # Last resort fallback
        } else {
            // Pick best available candidate
            $model = reset($available);
        }

        // Calculate confidence score
        $confidenceResult = $this->confidenceCalculator->calculate(
            $intent,
            $model,
            $context
        );

        // Prepare alternative models
        $alternatives = array_filter(array_diff($available, [$model]));

        // Estimate cost
        $costEstimate = $this->estimateCost($model, $context);

        // Log routing decision
        Log::info('routing_decision', [
            'intent' => $intent,
            'chosen_model' => $model,
            'confidence' => $confidenceResult['confidence'],
            'recommendation' => $confidenceResult['recommendation'],
        ]);

        $result = [
            'model' => $model,
            'reason' => $confidenceResult['reasoning'],
            'confidence' => $confidenceResult['confidence'],
            'recommendation' => $confidenceResult['recommendation'],
            'alternative_models' => array_values($alternatives),
            'cost_estimate' => $costEstimate,
        ];

        if ($debugMode) {
            $result['debug_info'] = [
                'intent' => $intent,
                'context' => $context,
                'rules' => $rules,
                'confidence_factors' => $confidenceResult['factors'],
                'candidates_available' => array_values($available),
            ];
        }

        return $result;
    }

    /**
     * Get default routing rules (fallback for unknown intents)
     * 
     * @return array
     */
    protected function getDefaultRules(): array
    {
        return [
            'primary' => 'qwen2.5:3b',
            'secondary' => 'qwen3.5:0.8b',
            'fallback' => 'qwen2.5:3b',
            'weights' => [
                'accuracy' => 0.7,
                'speed' => 0.3,
            ],
        ];
    }

    /**
     * Estimate operational cost for this routing decision
     * 
     * @param string $modelName
     * @param array $context
     * @return float Estimated cost in dollars
     */
    protected function estimateCost(string $modelName, array $context): float
    {
        $inputTokens = $context['estimated_input_tokens'] ?? 100;
        $outputTokens = $context['estimated_output_tokens'] ?? 100;

        return $this->registry->estimateCost($modelName, $inputTokens, $outputTokens);
    }

    /**
     * Validate that a routing decision is reasonable
     * 
     * Returns false if confidence is too low or requirements unmet
     * 
     * @param string $intent
     * @param string $modelName
     * @param array $context
     * @return array {
     *     'valid': bool,
     *     'issues': [string],
     *     'warnings': [string]
     * }
     */
    public function validateRouting(
        string $intent,
        string $modelName,
        array $context = []
    ): array {
        $issues = [];
        $warnings = [];

        // Check model exists and is available
        if (!$this->registry->isAvailable($modelName)) {
            $issues[] = "Model {$modelName} is not available";
        }

        // Check confidence is acceptable
        $result = $this->confidenceCalculator->calculate($intent, $modelName, $context);
        if ($result['confidence'] < 0.60) {
            $warnings[] = "Low confidence score: {$result['confidence']}";
        }

        // Check context requirements
        $model = $this->registry->getModel($modelName);
        if ($context['requires_vision'] ?? false) {
            if (!($model['capabilities']['vision'] ?? false)) {
                $issues[] = "Vision required but model doesn't support it";
            }
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'warnings' => $warnings,
        ];
    }

    /**
     * Get routing statistics (for dashboard/analytics)
     * 
     * @return array
     */
    public function getStats(): array
    {
        $totalIntentRules = count($this->intentRules);
        $availableModels = count($this->registry->getAvailableModels());
        $totalModels = count($this->registry->getAllModels());

        return [
            'intent_rules_defined' => $totalIntentRules,
            'available_models' => $availableModels,
            'total_models' => $totalModels,
            'registry_coverage' => round(($availableModels / $totalModels) * 100, 1) . '%',
            'primary_model' => 'qwen2.5:3b',
            'fallback_model' => 'qwen2.5:3b',
        ];
    }
}
