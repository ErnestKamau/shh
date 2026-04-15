<?php

namespace App\Services\LiveData\Detection;

use Illuminate\Support\Facades\Log;
use App\Services\LiveData\Contracts\IntentDetectorInterface;
use App\Services\LiveData\Support\IntentRegistry;

/**
 * Deterministic intent detection for operational queries.
 * Ported from legacy AI service to standalone LiveData service.
 */
class IntentDetector implements IntentDetectorInterface
{
    public function __construct(protected IntentRegistry $registry) {}

    /**
     * Detect intent from a raw message.
     * Layer 1: Deterministic regex matching.
     */
    public function detect(string $message): ?array
    {
        $normalised = mb_strtolower(trim($message));
        $intents = $this->registry->getIntents();

        foreach ($intents as $definition) {
            foreach ($definition['patterns'] as $pattern) {
                if (preg_match('/' . $pattern . '/i', $normalised, $matches)) {
                    Log::debug('LiveData: regex match', [
                        'intent' => $definition['intent'],
                    ]);

                    $entities = [];
                    // Domain-specific entity extraction logic
                    if ($definition['intent'] === 'action_email_sop' && isset($matches[1])) {
                        $entities['document_name'] = trim($matches[1]);
                    }

                    return [
                        'intent'     => $definition['intent'],
                        'confidence' => 1.0,
                        'type'       => $definition['type'] ?? 'data',
                        'source'     => 'regex',
                        'entities'   => $entities,
                    ];
                }
            }
        }

        return null;
    }
}
