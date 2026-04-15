<?php

namespace App\Services\LiveData\Support;

/**
 * Registry to manage the operational intent catalog.
 */
class IntentRegistry
{
    /**
     * Get all registered operational intents.
     */
    public function getIntents(): array
    {
        return config('live_data_intents.intents', []);
    }

    /**
     * Get a specific intent definition.
     */
    public function getIntent(string $intent): ?array
    {
        return collect($this->getIntents())->firstWhere('intent', $intent);
    }
}
