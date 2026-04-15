<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

/**
 * RoutingConfidenceCalculator
 * 
 * Calculates confidence scores (0-1) for routing decisions.
 * 
 * Factors considered:
 * - Intent match strength (ordinal: 0.95, fuzzy: 0.60-0.85)
 * - Context requirements (RAG needed?, Vision needed?)
 * - Historical routing success for intent
 * - Model capability coverage
 * - Time-of-day patterns
 */
class RoutingConfidenceCalculator
{
    /**
     * Historical routing success rates by intent
     * In production, this would come from database analytics
     */
    protected array $historicalSuccessRates = [
        'general_conversation' => 0.98,
        'rag_synthesis' => 0.92,
        'live_data_query' => 0.88,
        'intent_detection' => 0.95,
        'classification' => 0.96,
    ];

    /**
     * Model capability weights (how important is each capability?)
     */
    protected array $capabilityWeights = [
        'text_generation' => 1.0,     // Always needed
        'reasoning' => 0.6,            // Often helpful but not always
        'structured_output' => 0.4,    // Useful for specific tasks
        'vision' => 0.8,               // Critical for vision tasks
        'function_calling' => 0.3,     // Rarely used
    ];

    /**
     * Calculate confidence for a routing decision
     * 
     * @param string $intent The detected intent ('rag_synthesis', 'live_data_query', etc)
     * @param string $modelName The proposed model
     * @param array $context Additional context (question length, requires_vision, etc)
     * @param array $historicalData Model performance data (optional)
     * @return array {
     *     'confidence': 0-1 float,
     *     'factors': {
     *         'intent_match': 0.95,
     *         'model_capability': 0.87,
     *         'historical_success': 0.92,
     *         'context_fit': 0.85
     *     },
     *     'recommendation': 'high' | 'medium' | 'low',
     *     'reasoning': string explanation
     * }
     */
    public function calculate(
        string $intent,
        string $modelName,
        array $context = [],
        array $historicalData = []
    ): array {
        $factors = [];

        // Factor 1: Intent-Model match
        $factors['intent_match'] = $this->calculateIntentMatch($intent, $modelName);

        // Factor 2: Model capability coverage
        $factors['model_capability'] = $this->calculateCapabilityMatch($modelName, $context);

        // Factor 3: Historical success rate
        $factors['historical_success'] = $this->getHistoricalSuccessRate($intent, $modelName);

        // Factor 4: Context fit (question complexity, time, etc)
        $factors['context_fit'] = $this->calculateContextFit($context, $modelName);

        // Calculate weighted average confidence
        $confidence = $this->weightedAverage($factors);

        // Determine recommendation level
        $recommendation = $this->getRecommendationLevel($confidence);

        // Generate reasoning text
        $reasoning = $this->generateReasoning($intent, $modelName, $factors);

        Log::info('routing_confidence_calculated', [
            'intent' => $intent,
            'model' => $modelName,
            'confidence' => round($confidence, 3),
            'recommendation' => $recommendation,
        ]);

        return [
            'confidence' => round($confidence, 3),
            'factors' => array_map(fn($v) => round($v, 3), $factors),
            'recommendation' => $recommendation,
            'reasoning' => $reasoning,
        ];
    }

    /**
     * Calculate how well intent matches model strengths
     * 
     * @param string $intent
     * @param string $modelName
     * @return float 0-1
     */
    protected function calculateIntentMatch(string $intent, string $modelName): float
    {
        // Intent-specific routing rules
        $matches = [
            'general_conversation' => ['qwen2.5:3b' => 0.95, 'qwen3.5:0.8b' => 0.87],
            'rag_synthesis' => ['qwen2.5:3b' => 0.94, 'qwen3.5:0.8b' => 0.70],
            'live_data_query' => ['qwen2.5:3b' => 0.91, 'qwen3.5:0.8b' => 0.75],
            'intent_detection' => ['qwen2.5:3b' => 0.96, 'qwen3.5:0.8b' => 0.91],
            'classification' => ['qwen3.5:0.8b' => 0.97, 'qwen2.5:3b' => 0.93],
        ];

        return $matches[$intent][$modelName] ?? 0.75;
    }

