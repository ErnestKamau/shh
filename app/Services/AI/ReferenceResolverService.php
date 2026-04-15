<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

/**
 * ReferenceResolverService
 * 
 * Maps ambiguous user references ("the first one", "that sample", "previous result")
 * to actual entities in conversation history.
 * 
 * This enables follow-up questions like:
 * - "Tell me more about the first sample"
 * - "What about that equipment?"
 * - "Compare it with the earlier result"
 */
class ReferenceResolverService
{
    /**
     * Patterns for ordinal references: "the first", "the second", "the last"
     */
    protected const ORDINAL_PATTERNS = [
        '/\b(?:the\s+)?(first|1st|one)\b/i',
        '/\b(?:the\s+)?(second|2nd|two)\b/i',
        '/\b(?:the\s+)?(third|3rd|three)\b/i',
        '/\b(?:the\s+)?(fourth|4th|four)\b/i',
        '/\b(?:the\s+)?(fifth|5th|five)\b/i',
        '/\b(?:the\s+)?(last|final)\b/i',
    ];

    /**
     * Patterns for proximity references: "that", "previous", "earlier", "prior"
     */
    protected const PROXIMITY_PATTERNS = [
        '/\b(?:that|those|this|these)\s*(one|sample|equipment|batch|result|code|item)\b/i',
        '/\b(?:(?:the\s+)?previous|prior|earlier|earlier\s+mentioned)\b/i',
        '/\b(?:aforementioned|aforesaid)\b/i',
    ];

    /**
     * Patterns for anaphoric references: "it", "them", "the same", "the one with"
     */
    protected const ANAPHORIC_PATTERNS = [
        '/\b(?:it|its|itself)\b/i',
        '/\b(?:them|they|their|theirs|themselves)\b/i',
        '/\b(?:the\s+same)\b/i',
    ];

    /**
     * Known entity types in lab context
     */
    protected const ENTITY_TYPES = [
        'sample',
        'batch',
        'equipment',
        'result',
        'value',
        'code',
        'item',
        'test',
        'measurement',
    ];

    /**
     * Resolve references in user input
     * 
     * Returns structured information about detected references and their mappings.
     * 
     * @param string $userInput The user's message
     * @param array $conversationHistory Array of prior messages [['role' => '...', 'content' => '...'], ...]
     * 
     * @return array
     *   {
     *     'input': 'Tell me more about the first sample',
     *     'references': [
     *       {
     *         'phrase': 'the first sample',
     *         'reference_type': 'ordinal',
     *         'ordinal_position': 0,
     *         'entity_type': 'sample',
     *         'matched_entity': 'Sample #S001 with 25mg/L concentration',
     *         'source_message_index': 3,
     *         'confidence': 0.92
     *       }
     *     ],
     *     'resolved_input': 'Tell me more about the first sample (Sample #S001 with 25mg/L concentration)',
     *     'has_unresolved': false
     *   }
     */
    public function resolveReferences(
        string $userInput,
        array $conversationHistory = []
    ): array {
        $result = [
            'input' => $userInput,
            'references' => [],
            'resolved_input' => $userInput,
            'has_unresolved' => false,
        ];

        // Early exit: no history, no references to resolve
        if (empty($conversationHistory)) {
            return $result;
        }

        // Detect reference phrases
        $detectedReferences = $this->detectReferences($userInput);

        if (empty($detectedReferences)) {
            return $result;
        }

        $entities = $this->extractEntitiesFromHistory($conversationHistory);

        // For each detected reference, try to map it to an entity
        foreach ($detectedReferences as $reference) {
            $mapping = $this->mapReferenceToEntity(
                $reference,
                $entities,
                $conversationHistory
            );

            if ($mapping !== null) {
                $result['references'][] = $mapping;
                // Optionally inject mapped context into resolved input
                // (can be used by calling code to inject as system message)
            } else {
                $result['has_unresolved'] = true;
                Log::info('reference_resolver_unresolved', [
                    'phrase' => $reference['phrase'],
                    'reference_type' => $reference['type'],
                ]);
            }
        }

        return $result;
    }

