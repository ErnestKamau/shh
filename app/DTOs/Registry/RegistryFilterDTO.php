<?php

namespace App\DTOs\Registry;

readonly class RegistryFilterDTO
{
    public function __construct(
        public ?string $status = null,
        public ?string $categoryId = null,
        public ?string $direction = null,
        public ?string $priority = null,
        public ?int $assignedTo = null,
        public ?string $search = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? null,
            categoryId: $data['category_id'] ?? null,
            direction: $data['direction'] ?? null,
            priority: $data['priority'] ?? null,
            assignedTo: isset($data['assigned_to']) ? (int) $data['assigned_to'] : null,
            search: $data['search'] ?? null,
            startDate: $data['start_date'] ?? null,
            endDate: $data['end_date'] ?? null,
        );
    }
}
