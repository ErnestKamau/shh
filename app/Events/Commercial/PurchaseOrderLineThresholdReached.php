<?php

namespace App\Events\Commercial;

use App\Models\Commercial\CustomerPurchaseOrderLine;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A PO line's remaining quantity crossed its low-balance threshold (fired once per crossing).
 */
class PurchaseOrderLineThresholdReached implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public CustomerPurchaseOrderLine $line,
        public int $remainingQty,
    ) {}
}
