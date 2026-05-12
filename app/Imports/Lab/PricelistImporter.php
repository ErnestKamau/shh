<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistItem;
use Illuminate\Support\Str;

class PricelistImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['pricelist_code'] ?? null)) {
            $errors[] = 'Pricelist code is required';
        }

        if (empty($row['pricelist_name'] ?? null)) {
            $errors[] = 'Pricelist name is required';
        }

        if (empty($row['currency_code'] ?? null)) {
            $errors[] = 'Currency code is required';
        }

        if (empty($row['item_code'] ?? null)) {
            $errors[] = 'Item code is required';
        }

        if (empty($row['analyte_code'] ?? null)) {
            $errors[] = 'Analyte code is required';
        }

        if (empty($row['unit_price'] ?? null) || !is_numeric($row['unit_price'])) {
            $errors[] = 'Unit price must be a valid number';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        return [
            'pricelist_code' => $row['pricelist_code'],
            'pricelist_name' => $row['pricelist_name'],
            'is_master' => $row['is_master'] ?? 0,
            'currency_code' => $row['currency_code'],
            'valid_till' => $row['valid_till'] ?? null,
            'item_code' => $row['item_code'],
            'item_description' => $row['item_description'] ?? '',
            'analyte_code' => $row['analyte_code'],
            'unit_price' => (float) $row['unit_price'],
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            // Find or create pricelist
            $pricelist = Pricelist::firstOrCreate(
                ['code' => $transformedData['pricelist_code'], 'company_id' => $this->batch->company_id],
                [
                    'name' => $transformedData['pricelist_name'],
                    'is_master' => $transformedData['is_master'],
                    'currency_id' => $this->getCurrencyId($transformedData['currency_code']),
                    'valid_till' => $transformedData['valid_till'],
                ]
            );

            // Find or create pricelist item
            $item = PricelistItem::firstOrCreate(
                ['code' => $transformedData['item_code'], 'pricelist_id' => $pricelist->id],
                [
                    'description' => $transformedData['item_description'],
                    'analyte_id' => $this->getAnalyteId($transformedData['analyte_code']),
                    'unit_price' => $transformedData['unit_price'],
                ]
            );

            $this->recordUpsert($transformedData['item_code'], $pricelist->wasRecentlyCreated ? 'inserted' : 'updated');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import pricelist: {$e->getMessage()}");
        }
    }

    protected function getCurrencyId(string $code): ?string
    {
        // This would fetch from currencies table, returning ID or null
        return null; // Placeholder - adjust based on your currency model
    }

    protected function getAnalyteId(string $code): ?string
    {
        $analyte = \App\Analyte::where('code', $code)
            ->where('company_id', $this->batch->company_id)
            ->first();

        return $analyte?->id;
    }
}
