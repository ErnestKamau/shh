<?php

namespace App\Services;

use App\InvoicableItem;
use App\ModulePreConfigs;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DynamicsItemSyncService
{
    protected $baseUrl;
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->baseUrl = env('DYNAMICS_BASE_URL', "http://lsapp.fivet.local:8148/Dev-Svc/ODataV4/Company('FV-LIVE')");
        $this->username = env('DYNAMICS_USERNAME');
        $this->password = env('DYNAMICS_PASSWORD');
    }

    /**
     * Sync items from Dynamics 365 Business Central
     * 
     * @return array ['success' => bool, 'new_count' => int, 'updated_count' => int, 'total_fetched' => int, 'message' => string]
     */
    public function syncItems(): array
    {
        try {
            // Validate credentials
            if (!$this->username || !$this->password) {
                return [
                    'success' => false,
                    'new_count' => 0,
                    'updated_count' => 0,
                    'total_fetched' => 0,
                    'message' => 'Dynamics credentials not configured. Please set DYNAMICS_USERNAME and DYNAMICS_PASSWORD in .env'
                ];
            }

            // Make API request
            $url = "{$this->baseUrl}/Items";
            
            Log::info('Starting Dynamics item sync', ['url' => $url]);

            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(60)
                ->get($url);

            if (!$response->successful()) {
                Log::error('Dynamics API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'new_count' => 0,
                    'updated_count' => 0,
                    'total_fetched' => 0,
                    'message' => "API request failed: " . $response->status() . " - " . $response->body()
                ];
            }

            $data = $response->json();
            $items = $data['value'] ?? [];

            if (empty($items)) {
                return [
                    'success' => true,
                    'new_count' => 0,
                    'updated_count' => 0,
                    'total_fetched' => 0,
                    'message' => 'No items found in Dynamics'
                ];
            }

            // Get default currency (KES)
            $defaultCurrency = ModulePreConfigs::where('type', 'Currency')
                ->where('name', 'KES')
                ->first();

            if (!$defaultCurrency) {
                $defaultCurrency = ModulePreConfigs::where('type', 'Currency')->first();
            }

            $newCount = 0;
            $updatedCount = 0;

            foreach ($items as $item) {
                $this->syncItem($item, $defaultCurrency ? $defaultCurrency->id : null, $newCount, $updatedCount);
            }

            Log::info('Dynamics item sync completed', [
                'total_fetched' => count($items),
                'new' => $newCount,
                'updated' => $updatedCount
            ]);

            return [
                'success' => true,
                'new_count' => $newCount,
                'updated_count' => $updatedCount,
                'total_fetched' => count($items),
                'message' => "Successfully synced {$newCount} new items and updated {$updatedCount} existing items from Dynamics"
            ];

        } catch (\Exception $e) {
            Log::error('Dynamics item sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'new_count' => 0,
                'updated_count' => 0,
                'total_fetched' => 0,
                'message' => 'Sync failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync a single item
     */
    protected function syncItem(array $dynamicsItem, ?int $defaultCurrencyId, int &$newCount, int &$updatedCount): void
    {
        $itemCode = $dynamicsItem['No'] ?? null;
        
        if (!$itemCode) {
            return; // Skip items without code
        }

        // Check if item exists
        $existingItem = InvoicableItem::where('item_code', $itemCode)->first();

        $itemData = [
            // Basic Information
            'item_code' => $itemCode,
            'item_name' => $dynamicsItem['Description'] ?? $itemCode,
            'description' => $dynamicsItem['Description_2'] ?? null,
            'item_type' => $dynamicsItem['Type'] ?? 'Inventory',
            'item_category_code' => $dynamicsItem['Item_Category_Code'] ?? null,
            'currency_id' => $defaultCurrencyId,
            
            // Inventory & Stock Information
            'inventory_field' => $dynamicsItem['InventoryField'] ?? null,
            'shelf_no' => $dynamicsItem['Shelf_No'] ?? null,
            'costing_method' => $dynamicsItem['Costing_Method'] ?? null,
            
            // Cost Information
            'unit_cost' => (float)($dynamicsItem['Unit_Cost'] ?? 0),
            'standard_cost' => (float)($dynamicsItem['Standard_Cost'] ?? 0),
            'last_direct_cost' => (float)($dynamicsItem['Last_Direct_Cost'] ?? 0),
            'overhead_rate' => (float)($dynamicsItem['Overhead_Rate'] ?? 0),
            'indirect_cost_percent' => (float)($dynamicsItem['Indirect_Cost_Percent'] ?? 0),
            
            // Pricing Information
            'unit_price' => (float)($dynamicsItem['Unit_Price'] ?? 0),
            'price_profit_calculation' => $dynamicsItem['Price_Profit_Calculation'] ?? null,
            'profit_percent' => (float)($dynamicsItem['Profit_Percent'] ?? 0),
            
            // Posting Groups
            'inventory_posting_group' => $dynamicsItem['Inventory_Posting_Group'] ?? null,
            'gen_prod_posting_group' => $dynamicsItem['Gen_Prod_Posting_Group'] ?? null,
            'vat_prod_posting_group' => $dynamicsItem['VAT_Prod_Posting_Group'] ?? null,
            'item_disc_group' => $dynamicsItem['Item_Disc_Group'] ?? null,
            
            // Tax Information
            'tax_group_code' => $dynamicsItem['VAT_Prod_Posting_Group'] ?? null,
            'price_includes_tax' => ($dynamicsItem['Price_Includes_VAT'] ?? false) ? 1 : 0,
            
            // Vendor Information
            'vendor_no' => $dynamicsItem['Vendor_No'] ?? null,
            'vendor_item_no' => $dynamicsItem['Vendor_Item_No'] ?? null,
            
            // Additional Item Details
            'tariff_no' => $dynamicsItem['Tariff_No'] ?? null,
            'search_description' => $dynamicsItem['Search_Description'] ?? null,
            'last_date_modified' => !empty($dynamicsItem['Last_Date_Modified']) ? $dynamicsItem['Last_Date_Modified'] : null,
            'gtin' => $dynamicsItem['GTIN'] ?? null,
            
            // Units of Measure
            'base_unit_of_measure' => $dynamicsItem['Base_Unit_of_Measure'] ?? null,
            'sales_unit_of_measure' => $dynamicsItem['Sales_Unit_of_Measure'] ?? null,
            'purch_unit_of_measure' => $dynamicsItem['Purch_Unit_of_Measure'] ?? null,
            
            // Manufacturing & Replenishment
            'replenishment_system' => $dynamicsItem['Replenishment_System'] ?? null,
            'manufacturing_policy' => $dynamicsItem['Manufacturing_Policy'] ?? null,
            'assembly_policy' => $dynamicsItem['Assembly_Policy'] ?? null,
            'flushing_method' => $dynamicsItem['Flushing_Method'] ?? null,
            'item_tracking_code' => $dynamicsItem['Item_Tracking_Code'] ?? null,
            'production_bom_no' => $dynamicsItem['Production_BOM_No'] ?? null,
            'routing_no' => $dynamicsItem['Routing_No'] ?? null,
            'lead_time_calculation' => $dynamicsItem['Lead_Time_Calculation'] ?? null,
            
            // Boolean Flags
            'created_from_nonstock_item' => ($dynamicsItem['Created_From_Nonstock_Item'] ?? false) ? 1 : 0,
            'substitutes_exist' => ($dynamicsItem['Substitutes_Exist'] ?? false) ? 1 : 0,
            'stockkeeping_unit_exists' => ($dynamicsItem['Stockkeeping_Unit_Exists'] ?? false) ? 1 : 0,
            'assembly_bom' => ($dynamicsItem['Assembly_BOM'] ?? false) ? 1 : 0,
            'cost_is_adjusted' => ($dynamicsItem['Cost_is_Adjusted'] ?? false) ? 1 : 0,
            'blocked' => ($dynamicsItem['Blocked'] ?? false) ? 1 : 0,
            'active' => !($dynamicsItem['Blocked'] ?? false) ? 1 : 0,
            'coupled_to_crm' => ($dynamicsItem['Coupled_to_CRM'] ?? false) ? 1 : 0,
            'coupled_to_dataverse' => ($dynamicsItem['Coupled_to_Dataverse'] ?? false) ? 1 : 0,
            
            // Deferral Template
            'default_deferral_template_code' => $dynamicsItem['Default_Deferral_Template_Code'] ?? null,
        ];

        if ($existingItem) {
            // Update existing item
            $existingItem->update($itemData);
            $updatedCount++;
        } else {
            // Create new item
            InvoicableItem::create($itemData);
            $newCount++;
        }
    }
}

