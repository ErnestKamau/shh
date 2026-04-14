<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class LabInventoryCategory extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'lab_inventory_category';
    
    protected $fillable = [
        'name',
        'description',
        'image',
        'company_id',
        'inventory_location_id',
        'inventory_category_id',
        'active'
    ];

    public function available(): array
    {
        $stockDetails = $this->stock();

        return [
            'available' => floatval($stockDetails['stock_in']['total']) - floatval($stockDetails['stock_out']['total']),
            'pending' => floatval($stockDetails['pending']['total'])
        ];
    }

    public function stock(): array
    {
        $id = $this->id;

        $inventoryItems = \App\InventoryItem::join('inventory_sub_categories as isc', 'isc.id', '=', 'inventory_items.inventory_sub_category_id')
            ->selectRaw('status, isc.name as sub_category, SUM(stock_in) as stock_in, SUM(stock_out) as stock_out')
            ->where('inventory_items.inventory_category_id', $id)
            ->groupBy('stock_in', 'stock_out', 'isc.name', 'status')
            ->get();

        $inventoryItemsArr = [
            'stock_in' => [
                'subcategories' => [],
                'total' => 0
            ],
            'stock_out' => [
                'subcategories' => [],
                'total' => 0
            ],
            'pending' => [
                'subcategories' => [],
                'total' => 0
            ],
        ];

        foreach ($inventoryItems as $iItem) {
            if (!isset($inventoryItemsArr['stock_in']['subcategories'][$iItem->sub_category])) {
                $inventoryItemsArr['stock_in']['subcategories'][$iItem->sub_category] = 0;
                $inventoryItemsArr['stock_out']['subcategories'][$iItem->sub_category] = 0;
                $inventoryItemsArr['pending']['subcategories'][$iItem->sub_category] = 0;
            }

            if ($iItem->status == 'pending') {
                $inventoryItemsArr['pending']['subcategories'][$iItem->sub_category] += floatval($iItem->stock_in);
                $inventoryItemsArr['pending']['total'] += floatval($iItem->stock_in);
            } else {
                $inventoryItemsArr['stock_in']['subcategories'][$iItem->sub_category] += floatval($iItem->stock_in);
                $inventoryItemsArr['stock_in']['total'] += floatval($iItem->stock_in);
                $inventoryItemsArr['stock_out']['subcategories'][$iItem->sub_category] += floatval($iItem->stock_out);
                $inventoryItemsArr['stock_out']['total'] += floatval($iItem->stock_out);
            }
        }

        return $inventoryItemsArr;
    }
}
