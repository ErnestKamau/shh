<?php

namespace Tests\Unit\Billing;

use App\Invoice;
use App\Services\Billing\InvoiceNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvoiceNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_returns_inv0001_when_no_prior_sequential_invoices(): void
    {
        $number = app(InvoiceNumberGenerator::class)->next();

        $this->assertSame('INV0001', $number);
    }

    public function test_next_increments_from_highest_existing_inv_number(): void
    {
        $invoice = new Invoice();
        $invoice->id = (string) Str::uuid();
        $invoice->invoice_number = 'INV0042';
        $invoice->customer_id = (string) Str::uuid();
        $invoice->currency_id = (string) Str::uuid();
        $invoice->pricelist_id = (string) Str::uuid();
        $invoice->save();

        $number = app(InvoiceNumberGenerator::class)->next();

        $this->assertSame('INV0043', $number);
    }

    public function test_parse_sequence_ignores_uuid_style_invoice_numbers(): void
    {
        $this->assertNull(InvoiceNumberGenerator::parseSequence('INV-550e8400-e29b-41d4-a716-446655440000'));
        $this->assertSame(7, InvoiceNumberGenerator::parseSequence('INV-0007'));
        $this->assertSame(7, InvoiceNumberGenerator::parseSequence('INV0007'));
    }
}
