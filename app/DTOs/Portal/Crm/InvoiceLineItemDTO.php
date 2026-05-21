<?php

namespace App\DTOs\Portal\Crm;

use App\DTOs\Portal\Crm\Concerns\ArrayableDto;

final class InvoiceLineItemDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly string $id,
        public readonly ?string $description,
        public readonly ?float $quantity,
        public readonly ?float $unitPrice,
        public readonly ?float $total,
    ) {}
}
