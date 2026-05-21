<?php

namespace App\DTOs\Portal\Crm;

use App\DTOs\Portal\Crm\Concerns\ArrayableDto;

final class InvoiceListItemDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly string $id,
        public readonly string $invoiceNumber,
        public readonly ?string $referenceNumber,
        public readonly ?string $currencyCode,
        public readonly ?string $invoiceDate,
        public readonly ?string $dueDate,
        public readonly float $totalAmount,
        public readonly float $taxAmount,
        public readonly float $paidAmount,
        public readonly float $outstandingAmount,
        public readonly string $paymentStatus,
    ) {}
}
