<?php

namespace App\Console\Commands\Commercial;

use App\DTOs\Commercial\PurchaseOrderDemandItem;
use App\Enums\Commercial\PurchaseOrderStatus;
use App\Enums\Commercial\PurchaseOrderType;
use App\Enums\Commercial\SampleHeaderPoStatus;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\SampleSubmissionRequest;
use App\QuotationDetails;
use App\SampleHeader;
use App\Services\Commercial\PurchaseOrderAllocationService;
use App\Services\Commercial\QuotationPurchaseOrderLineMapper;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Converts legacy one-PO-per-enquiry rows into ledger-backed POs: one line per quotation detail,
 * an Order entry for each line, then a Commit (enquiry already has a job) or Reserve (not yet received).
 * POs that already have lines are skipped, so the command is safe to re-run.
 */
class BackfillPurchaseOrderLedgerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-orders:backfill-ledger
                            {--dry-run : Show what would be created without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create PO lines and ledger entries for legacy customer purchase orders recorded per enquiry';

    /**
     * Execute the console command.
     */
    public function handle(PurchaseOrderAllocationService $allocationService, QuotationPurchaseOrderLineMapper $lineMapper): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $purchaseOrders = CustomerPurchaseOrder::query()
            ->where('po_skipped', false)
            ->whereNotNull('po_number')
            ->where('po_number', '!=', '')
            ->whereDoesntHave('lines')
            ->with(['enquiry.batch', 'quotation.details.sampletype'])
            ->orderBy('created_at')
            ->get();

        if ($purchaseOrders->isEmpty()) {
            $this->info('No legacy purchase orders need a ledger backfill.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[dry-run] ' : '').'Backfilling '.$purchaseOrders->count().' purchase order(s)…');

        $converted = 0;
        $failed = 0;

        foreach ($purchaseOrders as $purchaseOrder) {
            $details = $this->quotationDetails($purchaseOrder);
            $enquiry = $purchaseOrder->enquiry;
            $batch = $enquiry?->batch;

            if ($dryRun) {
                $this->line(sprintf(
                    'Would convert PO %s (%s): %d line(s), %s',
                    $purchaseOrder->po_number,
                    $purchaseOrder->id,
                    $details->count(),
                    $batch !== null ? 'commit to job '.$batch->id : ($enquiry !== null ? 'reserve for enquiry '.$enquiry->id : 'no enquiry'),
                ));
                $converted++;

                continue;
            }

            try {
                DB::transaction(function () use ($allocationService, $lineMapper, $purchaseOrder, $details, $enquiry, $batch): void {
                    $this->convert($allocationService, $lineMapper, $purchaseOrder, $details, $enquiry, $batch);
                });
                $converted++;
            } catch (Throwable $exception) {
                $failed++;
                $this->error('PO '.$purchaseOrder->po_number.' ('.$purchaseOrder->id.') failed: '.$exception->getMessage());
            }
        }

        $this->info(($dryRun ? '[dry-run] Would convert ' : 'Converted ').$converted.' purchase order(s).');

        if ($failed > 0) {
            $this->warn($failed.' purchase order(s) failed and were left unchanged.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, QuotationDetails>  $details
     */
    private function convert(
        PurchaseOrderAllocationService $allocationService,
        QuotationPurchaseOrderLineMapper $lineMapper,
        CustomerPurchaseOrder $purchaseOrder,
        Collection $details,
        ?SampleSubmissionRequest $enquiry,
        ?SampleHeader $batch,
    ): void {
        $purchaseOrder->forceFill([
            'po_type' => $purchaseOrder->po_type ?? PurchaseOrderType::Single,
            'status' => PurchaseOrderStatus::Active,
        ])->save();

        $demand = [];

        foreach ($details as $detail) {
            $draft = $lineMapper->draftFromDetail($detail);

            $line = $allocationService->addLine($purchaseOrder, [
                'description' => $draft['description'],
                'ordered_qty' => $draft['ordered_qty'],
                'unit_price_gross' => $draft['unit_price_gross'],
                'sample_type_id' => $draft['sample_type_id'],
                'analysis_type_ids' => $draft['analysis_type_ids'],
                'is_package' => $draft['is_package'],
                'quotation_detail_id' => $draft['quotation_detail_id'],
            ]);

            $demand[] = new PurchaseOrderDemandItem((string) $line->id, $draft['sample_type_id'], $draft['analysis_type_ids'], $draft['ordered_qty']);
        }

        if ($enquiry === null) {
            return;
        }

        if (blank($enquiry->getAttribute('customer_purchase_order_id'))) {
            $enquiry->forceFill(['customer_purchase_order_id' => $purchaseOrder->id])->saveQuietly();
        }

        if ($demand === []) {
            return;
        }

        $purchaseOrder->refresh();

        if ($batch === null) {
            $allocationService->reserve($purchaseOrder, $demand, (string) $enquiry->id, now());

            return;
        }

        $result = $allocationService->commit(
            $purchaseOrder,
            $demand,
            (string) $batch->id,
            (string) $enquiry->id,
            $batch->created_at ?? now(),
        );

        SampleHeader::query()->whereKey($batch->id)->update([
            'customer_purchase_order_id' => $purchaseOrder->id,
            'po_status' => $result->hasUncovered() ? SampleHeaderPoStatus::NotRequired->value : SampleHeaderPoStatus::Covered->value,
        ]);
    }

    /**
     * @return Collection<int, QuotationDetails>
     */
    private function quotationDetails(CustomerPurchaseOrder $purchaseOrder): Collection
    {
        $details = $purchaseOrder->quotation?->details;

        return $details instanceof Collection ? $details->values() : collect();
    }
}