    /**
     * Detect reference phrases in user input
     * 
     * Returns array of detected references with type and position
     * 
     * @param string $userInput
     * @return array
     */
    private function detectReferences(string $userInput): array
    {
        $references = [];

        // Detect ordinal references
        foreach (self::ORDINAL_PATTERNS as $index => $pattern) {
            if (preg_match_all($pattern, $userInput, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $references[] = [
                        'phrase' => $match[0],
                        'type' => 'ordinal',
                        'ordinal_index' => $index,
                        'position' => $match[1],
                    ];
                }
            }
        }

        // Detect proximity references
        foreach (self::PROXIMITY_PATTERNS as $pattern) {
            if (preg_match_all($pattern, $userInput, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $references[] = [
                        'phrase' => $match[0],
                        'type' => 'proximity',
                        'position' => $match[1],
                    ];
                }
            }
        }

        // Detect anaphoric references
        foreach (self::ANAPHORIC_PATTERNS as $pattern) {
            if (preg_match_all($pattern, $userInput, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $references[] = [
                        'phrase' => $match[0],
                        'type' => 'anaphora',
                        'position' => $match[1],
                    ];
                }
            }
        }

        // Remove duplicates and sort by position
        $references = array_unique($references, SORT_REGULAR);
        usort($references, fn($a, $b) => $a['position'] <=> $b['position']);

        return $references;
    }

    /**
     * Extract potential entities from conversation history
     * 
     * Scans messages for mentions of entities (samples, equipment, results, etc.)
     * 
     * @param array $conversationHistory
     * @return array
     */
    private function extractEntitiesFromHistory(array $conversationHistory): array
    {
        $entities = [];

        foreach ($conversationHistory as $index => $message) {
            $content = $message['content'] ?? '';

            // Scan for entity patterns
            foreach (self::ENTITY_TYPES as $entityType) {
                // Match patterns like "Sample #S001", "Equipment E-123", etc.
                $pattern = sprintf(
                    '/\b(?:%s)[\s#:]*([A-Z0-9\-\.]+)/i',
                    $entityType
                );

                if (preg_match_all($pattern, $content, $matches)) {
                    foreach ($matches[1] as $i => $entity) {
                        $entities[] = [
                            'type' => $entityType,
                            'id' => $entity,
                            'full_text' => $matches[0][$i],
                            'source_message_index' => $index,
                            'context' => substr($content, 0, 100),  // First 100 chars
                        ];
                    }
                }
            }

            // Also capture any numeric references (e.g., "the 25mg/L concentration")
            if (preg_match_all('/(\d+[\.\d]*\s*(?:mg|g|ml|L|mL|µg|ppm|%)?)/i', $content, $matches)) {
                foreach ($matches[1] as $value) {
                    $entities[] = [
                        'type' => 'measurement',
                        'value' => $value,
                        'source_message_index' => $index,
                    ];
                }
            }
        }

        return $entities;
    }

    /**
     * Map a detected reference to an entity in history
     * 
     * For ordinal references: "first" → first mentioned entity of that type
     * For proximity references: "that sample" → most recent sample mentioned
     * For anaphoric references: "it" → most recent entity
     * 
     * @param array $reference Detected reference
     * @param array $entities Extracted entities
     * @param array $conversationHistory
     * 
     * @return array|null Mapping with confidence, or null if unmapped
     */
    private function mapReferenceToEntity(
        array $reference,
        array $entities,
        array $conversationHistory
    ): ?array {
        if ($reference['type'] === 'ordinal') {
            return $this->mapOrdinalReference($reference, $entities);
        }

        if ($reference['type'] === 'proximity') {
            return $this->mapProximityReference($reference, $entities);
        }

        if ($reference['type'] === 'anaphora') {
            return $this->mapAnaphoricReference($reference, $entities);
        }

        return null;
    }

