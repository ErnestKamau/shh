<?php

namespace App\Imports\Inventory;

use App\Imports\BaseImporter;
use App\InventoryCategories;

class InventoryCategoryImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['name'] ?? null)) {
            $errors[] = 'Category name is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $locationId = null;
        try {
            if (function_exists('getCurrentUserLocation')) {
                $locationId = getCurrentUserLocation()?->id;
            }
        } catch (\Throwable $e) {
        }

        return [
            'name' => $row['name'],
            'description' => $row['description'] ?? '',
            'company_id' => $this->batch->company_id,
            'inventory_location_id' => $locationId,
            'active' => 1,
            'category_type' => 'normal',
            'is_lab' => false,
            'image' => 'no-logo.png',
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            $category = InventoryCategories::where('name', $transformedData['name'])
                ->where('inventory_location_id', $transformedData['inventory_location_id'])
                ->first();

            $action = 'inserted';
            if ($category) {
                $category->update([
                    'description' => $transformedData['description'],
                    'active' => 1,
                ]);
                $action = 'updated';
            } else {
                $category = new InventoryCategories();
                $category->name = $transformedData['name'];
                $category->description = $transformedData['description'];
                $category->company_id = $transformedData['company_id'];
                $category->inventory_location_id = $transformedData['inventory_location_id'];
                $category->active = 1;
                $category->category_type = 'normal';
                $category->is_lab = false;
                $category->image = 'no-logo.png';
                $category->save();
            }

            $this->recordUpsert($transformedData['name'], $action);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import category: {$e->getMessage()}");
        }
    }
}
