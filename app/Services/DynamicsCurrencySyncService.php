<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DynamicsCurrencySyncService
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
     * Sync currencies from Dynamics 365 Business Central
     * 
     * @return array ['success' => bool, 'new_count' => int, 'updated_count' => int, 'total_fetched' => int, 'message' => string]
     */
    public function syncCurrencies(): array
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
            $url = "{$this->baseUrl}/Currencies";
            
            Log::info('Starting Dynamics currency sync', ['url' => $url]);

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
            $currencies = $data['value'] ?? [];

            if (empty($currencies)) {
                return [
                    'success' => true,
                    'new_count' => 0,
                    'updated_count' => 0,
                    'total_fetched' => 0,
                    'message' => 'No currencies found in Dynamics'
                ];
            }

            $newCount = 0;
            $updatedCount = 0;

            foreach ($currencies as $currency) {
                $this->syncCurrency($currency, $newCount, $updatedCount);
            }

            Log::info('Dynamics currency sync completed', [
                'total_fetched' => count($currencies),
                'new' => $newCount,
                'updated' => $updatedCount
            ]);

            return [
                'success' => true,
                'new_count' => $newCount,
                'updated_count' => $updatedCount,
                'total_fetched' => count($currencies),
                'message' => "Successfully synced {$newCount} new currencies and updated {$updatedCount} existing currencies from Dynamics"
            ];

        } catch (\Exception $e) {
            Log::error('Dynamics currency sync failed', [
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
     * Sync a single currency
     */
    protected function syncCurrency(array $dynamicsCurrency, int &$newCount, int &$updatedCount): void
    {
        $code = $dynamicsCurrency['Code'] ?? null;
        
        if (!$code) {
            return; // Skip currencies without code
        }

        // Check if currency exists
        $existingCurrency = Currency::where('code', $code)->first();

        $currencyData = [
            'code' => $code,
            'description' => $dynamicsCurrency['Description'] ?? $code,
            'iso_code' => $dynamicsCurrency['ISO_Code'] ?? null,
            'iso_numeric_code' => $dynamicsCurrency['ISO_Numeric_Code'] ?? null,
            
            // Exchange Rate Information
            'exchange_rate_date' => !empty($dynamicsCurrency['ExchangeRateDate']) && $dynamicsCurrency['ExchangeRateDate'] !== '0001-01-01' ? $dynamicsCurrency['ExchangeRateDate'] : null,
            'exchange_rate_amt' => (float)($dynamicsCurrency['ExchangeRateAmt'] ?? 0),
            'currency_factor' => (float)($dynamicsCurrency['CurrencyFactor'] ?? 0),
            
            // EMU Currency
            'emu_currency' => ($dynamicsCurrency['EMU_Currency'] ?? false) ? 1 : 0,
            
            // GL Accounts for Gains/Losses
            'realized_gains_acc' => $dynamicsCurrency['Realized_Gains_Acc'] ?? null,
            'realized_losses_acc' => $dynamicsCurrency['Realized_Losses_Acc'] ?? null,
            'unrealized_gains_acc' => $dynamicsCurrency['Unrealized_Gains_Acc'] ?? null,
            'unrealized_losses_acc' => $dynamicsCurrency['Unrealized_Losses_Acc'] ?? null,
            'realized_gl_gains_account' => $dynamicsCurrency['Realized_G_L_Gains_Account'] ?? null,
            'realized_gl_losses_account' => $dynamicsCurrency['Realized_G_L_Losses_Account'] ?? null,
            'residual_gains_account' => $dynamicsCurrency['Residual_Gains_Account'] ?? null,
            'residual_losses_account' => $dynamicsCurrency['Residual_Losses_Account'] ?? null,
            
            // Rounding Settings
            'amount_rounding_precision' => (float)($dynamicsCurrency['Amount_Rounding_Precision'] ?? 0.01),
            'amount_decimal_places' => $dynamicsCurrency['Amount_Decimal_Places'] ?? null,
            'invoice_rounding_precision' => (float)($dynamicsCurrency['Invoice_Rounding_Precision'] ?? 0.01),
            'invoice_rounding_type' => $dynamicsCurrency['Invoice_Rounding_Type'] ?? null,
            'unit_amount_rounding_precision' => (float)($dynamicsCurrency['Unit_Amount_Rounding_Precision'] ?? 0.00001),
            'unit_amount_decimal_places' => $dynamicsCurrency['Unit_Amount_Decimal_Places'] ?? null,
            'appln_rounding_precision' => (float)($dynamicsCurrency['Appln_Rounding_Precision'] ?? 0),
            
            // Conversion Rounding Accounts
            'conv_lcy_rndg_debit_acc' => $dynamicsCurrency['Conv_LCY_Rndg_Debit_Acc'] ?? null,
            'conv_lcy_rndg_credit_acc' => $dynamicsCurrency['Conv_LCY_Rndg_Credit_Acc'] ?? null,
            
            // VAT Settings
            'max_vat_difference_allowed' => (float)($dynamicsCurrency['Max_VAT_Difference_Allowed'] ?? 0),
            'vat_rounding_type' => $dynamicsCurrency['VAT_Rounding_Type'] ?? null,
            
            // Payment Tolerance
            'payment_tolerance_percent' => (float)($dynamicsCurrency['Payment_Tolerance_Percent'] ?? 0),
            'max_payment_tolerance_amount' => (float)($dynamicsCurrency['Max_Payment_Tolerance_Amount'] ?? 0),
            
            // Date Tracking
            'last_date_adjusted' => !empty($dynamicsCurrency['Last_Date_Adjusted']) && $dynamicsCurrency['Last_Date_Adjusted'] !== '0001-01-01' ? $dynamicsCurrency['Last_Date_Adjusted'] : null,
            'last_date_modified' => !empty($dynamicsCurrency['Last_Date_Modified']) && $dynamicsCurrency['Last_Date_Modified'] !== '0001-01-01' ? $dynamicsCurrency['Last_Date_Modified'] : null,
            
            // CRM Integration
            'coupled_to_crm' => ($dynamicsCurrency['Coupled_to_CRM'] ?? false) ? 1 : 0,
            'coupled_to_dataverse' => ($dynamicsCurrency['Coupled_to_Dataverse'] ?? false) ? 1 : 0,
            
            // Application Status
            'active' => 1,
        ];

        if ($existingCurrency) {
            // Update existing currency
            $existingCurrency->update($currencyData);
            $updatedCount++;
        } else {
            // Create new currency
            Currency::create($currencyData);
            $newCount++;
        }
    }
}

