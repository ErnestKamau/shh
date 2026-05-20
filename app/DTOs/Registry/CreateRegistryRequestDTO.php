<?php

namespace App\DTOs\Registry;

readonly class CreateRegistryRequestDTO
{
    public function __construct(
        public string $requestCategoryId,
        public string $subject,
        public ?string $description = null,
        public string $priority = 'normal',
        public string $direction = 'incoming',
        public ?string $submittingParty = null,
        public ?string $entityType = null,
        public ?string $entityId = null,
        public array $metadata = [],
        public ?int $receivedBy = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            requestCategoryId: (string) $data['request_category_id'],
            subject: (string) $data['subject'],
            description: $data['description'] ?? null,
            priority: (string) ($data['priority'] ?? 'normal'),
            direction: (string) ($data['direction'] ?? 'incoming'),
            submittingParty: $data['submitting_party'] ?? null,
            entityType: $data['entity_type'] ?? null,
            entityId: isset($data['entity_id']) ? (string) $data['entity_id'] : null,
            metadata: (array) ($data['metadata'] ?? []),
            receivedBy: isset($data['received_by']) ? (int) $data['received_by'] : null,
        );
    }
}
