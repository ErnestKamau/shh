<?php

if (!function_exists('getPriceForAnalysisType')) {
    /**
     * Get the price for an analysis type, optionally converting to target currency.
     *
     * @param int $analysisTypeId
     * @param int|null $targetCurrencyId
     * @return array|null Returns array with 'unit_price', 'unit_cost', 'invoicable_item_id', 'currency_id'
     */
    function getPriceForAnalysisType($analysisTypeId, $targetCurrencyId = null)
    {
        $analysisType = \App\AnalysisType::find($analysisTypeId);
        
        if (!$analysisType) {
            return null;
        }

        // Get the mapped invoicable item
        $invoicableItem = $analysisType->invoicableItems()->first();

        if (!$invoicableItem) {
            return null;
        }

        $unitPrice = $invoicableItem->unit_price;
        $unitCost = $invoicableItem->unit_cost;
        $currencyId = $invoicableItem->currency_id;

        // Convert currency if target currency is specified and different
        if ($targetCurrencyId && $currencyId != $targetCurrencyId) {
            $unitPrice = convertCurrency($unitPrice, $currencyId, $targetCurrencyId);
            $unitCost = convertCurrency($unitCost, $currencyId, $targetCurrencyId);
            $currencyId = $targetCurrencyId;
        }

        return [
            'unit_price' => $unitPrice,
            'unit_cost' => $unitCost,
            'invoicable_item_id' => $invoicableItem->id,
            'currency_id' => $currencyId,
            'item_name' => $invoicableItem->item_name,
            'item_code' => $invoicableItem->item_code,
            'tax_group_code' => $invoicableItem->tax_group_code,
            'price_includes_tax' => $invoicableItem->price_includes_tax,
        ];
    }
}

if (!function_exists('getInvoicableItemsForSelect')) {
    /**
     * Get invoicable items formatted for select dropdowns.
     *
     * @param string|null $search
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    function getInvoicableItemsForSelect($search = null, $limit = 50)
    {
        $query = \App\InvoicableItem::where('active', 1);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('item_name')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'text' => "{$item->item_code} - {$item->item_name} ({$item->formatted_price})",
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'unit_price' => $item->unit_price,
                ];
            });
    }
}