    /**
     * Map ordinal reference ("first", "second", "last")
     * 
     * @param array $reference
     * @param array $entities
     * @return array|null
     */
    private function mapOrdinalReference(array $reference, array $entities): ?array
    {
        $ordinalIndex = $reference['ordinal_index'] ?? 0;

        // Map pattern index to position
        $positionMap = [
            0 => 0,    // first
            1 => 1,    // second
            2 => 2,    // third
            3 => 3,    // fourth
            4 => 4,    // fifth
            5 => -1,   // last (special case)
        ];

        $position = $positionMap[$ordinalIndex] ?? null;

        if ($position === null) {
            return null;
        }

        // Get entity at position
        $entity = null;

        if ($position === -1 && !empty($entities)) {
            // "Last" = last entity
            $entity = end($entities);
        } elseif ($position >= 0 && isset($entities[$position])) {
            $entity = $entities[$position];
        }

        if ($entity === null) {
            return null;
        }

        return [
            'phrase' => $reference['phrase'],
            'reference_type' => 'ordinal',
            'entity_type' => $entity['type'] ?? 'unknown',
            'entity_id' => $entity['id'] ?? $entity['value'] ?? null,
            'context' => $entity['full_text'] ?? $entity['value'] ?? null,
            'source_message_index' => $entity['source_message_index'] ?? null,
            'confidence' => 0.85,  // Ordinals are pretty reliable
        ];
    }

    /**
     * Map proximity reference ("that sample", "previous result")
     * 
     * @param array $reference
     * @param array $entities
     * @return array|null
     */
    private function mapProximityReference(array $reference, array $entities): ?array
    {
        if (empty($entities)) {
            return null;
        }

        // Extract entity type from phrase, if mentioned
        $phrase = strtolower($reference['phrase']);
        $detectedType = null;

        foreach (self::ENTITY_TYPES as $type) {
            if (str_contains($phrase, strtolower($type))) {
                $detectedType = $type;
                break;
            }
        }

        // Find most recent entity of that type (or any if not specified)
        $candidate = null;

        if ($detectedType) {
            // Find most recent of this type
            for ($i = count($entities) - 1; $i >= 0; $i--) {
                if (($entities[$i]['type'] ?? null) === $detectedType) {
                    $candidate = $entities[$i];
                    break;
                }
            }
        } else {
            // No type specified; use most recent entity
            $candidate = end($entities);
        }

        if ($candidate === null) {
            return null;
        }

        return [
            'phrase' => $reference['phrase'],
            'reference_type' => 'proximity',
            'entity_type' => $candidate['type'] ?? 'unknown',
            'entity_id' => $candidate['id'] ?? $candidate['value'] ?? null,
            'context' => $candidate['full_text'] ?? $candidate['value'] ?? null,
            'source_message_index' => $candidate['source_message_index'] ?? null,
            'confidence' => 0.78,  // Proximity is less certain
        ];
    }

    /**
     * Map anaphoric reference ("it", "them", "the same")
     * 
     * For now, these are low-confidence mappings.
     * In practice, contextual understanding would be needed.
     * 
     * @param array $reference
     * @param array $entities
     * @return array|null
     */
    private function mapAnaphoricReference(array $reference, array $entities): ?array
    {
        if (empty($entities)) {
            return null;
        }

        // Anaphoric references usually refer to most recent entity
        $candidate = end($entities);

        // Lower confidence for anaphoric references
        return [
            'phrase' => $reference['phrase'],
            'reference_type' => 'anaphora',
            'entity_type' => $candidate['type'] ?? 'unknown',
            'entity_id' => $candidate['id'] ?? $candidate['value'] ?? null,
            'context' => $candidate['full_text'] ?? $candidate['value'] ?? null,
            'source_message_index' => $candidate['source_message_index'] ?? null,
            'confidence' => 0.65,  // Anaphora is low-confidence
        ];
    }
}
