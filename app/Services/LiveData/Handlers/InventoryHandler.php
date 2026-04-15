<?php

namespace App\Services\LiveData\Handlers;

use Illuminate\Support\Facades\DB;
use App\Services\LiveData\Contracts\LiveDataHandlerInterface;
use App\Services\LiveData\Contracts\FormatterInterface;
use App\Services\LiveData\DTOs\LiveDataResult;

class InventoryHandler implements LiveDataHandlerInterface
{
    public function __construct(protected FormatterInterface $formatter) {}

    public function supports(string $intent): bool
    {
        return str_starts_with($intent, 'inventory_');
    }

    public function handle(string $intent, ?string $question = null): LiveDataResult
    {
        $data = match ($intent) {
            'inventory_low_stock'    => $this->inventoryLowStock(),
            'inventory_order_status' => $this->inventoryOrderStatus(),
            default => ['reply' => "Unsupported inventory intent: {$intent}", 'value' => null]
        };

        return LiveDataResult::fromArray($intent, $data);
    }

    private function inventoryLowStock(): array
    {
        $rows = DB::table('inventory_items as ii')
            ->join('inventory_sub_categories as sc', 'ii.inventory_sub_category_id', '=', 'sc.id')
            ->join('inventory_categories as c', 'sc.inventory_category_id', '=', 'c.id')
            ->whereRaw('ii.stock_in < sc.minimum_level')
            ->selectRaw('
                c.name as category,
                sc.name as subcategory,
                ii.batch_code,
                ii.stock_in,
                sc.minimum_level,
                (sc.minimum_level - ii.stock_in) as shortage
            ')
            ->orderByDesc('shortage')
            ->limit(10)
            ->get();

        $total = $rows->count();
        $reply = "## Inventory — Low Stock Alert\n\n"
            . "**{$total}** item(s) are below minimum reorder level.\n\n";

        if ($total > 0) {
            $table = $this->formatter->buildTable(
                ['Category', 'Item', 'Stock', 'Min', 'Shortage'], 
                $rows->map(fn($r) => [
                    'category' => $r->category . ' → ' . $r->subcategory,
                    'item' => $r->batch_code,
                    'stock' => $r->stock_in,
                    'min' => $r->minimum_level,
                    'shortage' => $r->shortage
                ])->toArray()
            );
            $reply .= $table;
        }

        return ['reply' => $reply, 'value' => $total];
    }

    private function inventoryOrderStatus(): array
    {
        $rows = DB::table('inventory_orders as io')
            ->join('suppliers as s', 's.id', '=', 'io.supplier_id')
            ->whereIn('io.fulfillment_status', ['not_fulfilled', 'partially_fulfilled'])
            ->selectRaw('io.order_number, s.name as supplier, io.created_at, io.fulfillment_status')
            ->orderByDesc('io.created_at')
            ->limit(10)
            ->get();

        $total = $rows->count();
        $reply = "## Pending Inventory Orders\n\n"
            . "**{$total}** order(s) awaiting delivery.\n\n";

        if ($total > 0) {
            $table = $this->formatter->buildTable(
                ['Order #', 'Supplier', 'Status', 'Date'],
                $rows->map(fn($r) => [
                    'number' => $r->order_number,
                    'supplier' => $r->supplier,
                    'status' => ucfirst(str_replace('_', ' ', $r->fulfillment_status)),
                    'date' => \Carbon\Carbon::parse($r->created_at)->format('d M Y')
                ])->toArray()
            );
            $reply .= $table;
        }

        return ['reply' => $reply, 'value' => $total];
    }
}
