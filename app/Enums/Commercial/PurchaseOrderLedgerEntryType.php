<?php

namespace App\Enums\Commercial;

/**
 * Ledger entry types and the balance bucket each one moves.
 *
 * ordered   = Order + Adjust
 * reserved  = Reserve + Unreserve
 * committed = Commit + Release
 * invoiced  = Invoice + Uninvoice   (billing status of committed units, not a second deduction)
 * remaining = ordered − reserved − committed
 */
enum PurchaseOrderLedgerEntryType: string
{
    case Order = 'order';
    case Adjust = 'adjust';
    case Reserve = 'reserve';
    case Unreserve = 'unreserve';
    case Commit = 'commit';
    case Release = 'release';
    case Invoice = 'invoice';
    case Uninvoice = 'uninvoice';

    public function label(): string
    {
        return match ($this) {
            self::Order => 'Ordered',
            self::Adjust => 'Quantity adjusted',
            self::Reserve => 'Reserved',
            self::Unreserve => 'Reservation released',
            self::Commit => 'Committed to job',
            self::Release => 'Released',
            self::Invoice => 'Invoiced',
            self::Uninvoice => 'Invoice reversed',
        };
    }

    public function bucket(): string
    {
        return match ($this) {
            self::Order, self::Adjust => 'ordered',
            self::Reserve, self::Unreserve => 'reserved',
            self::Commit, self::Release => 'committed',
            self::Invoice, self::Uninvoice => 'invoiced',
        };
    }
}
