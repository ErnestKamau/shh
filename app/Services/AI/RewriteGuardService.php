<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * RewriteGuardService
 * 
 * Safety layer for rewrite/edit operations on assistant responses
 * 
 * When user wants to rewrite (locally modify) an assistant response,
 * this service validates the rewrite is semantically safe:
 * 1. Not changing established facts from earlier in conversation
 * 2. Not introducing contradictions
 * 3. Staying within reasonable deviation bounds (30% max)
 * 4. Respecting domain-specific safety constraints
 * 
 * Presets:
 * - 'tone': Adjust tone/style (confidence: high)
 * - 'clarity': Improve clarity/structure (confidence: high)
 * - 'domain_filter': Remove domain-sensitive content (confidence: medium)
 * - 'safety_filter': General safety constraints (confidence: low)
 */
class RewriteGuardService
{
    protected const PRESET_CONFIGS = [
        'tone' => [
            'category' => 'stylistic',
            'max_deviation' => 0.15,    // 15% max deviation
            'safe_edits' => ['vocabulary', 'sentence_structure', 'formality'],
            'restricted_edits' => ['facts', 'numbers', 'opinions'],
            'confidence' => 'high',
        ],
        'clarity' => [
            'category' => 'structural',
            'max_deviation' => 0.20,    // 20% max deviation
            'safe_edits' => ['organization', 'examples', 'explanations'],
            'restricted_edits' => ['facts', 'conclusions'],
            'confidence' => 'high',
        ],
        'domain_filter' => [
            'category' => 'domain',
            'max_deviation' => 0.25,    // 25% max deviation
            'safe_edits' => ['removal', 'redaction', 'simplification'],
            'restricted_edits' => ['addition', 'reinterpretation'],
            'confidence' => 'medium',
        ],
        'safety_filter' => [
            'category' => 'safety',
            'max_deviation' => 0.30,    // 30% max deviation
            'safe_edits' => ['removal', 'softening', 'disclaimers'],
            'restricted_edits' => ['contradiction', 'inversion'],
            'confidence' => 'low',
        ],
    ];

    /**
     * Validate a proposed rewrite against safety constraints
     * 
     * @param string $originalText The original assistant response
     * @param string $rewrittenText The proposed rewrite
     * @param string $preset The preset profile ('tone', 'clarity', etc)
     * @param array $context Optional context {
     *     'conversation_history': array,
     *     'domain_constraints': array,
     *     'fact_registry': array,
     * }
     * @return array {
     *     'is_safe': bool,
     *     'confidence': string,           // 'high', 'medium', 'low'
     *     'deviation_percent': float,     // 0-100
     *     'safety_checks': {
     *         'semantic_consistency': bool,
     *         'fact_preservation': bool,
     *         'contradiction_check': bool,
     *         'deviation_within_bounds': bool,
     *     },
     *     'warnings': string[],
     *     'suggestion': string|null,
     * }
     */
    public function validateRewrite(
        string $originalText,
        string $rewrittenText,
        string $preset = 'clarity',
        array $context = []
    ): array {
        if (!isset(self::PRESET_CONFIGS[$preset])) {
            $preset = 'clarity'; // fallback
        }

        $config = self::PRESET_CONFIGS[$preset];
        $warnings = [];
        $checks = [
            'semantic_consistency' => true,
            'fact_preservation' => true,
            'contradiction_check' => true,
            'deviation_within_bounds' => true,
        ];

        // Check 1: Semantic consistency (word overlap)
        $semanticScore = $this->calculateSemanticSimilarity($originalText, $rewrittenText);
        if ($semanticScore < 0.4) {
            $checks['semantic_consistency'] = false;
            $warnings[] = "Low semantic similarity ({$semanticScore}): rewrite changes core meaning";
        }

        // Check 2: Fact preservation
        $factsPreserved = $this->checkFactPreservation($originalText, $rewrittenText, $context);
        if (!$factsPreserved['preserved']) {
            $checks['fact_preservation'] = false;
            $warnings[] = "Facts were altered: " . implode(', ', $factsPreserved['altered_facts']);
        }

        // Check 3: Contradiction check
        $contradictions = $this->checkForContradictions(
            $originalText,
            $rewrittenText,
            $context['conversation_history'] ?? []
        );
        if (!empty($contradictions)) {
            $checks['contradiction_check'] = false;
            $warnings[] = "Contradictions detected: " . implode(', ', $contradictions);
        }

        // Check 4: Deviation within bounds
        $deviationPercent = (1 - $semanticScore) * 100;
        $maxDeviation = $config['max_deviation'] * 100;
        $checks['deviation_within_bounds'] = $deviationPercent <= $maxDeviation;

        if (!$checks['deviation_within_bounds']) {
            $warnings[] = "Deviation {$deviationPercent}% exceeds max {$maxDeviation}%";
        }

        // Determine safety
        $passedChecks = array_sum(array_values($checks));
        $isSafe = $passedChecks >= 3; // At least 3 of 4 checks must pass

        // Build suggestion if unsafe
        $suggestion = null;
        if (!$isSafe) {
            $suggestion = $this->buildSuggestion($originalText, $rewrittenText, $preset, $warnings);
        }

        Log::info('rewrite_validation', [
            'preset' => $preset,
            'is_safe' => $isSafe,
            'deviation_percent' => round($deviationPercent, 1),
            'checks_passed' => $passedChecks,
            'warnings_count' => count($warnings),
        ]);

        return [
            'is_safe' => $isSafe,
            'confidence' => $config['confidence'],
            'deviation_percent' => round($deviationPercent, 1),
            'safety_checks' => $checks,
            'warnings' => $warnings,
            'suggestion' => $suggestion,
        ];
    }

