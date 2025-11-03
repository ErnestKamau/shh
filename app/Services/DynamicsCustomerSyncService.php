<?php

namespace App\Services;

use App\ZohoCustomers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DynamicsCustomerSyncService
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $batchSize = 500; // Bulk insert batch size

    public function __construct()
    {
        $this->baseUrl = env('DYNAMICS_BASE_URL', "http://lsapp.fivet.local:8148/Dev-Svc/ODataV4/Company('FV-LIVE')");
        $this->username = env('DYNAMICS_USERNAME');
        $this->password = env('DYNAMICS_PASSWORD');
    }

    /**
     * Sync customers from Dynamics 365 Business Central
     * 
     * @return array ['success' => bool, 'new_count' => int, 'updated_count' => int, 'total_fetched' => int, 'message' => string]
     */
    public function syncCustomers(): array
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
            $url = "{$this->baseUrl}/Customers";
            
            Log::info('Starting Dynamics customer sync', ['url' => $url]);

            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(120)
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
            $customers = $data['value'] ?? [];

            if (empty($customers)) {
                return [
                    'success' => true,
                    'new_count' => 0,
                    'updated_count' => 0,
                    'total_fetched' => 0,
                    'message' => 'No customers found in Dynamics'
                ];
            }

            // Process customers in bulk
            $result = $this->bulkSyncCustomers($customers);

            Log::info('Dynamics customer sync completed', [
                'total_fetched' => count($customers),
                'new' => $result['new_count'],
                'updated' => $result['updated_count']
            ]);

            return [
                'success' => true,
                'new_count' => $result['new_count'],
                'updated_count' => $result['updated_count'],
                'total_fetched' => count($customers),
                'message' => "Successfully synced {$result['new_count']} new customers and updated {$result['updated_count']} existing customers from Dynamics"
            ];

        } catch (\Exception $e) {
            Log::error('Dynamics customer sync failed', [
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
     * Bulk sync customers using batch inserts/updates
     */
    protected function bulkSyncCustomers(array $dynamicsCustomers): array
    {
        $newCount = 0;
        $updatedCount = 0;

        // Get existing customer numbers for quick lookup
        $existingCustomers = ZohoCustomers::whereNotNull('customer_no')
            ->pluck('id', 'customer_no')
            ->toArray();

        $toInsert = [];
        $toUpdate = [];

        $now = now();

        foreach ($dynamicsCustomers as $dynamicsCustomer) {
            $customerNo = $dynamicsCustomer['No'] ?? null;
            
            if (!$customerNo) {
                continue; // Skip customers without number
            }

            $customerData = $this->mapCustomerData($dynamicsCustomer, $now);

            if (isset($existingCustomers[$customerNo])) {
                // Existing customer - prepare for update
                $customerData['id'] = $existingCustomers[$customerNo];
                $toUpdate[] = $customerData;
            } else {
                // New customer - prepare for insert
                $toInsert[] = $customerData;
            }
        }

        // Bulk insert new customers
        if (!empty($toInsert)) {
            foreach (array_chunk($toInsert, $this->batchSize) as $batch) {
                DB::table('zoho_customers')->insert($batch);
                $newCount += count($batch);
            }
        }

        // Bulk update existing customers
        if (!empty($toUpdate)) {
            foreach ($toUpdate as $customerData) {
                $id = $customerData['id'];
                unset($customerData['id']);
                
                DB::table('zoho_customers')
                    ->where('id', $id)
                    ->update($customerData);
                    
                $updatedCount++;
            }
        }

        return [
            'new_count' => $newCount,
            'updated_count' => $updatedCount,
        ];
    }

    /**
     * Map Dynamics customer data to local database structure
     */
    protected function mapCustomerData(array $dynamicsCustomer, $now): array
    {
        return [
            'customer_no' => $dynamicsCustomer['No'] ?? null,
            'name' => $dynamicsCustomer['Name'] ?? null,
            'name_2' => $dynamicsCustomer['Name_2'] ?? null,
            'email' => null, // Not provided in Dynamics data
            'phone_no' => $dynamicsCustomer['Phone_No'] ?? null,
            'contact' => $dynamicsCustomer['Contact'] ?? null,
            'responsibility_center' => $dynamicsCustomer['Responsibility_Center'] ?? null,
            'location_code' => $dynamicsCustomer['Location_Code'] ?? null,
            'post_code' => $dynamicsCustomer['Post_Code'] ?? null,
            'country_region_code' => $dynamicsCustomer['Country_Region_Code'] ?? null,
            'ic_partner_code' => $dynamicsCustomer['IC_Partner_Code'] ?? null,
            'salesperson_code' => $dynamicsCustomer['Salesperson_Code'] ?? null,
            'customer_posting_group' => $dynamicsCustomer['Customer_Posting_Group'] ?? null,
            'allow_multiple_posting_groups' => ($dynamicsCustomer['Allow_Multiple_Posting_Groups'] ?? false) ? 1 : 0,
            'gen_bus_posting_group' => $dynamicsCustomer['Gen_Bus_Posting_Group'] ?? null,
            'vat_bus_posting_group' => $dynamicsCustomer['VAT_Bus_Posting_Group'] ?? null,
            'customer_price_group' => $dynamicsCustomer['Customer_Price_Group'] ?? null,
            'customer_disc_group' => $dynamicsCustomer['Customer_Disc_Group'] ?? null,
            'payment_terms_code' => $dynamicsCustomer['Payment_Terms_Code'] ?? null,
            'reminder_terms_code' => $dynamicsCustomer['Reminder_Terms_Code'] ?? null,
            'fin_charge_terms_code' => $dynamicsCustomer['Fin_Charge_Terms_Code'] ?? null,
            'currency_code' => $dynamicsCustomer['Currency_Code'] ?? null,
            'language_code' => $dynamicsCustomer['Language_Code'] ?? null,
            'search_name' => $dynamicsCustomer['Search_Name'] ?? null,
            'credit_limit_lcy' => (float)($dynamicsCustomer['Credit_Limit_LCY'] ?? 0),
            'blocked' => $dynamicsCustomer['Blocked'] ?? null,
            'privacy_blocked' => ($dynamicsCustomer['Privacy_Blocked'] ?? false) ? 1 : 0,
            'last_date_modified' => isset($dynamicsCustomer['Last_Date_Modified']) ? date('Y-m-d', strtotime($dynamicsCustomer['Last_Date_Modified'])) : null,
            'application_method' => $dynamicsCustomer['Application_Method'] ?? null,
            'combine_shipments' => ($dynamicsCustomer['Combine_Shipments'] ?? false) ? 1 : 0,
            'reserve' => $dynamicsCustomer['Reserve'] ?? null,
            'ship_to_code' => $dynamicsCustomer['Ship_to_Code'] ?? null,
            'shipping_advice' => $dynamicsCustomer['Shipping_Advice'] ?? null,
            'shipping_agent_code' => $dynamicsCustomer['Shipping_Agent_Code'] ?? null,
            'base_calendar_code' => $dynamicsCustomer['Base_Calendar_Code'] ?? null,
            'balance_lcy' => (float)($dynamicsCustomer['Balance_LCY'] ?? 0),
            'balance_due_lcy' => (float)($dynamicsCustomer['Balance_Due_LCY'] ?? 0),
            'sales_lcy' => (float)($dynamicsCustomer['Sales_LCY'] ?? 0),
            'payments_lcy' => (float)($dynamicsCustomer['Payments_LCY'] ?? 0),
            'coupled_to_crm' => ($dynamicsCustomer['Coupled_to_CRM'] ?? false) ? 1 : 0,
            'coupled_to_dataverse' => ($dynamicsCustomer['Coupled_to_Dataverse'] ?? false) ? 1 : 0,
            'status' => 'Active', // Default status
            'updated_at' => $now,
            'created_at' => $now,
        ];
    }
}

