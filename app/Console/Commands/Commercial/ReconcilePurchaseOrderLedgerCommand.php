<?php

namespace App\Console\Commands\Commercial;

use App\DTOs\Commercial\PurchaseOrderLineBalance;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use App\Services\Commercial\PurchaseOrderAllocationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcilePurchaseOrderLedgerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-orders:reconcile
                            {--po= : Only reconcile lines of this customer purchase order id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recompute PO line balances from the ledger and flag any drift in the cached columns (never auto-corrects)';

    /**
     * Execute the console command.
     */
    public function handle(PurchaseOrderAllocationService $allocationService): int
    {
        $query = CustomerPurchaseOrderLine::query()->orderBy('customer_purchase_order_id')->orderBy('line_no');

        $poId = trim((string) $this->option('po'));
        if ($poId !== '') {
            $query->where('customer_purchase_order_id', $poId);
        }

        $drifted = [];
        $checked = 0;

        $query->chunkById(200, function ($lines) use ($allocationService, &$drifted, &$checked): void {
            foreach ($lines as $line) {
                $checked++;
                $cached = PurchaseOrderLineBalance::fromCachedColumns($line);
                $ledger = $allocationService->balanceFromLedger($line);
                $cachedRemaining = (int) $line->remaining_qty;

                if ($cached->equals($ledger) && $cachedRemaining === $ledger->remaining()) {
                    continue;
                }

                $drifted[] = [
                    'po' => (string) $line->customer_purchase_order_id,
                    'line' => (string) $line->id,
                    'line_no' => (int) $line->line_no,
                    'cached' => $cached->toColumns() + ['remaining_qty' => $cachedRemaining],
                    'ledger' => $ledger->toColumns(),
                ];
            }
        });

        if ($drifted === []) {
            $this->info("Checked {$checked} purchase order line(s): cached balances match the ledger.");

            return self::SUCCESS;
        }

        $this->error('Balance drift found on '.count($drifted).' of '.$checked.' purchase order line(s). Cached columns were NOT changed.');
        $this->table(
            ['PO', 'Line', 'Column', 'Cached', 'Ledger'],
            collect($drifted)->flatMap(function (array $row): array {
                $rows = [];
                foreach ($row['ledger'] as $column => $ledgerValue) {
                    $cachedValue = $row['cached'][$column] ?? null;
                    if ($cachedValue !== $ledgerValue) {
                        $rows[] = [$row['po'], '#'.$row['line_no'].' '.$row['line'], $column, $cachedValue, $ledgerValue];
                    }
                }

                return $rows;
            })->all(),
        );

        Log::warning('Customer PO ledger drift detected', ['lines' => $drifted]);

        return self::FAILURE;
    }
}