    /**
     * Calculate semantic similarity using basic TF-IDF-like approach
     * 
     * In production, use sentence transformers or embeddings API
     * 
     * @param string $text1
     * @param string $text2
     * @return float 0-1 similarity score
     */
    protected function calculateSemanticSimilarity(string $text1, string $text2): float
    {
        // Simple word overlap heuristic
        $words1 = $this->getKeywords($text1);
        $words2 = $this->getKeywords($text2);

        if (empty($words1) || empty($words2)) {
            return 0.0;
        }

        $overlap = count(array_intersect($words1, $words2));
        $union = count(array_unique(array_merge($words1, $words2)));

        return $union > 0 ? $overlap / $union : 0.0;
    }

    /**
     * Extract keywords from text
     * 
     * Removes common words, keeps nouns/verbs/adjectives
     * 
     * @param string $text
     * @return array
     */
    protected function getKeywords(string $text): array
    {
        $commonWords = [
            'the', 'a', 'an', 'and', 'or', 'but', 'is', 'are', 'was', 'were',
            'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did',
            'will', 'would', 'should', 'could', 'may', 'might', 'must', 'can',
            'to', 'of', 'in', 'on', 'at', 'by', 'for', 'with', 'from', 'as',
            'if', 'that', 'which', 'who', 'what', 'where', 'when', 'why', 'how',
            'this', 'that', 'these', 'those', 'it', 'its', 'they', 'them', 'their',
            'i', 'you', 'he', 'she', 'we', 'me', 'him', 'her', 'us',
        ];

        // Simple lowercase + split approach
        $words = preg_split('/[^a-z0-9]+/i', strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
        return array_filter($words, fn($w) => !in_array($w, $commonWords) && strlen($w) > 2);
    }

    /**
     * Check if facts were altered in rewrite
     * 
     * @param string $original
     * @param string $rewritten
     * @param array $context
     * @return array {preserved: bool, altered_facts: array}
     */
    protected function checkFactPreservation(string $original, string $rewritten, array $context): array
    {
        // Extract numerical facts from original
        $originalNumbers = $this->extractNumbers($original);
        $rewrittenNumbers = $this->extractNumbers($rewritten);

        $alteredFacts = [];
        foreach ($originalNumbers as $fact) {
            if (!in_array($fact, $rewrittenNumbers)) {
                $alteredFacts[] = "number '{$fact}'";
            }
        }

        // Check for factual assertions
        $originalAssertions = $this->extractAssertions($original);
        $rewrittenAssertions = $this->extractAssertions($rewritten);

        $missingAssertions = array_diff($originalAssertions, $rewrittenAssertions);
        foreach ($missingAssertions as $assertion) {
            if (strlen($assertion) > 10) { // Skip trivial assertions
                $alteredFacts[] = "assertion removed";
            }
        }

        return [
            'preserved' => empty($alteredFacts),
            'altered_facts' => $alteredFacts,
        ];
    }

    /**
     * Extract numbers from text
     * 
     * @param string $text
     * @return array
     */
    protected function extractNumbers(string $text): array
    {
        preg_match_all('/\d+(?:\.\d+)?/', $text, $matches);
        return $matches[0] ?? [];
    }

    /**
     * Extract factual assertions (sentences with verbs and objects)
     * 
     * Very naive: just split by sentence
     * 
     * @param string $text
     * @return array
     */
    protected function extractAssertions(string $text): array
    {
        $sentences = preg_split('/[\.\!\?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        return array_filter(
            array_map('trim', $sentences),
            fn($s) => strlen($s) > 10 && preg_match('/[a-z]\s+(is|are|was|were|has|have|can|will)/i', $s)
        );
    }

    /**
     * Check for contradictions with conversation history
     * 
     * @param string $original
     * @param string $rewritten
     * @param array $history Previous messages
     * @return array Contradiction descriptions
     */
    protected function checkForContradictions(string $original, string $rewritten, array $history): array
    {
        $contradictions = [];

        // Extract assertions from rewritten text
        $newAssertions = $this->extractAssertions($rewritten);

        // Check against historical assertions
        foreach ($history as $historicalMessage) {
            if (!is_array($historicalMessage) && !is_object($historicalMessage)) {
                continue;
            }

            $historicalContent = $historicalMessage['content'] ?? $historicalMessage->content ?? '';
            $historicalAssertions = $this->extractAssertions($historicalContent);

            // Very naive contradiction detection: if assertion contains negation opposite to history
            foreach ($newAssertions as $assertion) {
                foreach ($historicalAssertions as $historical) {
                    if ($this->detectNegationConflict($assertion, $historical)) {
                        $contradictions[] = "Conflicts with earlier statement";
                    }
                }
            }
        }

        return array_unique($contradictions);
    }

    /**
     * Detect if two assertions conflict (one negates the other)
     * 
     * @param string $assertion1
     * @param string $assertion2
     * @return bool
     */
    protected function detectNegationConflict(string $assertion1, string $assertion2): bool
    {
        // Check if one has "not" or "cannot" and the other doesn't
        $hasNegation1 = preg_match('/\b(not|cannot|does not|do not|did not|is not|are not)\b/i', $assertion1);
        $hasNegation2 = preg_match('/\b(not|cannot|does not|do not|did not|is not|are not)\b/i', $assertion2);

        // Only a conflict if opposite negations and high word overlap
        if ($hasNegation1 === $hasNegation2) {
            return false; // Both negated or both positive - not a conflict
        }

        $similarity = $this->calculateSemanticSimilarity($assertion1, $assertion2);
        return $similarity > 0.6; // High overlap but opposite negation = conflict
    }

    /**
     * Build a suggestion for how to fix unsafe rewrites
     * 
     * @param string $original
     * @param string $rewritten
     * @param string $preset
     * @param array $warnings
     * @return string
     */
    protected function buildSuggestion(
        string $original,
        string $rewritten,
        string $preset,
        array $warnings
    ): string {
        $warningText = implode('|', $warnings);
        
        if (strpos($warningText, 'Facts were altered') !== false) {
            return "Consider restoring factual assertions from the original.";
        }

        if (strpos($warningText, 'Contradictions detected') !== false) {
            return "The rewrite contradicts earlier statements. Review history and align your edit.";
        }

        if (strpos($warningText, 'Deviation') !== false) {
            return "The rewrite is too different from the original. Try smaller, focused edits.";
        }

        if ($preset === 'tone') {
            return "For tone changes, focus on vocabulary and phrasing while keeping facts intact.";
        }

        return "Consider reverting to a more conservative edit that preserves core meaning.";
    }

    /**
     * Get preset configuration
     * 
     * @param string $preset
     * @return array|null
     */
    public function getPresetConfig(string $preset): ?array
    {
        return self::PRESET_CONFIGS[$preset] ?? null;
    }

    /**
     * List all available presets
     * 
     * @return array
     */
    public function listPresets(): array
    {
        return array_keys(self::PRESET_CONFIGS);
    }
}
