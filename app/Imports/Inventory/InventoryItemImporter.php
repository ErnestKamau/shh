<?php

namespace App\Imports\Inventory;

use App\Imports\BaseImporter;
use App\InventoryCategories;
use App\InventorySubCategories;
use App\InventoryItem;

class InventoryItemImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        $name = $this->fuzzyGet($row, ['name', 'item_name', 'item']);
        $category = $this->fuzzyGet($row, ['category', 'categories', 'inventory_category', 'inventory_categories']);
        $volumeUnit = $this->fuzzyGet($row, ['volume_unit', 'volume_/_unit', 'volume_or_unit', 'volume', 'unit_type', 'volume / unit']);

        if (empty($name)) {
            $errors[] = 'Item name is required';
        }

        if (empty($category)) {
            $errors[] = 'Category is required';
        }

        if (empty($volumeUnit)) {
            $errors[] = 'Volume/Unit is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $name = trim($this->fuzzyGet($row, ['name', 'item_name', 'item']) ?? '');
        $categoryName = trim($this->fuzzyGet($row, ['category', 'categories', 'inventory_category', 'inventory_categories']) ?? '');
        $volumeUnit = trim($this->fuzzyGet($row, ['volume_unit', 'volume_/_unit', 'volume_or_unit', 'volume', 'unit_type', 'volume / unit']) ?? '');

        if (empty($name) || empty($categoryName) || empty($volumeUnit)) {
            return false;
        }

        $description = $this->fuzzyGet($row, ['description', 'desc']) ?? $name;
        $qty = floatval($this->fuzzyGet($row, ['qty', 'quantity', 'stock_in', 'initial_quantity']) ?? 0);

        $locationId = null;
        try {
            if (function_exists('getCurrentUserLocation')) {
                $locationId = getCurrentUserLocation()?->id;
            }
        } catch (\Throwable $e) {
        }

        // Auto-resolve or create Category
        $category = InventoryCategories::where('name', $categoryName)
            ->where('inventory_location_id', $locationId)
            ->first();

        if (!$category) {
            $category = new InventoryCategories();
            $category->name = $categoryName;
            $category->description = "Auto-created during bulk import";
            $category->company_id = $this->batch->company_id;
            $category->inventory_location_id = $locationId;
            $category->active = 1;
            $category->category_type = 'normal';
            $category->is_lab = false;
            $category->image = 'no-logo.png';
            $category->save();
        }

        return [
            'name' => $name,
            'description' => $description,
            'inventory_category_id' => $category->id,
            'unit_type' => $volumeUnit,
            'company_id' => $this->batch->company_id,
            'location_id' => $locationId, // maps to sub_categories location_id
            'quantity' => $qty,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            $subCategory = InventorySubCategories::where('name', $transformedData['name'])
                ->where('inventory_category_id', $transformedData['inventory_category_id'])
                ->where('location_id', $transformedData['location_id'])
                ->first();

            $action = 'inserted';
            if ($subCategory) {
                $subCategory->description = $transformedData['description'];
                $subCategory->unit_type = $transformedData['unit_type'];
                $subCategory->active = 1;
                $subCategory->save();
                $action = 'updated';
            } else {
                $subCategory = new InventorySubCategories();
                $subCategory->name = $transformedData['name'];
                $subCategory->description = $transformedData['description'];
                $subCategory->inventory_category_id = $transformedData['inventory_category_id'];
                $subCategory->unit_type = $transformedData['unit_type'];
                $subCategory->company_id = $transformedData['company_id'];
                $subCategory->location_id = $transformedData['location_id'];
                $subCategory->manufacturer = 'Any';
                $subCategory->active = 1;
                $subCategory->save();
            }

            // Create initial stock transaction if quantity supplied is positive
            if ($transformedData['quantity'] > 0) {
                // Get or generate batch code
                $catPart = substr(preg_replace('/[^a-zA-Z0-9]/', '', $originalRow['categories'] ?? $transformedData['name'] ?? 'INV'), 0, 2);
                $namePart = substr(preg_replace('/[^a-zA-Z0-9]/', '', $transformedData['name'] ?? 'IT'), 0, 2);
                $cP = strtoupper($catPart . "-" . $namePart);

                try {
                    $batchcode = getNamingConventionCode("Samples", false, $cP);
                } catch (\Throwable $e) {
                    $batchcode = $cP . '-' . rand(1000, 9999);
                }

                $item = new InventoryItem();
                $item->inventory_category_id = $transformedData['inventory_category_id'];
                $item->inventory_sub_category_id = $subCategory->id;
                $item->batch_code = $batchcode;
                $item->po_number = 'n/a';
                $item->barcode = 'n/a';
                $item->created_by = $this->batch->user_id;
                $item->stock_in = $transformedData['quantity'];
                $item->status = 'approved';
                $item->inventory_location_id = $transformedData['location_id'];
                $item->save();

                // Recalculate available stock and reorder flags using native helper
                try {
                    if (function_exists('calculateAvailableStock')) {
                        calculateAvailableStock($subCategory->id);
                    }
                } catch (\Throwable $e) {}
            }

            $this->recordUpsert($transformedData['name'], $action);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import item: {$e->getMessage()}");
        }
    }
}
