<?php

namespace App\Exceptions\Commercial;

use RuntimeException;

/**
 * A PO ledger write would break a balance invariant (e.g. negative remaining quantity).
 */
class PurchaseOrderLedgerException extends RuntimeException {}
