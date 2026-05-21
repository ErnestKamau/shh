<?php

namespace App\DTOs\Portal\Crm;

use App\DTOs\Portal\Crm\Concerns\ArrayableDto;

final class InvoicePaymentDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly string $id,
        public readonly ?float $amount,
        public readonly ?string $paymentMethod,
        public readonly ?string $refNo,
        public readonly ?string $transactionNo,
        public readonly ?string $createdAt,
    ) {}
}
