<?php

namespace App\DTOs\Portal\Crm;

use App\DTOs\Portal\Crm\Concerns\ArrayableDto;

final class InvoiceDetailDTO
{
    use ArrayableDto;

    /**
     * @param  list<InvoiceLineItemDTO>  $lineItems
     * @param  list<InvoicePaymentDTO>  $payments
     */
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
        public readonly array $lineItems,
        public readonly array $payments,
    ) {}
}
