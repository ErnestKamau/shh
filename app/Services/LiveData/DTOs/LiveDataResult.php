<?php

namespace App\Services\LiveData\DTOs;

/**
 * Data Transfer Object for LiveData operational results.
 * Ensures consistent structure regardless of the domain handler used.
 */
class LiveDataResult
{
    public function __construct(
        public string $intent,
        public string $reply,
        public mixed $value = null,
        public string $type = 'data',
        public array $metadata = []
    ) {}

    /**
     * Helper to create from array-based legacy returns.
     */
    public static function fromArray(string $intent, array $data): self
    {
        return new self(
            intent: $intent,
            reply: $data['reply'] ?? '',
            value: $data['value'] ?? null,
            type: $data['type'] ?? 'data',
            metadata: $data['metadata'] ?? []
        );
    }
}
