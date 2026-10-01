<?php

namespace App\Events\Commercial;

use App\Models\Commercial\CustomerPurchaseOrderLine;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A PO line has no remaining quantity left to draw (fired once per exhaustion).
 */
class PurchaseOrderLineExhausted implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public CustomerPurchaseOrderLine $line,
    ) {}
}
