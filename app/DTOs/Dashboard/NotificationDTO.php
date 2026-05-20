<?php

namespace App\DTOs\Dashboard;

use App\DTOs\Dashboard\Concerns\ArrayableDto;

final class NotificationDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $message,
        public readonly string $severity,
        public readonly ?string $createdAt,
        public readonly bool $isRead,
        public readonly ?string $actionUrl,
    ) {}
}
