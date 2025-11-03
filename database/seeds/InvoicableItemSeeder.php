<?php

use Illuminate\Database\Seeder;
use App\InvoicableItem;
use App\ModulePreConfigs;

class InvoicableItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load the JSON file
        $jsonPath = base_path('dynamicJsons/items.json');
        
        if (!file_exists($jsonPath)) {
            $this->command->error("Items JSON file not found at: {$jsonPath}");
            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $itemData = json_decode($jsonContent, true);

        if (!$itemData) {
            $this->command->error("Failed to parse items.json");
            return;
        }

        // Get a default currency (or create one if needed)
        $currency = ModulePreConfigs::where('type', 'Currency')->first();
        
        if (!$currency) {
            $this->command->warn("No currency found in module_pre_configs. Creating default USD currency.");
            $currency = ModulePreConfigs::create([
                'type' => 'Currency',
                'name' => 'USD',
                'value' => 'US Dollar',
            ]);
        }

        // Check if item already exists
        $existingItem = InvoicableItem::where('item_code', $itemData['number'] ?? '')->first();
        
        if ($existingItem) {
            $this->command->info("Item {$itemData['number']} already exists. Skipping.");
            return;
        }

        // Create the invoicable item from JSON data
        $item = InvoicableItem::create([
            'item_code' => $itemData['number'] ?? 'ITEM-001',
            'item_name' => $itemData['displayName'] ?? 'Sample Item',
            'description' => $itemData['displayName'] ?? '',
            'item_type' => $itemData['type'] ?? 'Service',
            'item_category_code' => $itemData['itemCategoryCode'] ?? null,
            'unit_price' => $itemData['unitPrice'] ?? 0,
            'unit_cost' => $itemData['unitCost'] ?? 0,
            'currency_id' => $currency->id,
            'price_includes_tax' => $itemData['priceIncludesTax'] ?? false,
            'tax_group_code' => $itemData['taxGroupCode'] ?? null,
            'base_unit_of_measure' => $itemData['baseUnitOfMeasureCode'] ?? 'PCS',
            'gtin' => $itemData['gtin'] ?? null,
            'blocked' => $itemData['blocked'] ?? false,
            'active' => true,
        ]);

        $this->command->info("Created invoicable item: {$item->item_code} - {$item->item_name}");
        
        // You can add more sample items here if needed
        $this->createDefaultLabServiceItems($currency->id);
    }

    /**
     * Create default lab service items
     */
    private function createDefaultLabServiceItems(int $currencyId): void
    {
        $defaultItems = [
            [
                'item_code' => 'LAB-MICRO-001',
                'item_name' => 'Microbiological Analysis',
                'description' => 'Standard microbiological analysis service',
                'item_type' => 'Service',
                'unit_price' => 500.00,
                'unit_cost' => 300.00,
            ],
            [
                'item_code' => 'LAB-CHEM-001',
                'item_name' => 'Chemical Analysis',
                'description' => 'Standard chemical analysis service',
                'item_type' => 'Service',
                'unit_price' => 750.00,
                'unit_cost' => 450.00,
            ],
            [
                'item_code' => 'LAB-PHYS-001',
                'item_name' => 'Physical Testing',
                'description' => 'Standard physical testing service',
                'item_type' => 'Service',
                'unit_price' => 400.00,
                'unit_cost' => 250.00,
            ],
        ];

        foreach ($defaultItems as $itemData) {
            $exists = InvoicableItem::where('item_code', $itemData['item_code'])->exists();
            
            if (!$exists) {
                InvoicableItem::create(array_merge($itemData, [
                    'currency_id' => $currencyId,
                    'price_includes_tax' => false,
                    'base_unit_of_measure' => 'PCS',
                    'active' => true,
                ]));
                
                $this->command->info("Created default item: {$itemData['item_code']}");
            }
        }
    }
}
