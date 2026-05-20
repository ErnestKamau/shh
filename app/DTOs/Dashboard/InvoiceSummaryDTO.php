<?php

namespace App\DTOs\Dashboard;

use App\DTOs\Dashboard\Concerns\ArrayableDto;

final class InvoiceSummaryDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly int $totalInvoices,
        public readonly int $fullyPaidInvoices,
        public readonly int $unpaidInvoices,
        public readonly int $partiallyPaidInvoices,
        public readonly float $totalInvoicedAmount,
        public readonly float $totalPaidAmount,
        public readonly float $outstandingBalance,
    ) {}
}