    /**
     * Calculate how well model capabilities match context requirements
     * 
     * @param string $modelName
     * @param array $context
     * @return float 0-1
     */
    protected function calculateCapabilityMatch(string $modelName, array $context): float
    {
        $registry = app(ModelRegistry::class);
        $model = $registry->getModel($modelName);

        if (!$model) {
            return 0.5;
        }

        $requiredCapabilities = [];

        // Detect required capabilities from context
        if ($context['requires_vision'] ?? false) {
            $requiredCapabilities['vision'] = 1.0;
        }
        if ($context['requires_structured'] ?? false) {
            $requiredCapabilities['structured_output'] = 0.8;
        }
        if ($context['requires_reasoning'] ?? false) {
            $requiredCapabilities['reasoning'] = 0.9;
        }

        // If no specific requirements, general capability is good
        if (empty($requiredCapabilities)) {
            $requiredCapabilities['text_generation'] = 0.5;
        }

        $score = 0;
        $weight = 0;

        foreach ($requiredCapabilities as $capability => $importance) {
            $hasIt = $model['capabilities'][$capability] ?? false;
            if ($hasIt) {
                $score += $importance;
            } else {
                $score -= $importance * 0.5;  // Penalty for missing capability
            }
            $weight += $importance;
        }

        return max(0, min(1, $score / $weight));
    }

    /**
     * Get historical success rate for intent-model combination
     * 
     * @param string $intent
     * @param string $modelName
     * @return float 0-1
     */
    protected function getHistoricalSuccessRate(string $intent, string $modelName): float
    {
        // In production, query analytics table
        // For now, use base rate + small model adjustment
        $baseRate = $this->historicalSuccessRates[$intent] ?? 0.90;
        
        // Adjust for model tier
        $registry = app(ModelRegistry::class);
        $model = $registry->getModel($modelName);
        
        if ($model && $model['tier'] === 'advanced') {
            return min(1.0, $baseRate + 0.05);
        }

        return $baseRate;
    }

    /**
     * Calculate context fit (question complexity, token expectations, etc)
     * 
     * @param array $context
     * @param string $modelName
     * @return float 0-1
     */
    protected function calculateContextFit(array $context, string $modelName): float
    {
        $registry = app(ModelRegistry::class);
        $model = $registry->getModel($modelName);

        if (!$model) {
            return 0.7;
        }

        $score = 0.8;  // Start neutral

        // Check expected output length
        $expectedOutputTokens = $context['expected_output_tokens'] ?? 100;
        if ($expectedOutputTokens > $model['max_tokens']) {
            // Model can't handle expected output
            $score -= 0.3;
        }

        // Check context requirements
        $estimatedContextTokens = $context['estimated_context_tokens'] ?? 100;
        if ($estimatedContextTokens > $model['context_size'] * 0.8) {
            // Model memory almost full
            $score -= 0.2;
        }

        // Check latency expectations
        $maxLatencyMs = $context['max_latency_ms'] ?? 5000;
        $modelLatency = $model['performance']['avg_latency_ms'] ?? 800;
        if ($modelLatency > $maxLatencyMs) {
            $score -= 0.15;
        }

        return max(0.3, min(1.0, $score));
    }

    /**
     * Calculate weighted average of factors
     * 
     * Equal weighting by default, can be tuned
     * 
     * @param array $factors
     * @return float
     */
    protected function weightedAverage(array $factors): float
    {
        $weights = [
            'intent_match' => 0.40,        // Strongest signal
            'model_capability' => 0.25,    // Important
            'historical_success' => 0.20,  // Proven track record
            'context_fit' => 0.15,         // Situational
        ];

        $sum = 0;
        $weightSum = 0;

        foreach ($factors as $name => $value) {
            $weight = $weights[$name] ?? 0.25;
            $sum += $value * $weight;
            $weightSum += $weight;
        }

        return $weightSum > 0 ? $sum / $weightSum : 0.5;
    }

    /**
     * Categorize confidence level
     * 
     * @param float $confidence
     * @return string 'high' | 'medium' | 'low'
     */
    protected function getRecommendationLevel(float $confidence): string
    {
        if ($confidence >= 0.85) {
            return 'high';
        } elseif ($confidence >= 0.70) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Generate human-readable explanation of routing decision
     * 
     * @param string $intent
     * @param string $modelName
     * @param array $factors
     * @return string
     */
    protected function generateReasoning(string $intent, string $modelName, array $factors): string
    {
        $strongest = array_key_first(
            array_filter($factors, fn($v) => $v >= 0.90)
        ) ?? 'intent_match';

        $reason = "Route {$intent} to {$modelName}: ";

        if ($strongest === 'intent_match') {
            $reason .= "Strong intent-model match ({$factors['intent_match']}).";
        } elseif ($strongest === 'historical_success') {
            $reason .= "High historical success rate for this route ({$factors['historical_success']}).";
        } elseif ($strongest === 'model_capability') {
            $reason .= "Model has required capabilities ({$factors['model_capability']}).";
        } else {
            $reason .= "Good overall fit for context.";
        }

        // Add warnings if any factor is weak
        $weakFactors = array_filter($factors, fn($v) => $v < 0.70);
        if (!empty($weakFactors)) {
            $weak = array_keys($weakFactors);
            $reason .= " Warning: " . implode(', ', $weak) . " score(s) below 0.70.";
        }

        return $reason;
    }
}
