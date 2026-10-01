<?php

namespace Tests\Feature\Commercial;

use App\DTOs\Commercial\PurchaseOrderDemandItem;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use App\Services\Commercial\PurchaseOrderAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReconcilePurchaseOrderLedgerCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_succeeds_when_cached_balances_match_the_ledger(): void
    {
        $line = $this->lineWithActivity();

        $this->artisan('purchase-orders:reconcile')
            ->expectsOutputToContain('cached balances match the ledger')
            ->assertSuccessful();

        $this->assertSame(70, $line->fresh()->remaining_qty);
    }

    public function test_it_succeeds_when_there_are_no_lines(): void
    {
        $this->artisan('purchase-orders:reconcile')->assertSuccessful();
    }

    public function test_it_flags_drift_without_changing_the_cached_columns(): void
    {
        Log::spy();

        $line = $this->lineWithActivity();
        CustomerPurchaseOrderLine::query()->whereKey($line->id)->update([
            'committed_qty' => 20,
            'remaining_qty' => 80,
        ]);

        $this->artisan('purchase-orders:reconcile')
            ->expectsOutputToContain('Balance drift found on 1 of 1')
            ->assertFailed();

        $line->refresh();
        $this->assertSame(20, $line->committed_qty);
        $this->assertSame(80, $line->remaining_qty);

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $message): bool => str_contains($message, 'ledger drift'))
            ->once();
    }

    public function test_it_flags_a_remaining_column_that_disagrees_with_the_other_buckets(): void
    {
        $line = $this->lineWithActivity();
        CustomerPurchaseOrderLine::query()->whereKey($line->id)->update(['remaining_qty' => 75]);

        $this->artisan('purchase-orders:reconcile')->assertFailed();
    }

    public function test_the_po_option_limits_the_check_to_one_purchase_order(): void
    {
        $healthy = $this->lineWithActivity();
        $drifted = $this->lineWithActivity();
        CustomerPurchaseOrderLine::query()->whereKey($drifted->id)->update(['reserved_qty' => 3, 'remaining_qty' => 67]);

        $this->artisan('purchase-orders:reconcile', ['--po' => $healthy->customer_purchase_order_id])
            ->assertSuccessful();

        $this->artisan('purchase-orders:reconcile', ['--po' => $drifted->customer_purchase_order_id])
            ->assertFailed();
    }

    private function lineWithActivity(): CustomerPurchaseOrderLine
    {
        $service = app(PurchaseOrderAllocationService::class);
        $sampleTypeId = (string) Str::uuid();
        $packageId = (string) Str::uuid();

        $po = CustomerPurchaseOrder::factory()->create();
        $line = $service->addLine($po, [
            'description' => 'Potable water package',
            'ordered_qty' => 100,
            'sample_type_id' => $sampleTypeId,
            'analysis_type_ids' => [$packageId],
        ]);

        $service->commit(
            $po->fresh(),
            [new PurchaseOrderDemandItem('package', $sampleTypeId, [$packageId], 30)],
            (string) Str::uuid(),
            null,
            now(),
        );

        return $line->fresh();
    }
}
