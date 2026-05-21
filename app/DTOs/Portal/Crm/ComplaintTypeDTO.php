<?php

namespace App\DTOs\Portal\Crm;

use App\DTOs\Portal\Crm\Concerns\ArrayableDto;

final class ComplaintTypeDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description,
    ) {}
}
