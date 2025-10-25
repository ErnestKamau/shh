<?php

namespace App\Livewire\Billing;

use App\Invoice;
use App\InvoiceDetails;
use App\InvoicableItem;
use App\SampleHeader;
use App\SampleDetails;
use App\AnalysisType;
use App\SampleAnalysisTypeRelation;
use App\Models\CRM\CRMCustomer;
use App\ZohoCustomers;
use App\Models\Currency;
use App\TaxRegime;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class SalesOrderWizard extends Component
{
    // Batch selection
    public $selectedBatches = [];
    public $batchData = [];

    // Current step (1-5)
    public $currentStep = 1;

    // Customer validation
    public $customer = null;
    public $customerId = null;

    // Zoho mapping (Step 2)
    public $zohoCustomerId = null;
    public $zohoCurrencyId = null;
    public $showZohoCustomerDropdown = false;
    public $showZohoCurrencyDropdown = false;
    public $zohoCustomerSearch = '';
    public $zohoCurrencySearch = '';
    public $updateCustomerZohoData = false;

    // Analysis mapping (Step 3)
    public $analysisTypesData = []; // [analysis_type_id => ['name' => '', 'code' => '', 'count' => 0]]
    public $analysisMappings = []; // [analysis_type_id => invoicable_item_id]
    public $analysisCustomPrices = []; // [analysis_type_id => custom_unit_price]
    public $showItemDropdowns = []; // [analysis_type_id => boolean]
    public $itemSearches = []; // [analysis_type_id => search_string]

    // Additional items (Step 4)
    public $additionalItems = [];
    public $showAdditionalItemDropdown = false;
    public $additionalItemSearch = '';
    public $showAddItemModal = false;

    // Messages
    public $errorMessage = '';
    public $validationErrors = [];
    public $successMessage = '';

    public function mount($batchCodes = [])
    {
        if (!empty($batchCodes)) {
            $this->selectedBatches = is_array($batchCodes) ? $batchCodes : [$batchCodes];
            $this->loadBatchData();
            $this->validateBatches();
        }
    }

    public function loadBatchData(): void
    {
        $batches = SampleHeader::with(['sample_type', 'client'])
            ->whereIn('batch_code', $this->selectedBatches)
            ->get();

        $this->batchData = $batches->map(function($batch) {
            return [
                'id' => $batch->id,
                'batch_code' => $batch->batch_code,
                'customer_id' => $batch->crm_customer_id,
                'customer_name' => $batch->client->name ?? 'N/A',
                'sample_type_name' => $batch->sample_type->name ?? 'N/A',
                'receipt_date' => $batch->receipt_date,
                'sample_count' => SampleDetails::where('sample_header_id', $batch->id)->count(),
                'has_invoice' => $batch->invoice_id > 0,
            ];
        })->toArray();
    }

    public function validateBatches(): bool
    {
        $this->validationErrors = [];

        if (empty($this->batchData)) {
            $this->validationErrors[] = 'No batches selected';
            return false;
        }

        // Check same customer
        $customerIds = array_unique(array_column($this->batchData, 'customer_id'));
        if (count($customerIds) > 1) {
            $this->validationErrors[] = 'Selected batches are from different customers';
            return false;
        }

        // Check no existing invoices
        foreach ($this->batchData as $batch) {
            if ($batch['has_invoice']) {
                $this->validationErrors[] = "Batch {$batch['batch_code']} already has an existing sales order";
            }
        }

        if (!empty($this->validationErrors)) {
            return false;
        }

        // Set customer
        $this->customerId = $customerIds[0];
        $this->customer = CRMCustomer::find($this->customerId);

        return true;
    }

    public function removeBatch($batchCode): void
    {
        $this->selectedBatches = array_values(array_filter($this->selectedBatches, function($code) use ($batchCode) {
            return $code !== $batchCode;
        }));
        
        $this->loadBatchData();
        $this->validateBatches();
    }

    // Navigation Methods
    public function nextStep(): void
    {
        // Validate current step before proceeding
        if ($this->currentStep === 1) {
            if (!$this->validateBatches()) {
                return;
            }
            $this->loadAnalysisTypesData();
        }

        if ($this->currentStep === 2) {
            if (!$this->validateCustomerZohoData()) {
                return;
            }
            // Auto-map analyses when moving to step 3 (silent - no success message)
            $this->autoMapAllAnalyses(false);
        }

        if ($this->currentStep === 3) {
            if (!$this->validateAnalysisMappings()) {
                return;
            }
        }

        if ($this->currentStep === 4) {
            $this->buildInvoicePreview();
        }

        $this->currentStep++;
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->errorMessage = '';
            $this->validationErrors = [];
        }
    }

    public function goToStep($step): void
    {
        $this->currentStep = $step;
    }

    // Step 2: Zoho Mapping Methods
    public function validateCustomerZohoData(): bool
    {
        $this->validationErrors = [];

        if (!$this->zohoCustomerId) {
            $this->validationErrors[] = 'Please select a Zoho Customer';
        }

        if (!$this->zohoCurrencyId) {
            $this->validationErrors[] = 'Please select a Currency';
        }

        return empty($this->validationErrors);
    }

    public function selectZohoCustomer($customerNo): void
    {
        $this->zohoCustomerId = $customerNo;
        $this->showZohoCustomerDropdown = false;
        $this->zohoCustomerSearch = '';
        
        // Clear currency first
        $this->zohoCurrencyId = null;
        
        // Auto-fetch currency from the selected Zoho/Dynamics customer
        $zohoCustomer = ZohoCustomers::where('customer_no', $customerNo)->first();
        
        if ($zohoCustomer) {
            // Try currency_id first (direct foreign key)
            if (!empty($zohoCustomer->currency_id)) {
                $currency = Currency::find($zohoCustomer->currency_id);
                if ($currency) {
                    $this->zohoCurrencyId = $currency->id;
                }
            }
            
            // If no currency found yet, try currency_code
            if (!$this->zohoCurrencyId && !empty($zohoCustomer->currency_code)) {
                $currency = Currency::where('code', $zohoCustomer->currency_code)
                    ->orWhere('iso_code', $zohoCustomer->currency_code)
                    ->first();
                
                if ($currency) {
                    $this->zohoCurrencyId = $currency->id;
                }
            }
        }
        
        // Auto-check update if value changed
        if ($this->customer && $this->customer->zoho_id != $customerNo) {
            $this->updateCustomerZohoData = true;
        }
    }

    public function clearZohoCustomer(): void
    {
        $this->zohoCustomerId = null;
        $this->zohoCurrencyId = null;
        $this->zohoCustomerSearch = '';
        $this->showZohoCustomerDropdown = false;
        $this->updateCustomerZohoData = false;
    }

    public function selectZohoCurrency($currencyId): void
    {
        $this->zohoCurrencyId = $currencyId;
        $this->showZohoCurrencyDropdown = false;
        $this->zohoCurrencySearch = '';
        
        // Auto-check update checkbox when currency is manually selected or different from customer's
        if ($this->customer && ($this->customer->currency_id != $currencyId || !$this->customer->currency_id)) {
            $this->updateCustomerZohoData = true;
        }
    }

    public function getFilteredZohoCustomersProperty()
    {
        $query = ZohoCustomers::query();

        if ($this->zohoCustomerSearch) {
            $query->where(function($q) {
                $q->where('name', 'like', "%{$this->zohoCustomerSearch}%")
                  ->orWhere('email', 'like', "%{$this->zohoCustomerSearch}%")
                  ->orWhere('customer_no', 'like', "%{$this->zohoCustomerSearch}%");
            });
        }

        return $query->orderBy('name')->limit(20)->get();
    }
    
    public function updatedZohoCustomerSearch()
    {
        $this->showZohoCustomerDropdown = true;
    }
    
    public function updatedZohoCurrencySearch()
    {
        $this->showZohoCurrencyDropdown = true;
    }

    public function getFilteredCurrenciesProperty()
    {
        $query = Currency::where('active', 1);

        if ($this->zohoCurrencySearch) {
            $query->where(function($q) {
                $q->where('code', 'like', "%{$this->zohoCurrencySearch}%")
                  ->orWhere('description', 'like', "%{$this->zohoCurrencySearch}%");
            });
        }

        return $query->orderBy('code')->get();
    }

    public function getSelectedZohoCustomerProperty()
    {
        if ($this->zohoCustomerId) {
            return ZohoCustomers::where('customer_no', $this->zohoCustomerId)->first();
        }
        return null;
    }

    public function getSelectedZohoCurrencyProperty()
    {
        if ($this->zohoCurrencyId) {
            return Currency::find($this->zohoCurrencyId);
        }
        return null;
    }

    // Step 3: Analysis Mapping Methods
    public function loadAnalysisTypesData(): void
    {
        $batchIds = array_column($this->batchData, 'id');
        
        // Get all analysis types from selected batches with their counts
        $analysisData = SampleAnalysisTypeRelation::whereIn('batch_id', $batchIds)
            ->join('analysis_types', 'analysis_types.id', '=', 'sample_analysis_type_relation.analysis_type_id')
            ->select('analysis_types.id', 'analysis_types.name', 'analysis_types.code', DB::raw('COUNT(*) as count'))
            ->groupBy('analysis_types.id', 'analysis_types.name', 'analysis_types.code')
            ->get();

        $this->analysisTypesData = [];
        foreach ($analysisData as $data) {
            $this->analysisTypesData[$data->id] = [
                'name' => $data->name,
                'code' => $data->code,
                'count' => $data->count,
            ];
        }

        // Pre-load Zoho data if customer has them
        if ($this->customer) {
            $this->zohoCustomerId = $this->customer->zoho_id;
            
            // Check if customer has currency_id that maps to currencies table
            if ($this->customer->currency_id) {
                $this->zohoCurrencyId = $this->customer->currency_id;
            }
        }
    }

    public function autoMapAllAnalyses($showMessage = true): void
    {
        foreach (array_keys($this->analysisTypesData) as $analysisTypeId) {
            $analysisType = AnalysisType::find($analysisTypeId);
            $invoicableItem = $analysisType?->invoicableItems()->first();
            
            if ($invoicableItem) {
                $this->analysisMappings[$analysisTypeId] = $invoicableItem->id;
            }
        }

        if ($showMessage) {
            $this->successMessage = 'Re-mapped ' . count($this->analysisMappings) . ' analysis type(s)';
        }
    }

    public function selectInvoicableItemForAnalysis($analysisTypeId, $itemId): void
    {
        $this->analysisMappings[$analysisTypeId] = $itemId;
        $this->showItemDropdowns[$analysisTypeId] = false;
        $this->itemSearches[$analysisTypeId] = '';
    }

    public function validateAnalysisMappings(): bool
    {
        $this->validationErrors = [];

        foreach (array_keys($this->analysisTypesData) as $analysisTypeId) {
            if (!isset($this->analysisMappings[$analysisTypeId])) {
                $analysisName = $this->analysisTypesData[$analysisTypeId]['name'];
                $this->validationErrors[] = "Analysis type '{$analysisName}' is not mapped to any invoicable item";
            }
        }

        return empty($this->validationErrors);
    }

    public function getFilteredItemsForAnalysisProperty()
    {
        return InvoicableItem::where('active', 1)
            ->where(function($q) {
                foreach ($this->itemSearches as $search) {
                    if ($search) {
                        $q->where('item_code', 'like', "%{$search}%")
                          ->orWhere('item_name', 'like', "%{$search}%");
                    }
                }
            })
            ->orderBy('item_name')
            ->limit(20)
            ->get();
    }

    public function getInvoicableItemById($itemId)
    {
        return InvoicableItem::find($itemId);
    }

    public function getAnalysisTotalProperty()
    {
        $total = 0;
        foreach ($this->analysisMappings as $analysisTypeId => $itemId) {
            $item = $this->getInvoicableItemById($itemId);
            if ($item) {
                $count = $this->analysisTypesData[$analysisTypeId]['count'] ?? 0;
                // Use custom price if set, otherwise use item price
                $unitPrice = $this->analysisCustomPrices[$analysisTypeId] ?? $item->unit_price;
                $total += $unitPrice * $count;
            }
        }
        return $total;
    }

    public function getUnmappedAnalysisCountProperty()
    {
        return count($this->analysisTypesData) - count($this->analysisMappings);
    }

    // Step 4: Additional Items Methods
    public function openAddItemModal(): void
    {
        $this->showAddItemModal = true;
    }

    public function closeAddItemModal(): void
    {
        $this->showAddItemModal = false;
        $this->additionalItemSearch = '';
    }

    public function addAdditionalItem($itemId): void
    {
        $item = InvoicableItem::find($itemId);
        
        if (!$item) {
            return;
        }

        // Check if already added
        foreach ($this->additionalItems as $addedItem) {
            if ($addedItem['invoicable_item_id'] == $itemId) {
                $this->errorMessage = 'Item already added';
                return;
            }
        }

        $this->additionalItems[] = [
            'invoicable_item_id' => $item->id,
            'item_code' => $item->item_code,
            'item_name' => $item->item_name,
            'unit_price' => $item->unit_price,
            'unit_cost' => $item->unit_cost,
            'quantity' => 1,
        ];

        $this->closeAddItemModal();
    }

    public function removeAdditionalItem($index): void
    {
        unset($this->additionalItems[$index]);
        $this->additionalItems = array_values($this->additionalItems);
    }

    public function getNonAnalysisItemsProperty()
    {
        // Show all active items - Service, Inventory, and Non-Inventory
        $query = InvoicableItem::where('active', 1);

        if ($this->additionalItemSearch) {
            $query->where(function($q) {
                $q->where('item_code', 'like', "%{$this->additionalItemSearch}%")
                  ->orWhere('item_name', 'like', "%{$this->additionalItemSearch}%")
                  ->orWhere('description', 'like', "%{$this->additionalItemSearch}%")
                  ->orWhere('item_type', 'like', "%{$this->additionalItemSearch}%");
            });
        }

        return $query->orderBy('item_name')->get(); // Show all items, no limit
    }

    public function getAdditionalItemsTotalProperty()
    {
        $total = 0;
        foreach ($this->additionalItems as $item) {
            $total += $item['unit_price'] * $item['quantity'];
        }
        return $total;
    }

    // Step 5: Review & Generate
    public function buildInvoicePreview(): void
    {
        $preview = [];

        // Add analysis items
        foreach ($this->analysisMappings as $analysisTypeId => $itemId) {
            $item = $this->getInvoicableItemById($itemId);
            $analysisData = $this->analysisTypesData[$analysisTypeId];
            
            $preview[] = [
                'type' => 'analysis',
                'description' => $analysisData['name'] . ' (' . $analysisData['code'] . ')',
                'quantity' => $analysisData['count'],
                'unit_price' => $item->unit_price,
                'total' => $item->unit_price * $analysisData['count'],
            ];
        }

        // Add additional items
        foreach ($this->additionalItems as $item) {
            $preview[] = [
                'type' => 'additional',
                'description' => $item['item_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['unit_price'] * $item['quantity'],
            ];
        }

        $this->dispatch('invoicePreviewUpdated', $preview);
    }

    public function getInvoicePreviewProperty()
    {
        $preview = [];

        // Add analysis items
        foreach ($this->analysisMappings as $analysisTypeId => $itemId) {
            $item = $this->getInvoicableItemById($itemId);
            if (!$item) continue;
            
            $analysisData = $this->analysisTypesData[$analysisTypeId];
            // Use custom price if set, otherwise use item price
            $unitPrice = $this->analysisCustomPrices[$analysisTypeId] ?? $item->unit_price;
            
            $preview[] = [
                'type' => 'analysis',
                'description' => $analysisData['name'] . ' (' . $analysisData['code'] . ')',
                'quantity' => $analysisData['count'],
                'unit_price' => $unitPrice,
                'total' => $unitPrice * $analysisData['count'],
            ];
        }

        // Add additional items
        foreach ($this->additionalItems as $item) {
            $preview[] = [
                'type' => 'additional',
                'description' => $item['item_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['unit_price'] * $item['quantity'],
            ];
        }

        return $preview;
    }

    public function getSubtotalProperty()
    {
        return array_sum(array_column($this->invoicePreview, 'total'));
    }

    public function getTaxRateProperty()
    {
        $taxRegime = TaxRegime::where('active', 1)->first();
        return $taxRegime ? $taxRegime->value : 0;
    }

    public function getTaxAmountProperty()
    {
        return $this->subtotal * ($this->taxRate / 100);
    }

    public function getGrandTotalProperty()
    {
        return $this->subtotal + $this->taxAmount;
    }

    public function getNextInvoiceNumberProperty()
    {
        $lastInvoice = Invoice::orderBy('id', 'desc')->first();
        $nextId = $lastInvoice ? $lastInvoice->id + 1 : 1;
        return str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    public function generateSalesOrder()
    {
        $this->validationErrors = [];

        try {
            DB::beginTransaction();

            // Update customer Zoho data if needed
            if ($this->updateCustomerZohoData) {
                $this->customer->zoho_id = $this->zohoCustomerId;
                $this->customer->currency_id = $this->zohoCurrencyId;
                $this->customer->zoho_currency_id = $this->zohoCurrencyId;
                $this->customer->save();
            }

            // Create Invoice header
            $invoice = new Invoice();
            $invoice->customer_id = $this->customerId;
            $invoice->currency_id = $this->zohoCurrencyId;
            $invoice->pricelist_id = null;
            $invoice->save();

            // Generate invoice number: FV-S-XXXX
            $invoice->invoice_number = 'FV-S-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);

            // Calculate due date
            $creditDays = $this->customer->credit_days ?? 30;
            $invoice->due_date = now()->addDays($creditDays);

            // Create invoice details from analysis mappings
            foreach ($this->analysisMappings as $analysisTypeId => $itemId) {
                $invoicableItem = InvoicableItem::find($itemId);
                $analysisData = $this->analysisTypesData[$analysisTypeId];
                
                // Check if custom price is set, otherwise get price with currency conversion
                if (isset($this->analysisCustomPrices[$analysisTypeId])) {
                    $unitPrice = $this->analysisCustomPrices[$analysisTypeId];
                    $unitCost = $invoicableItem->unit_cost ?? 0;
                } else {
                    $priceData = getPriceForAnalysisType($analysisTypeId, $this->zohoCurrencyId);
                    $unitPrice = $priceData['unit_price'] ?? $invoicableItem->unit_price;
                    $unitCost = $priceData['unit_cost'] ?? $invoicableItem->unit_cost;
                }

                // Get batch and sample IDs for this analysis type
                $batchIds = array_column($this->batchData, 'id');
                $relations = SampleAnalysisTypeRelation::whereIn('batch_id', $batchIds)
                    ->where('analysis_type_id', $analysisTypeId)
                    ->get();

                $sampleHeaderIds = $relations->pluck('batch_id')->unique()->implode(',');
                $sampleDetailIds = $relations->pluck('sample_detail_id')->unique()->implode(',');

                InvoiceDetails::create([
                    'invoice_id' => $invoice->id,
                    'analysis_type' => $analysisTypeId,
                    'analysis_type_name' => $analysisData['name'],
                    'invoicable_item_id' => $itemId,
                    'quantity' => $analysisData['count'],
                    'selling_price' => $unitPrice,
                    'cost_price' => $unitCost,
                    'selling_amount' => $unitPrice,
                    'total' => $unitPrice * $analysisData['count'],
                    'sample_header_id' => $sampleHeaderIds,
                    'sample_detail_id' => $sampleDetailIds,
                    'crm_customer_id' => $this->customerId,
                    'tax_rate' => '0',
                    'tax_amount' => 0,
                ]);
            }

            // Create invoice details for additional items
            foreach ($this->additionalItems as $item) {
                InvoiceDetails::create([
                    'invoice_id' => $invoice->id,
                    'invoicable_item_id' => $item['invoicable_item_id'],
                    'quantity' => $item['quantity'],
                    'selling_price' => $item['unit_price'],
                    'cost_price' => $item['unit_cost'],
                    'selling_amount' => $item['unit_price'],
                    'total' => $item['unit_price'] * $item['quantity'],
                    'analysis_type_name' => $item['item_name'],
                    'crm_customer_id' => $this->customerId,
                    'tax_rate' => '0',
                    'tax_amount' => 0,
                    'analysis_type' => '0',
                ]);
            }

            // Calculate totals with tax
            $taxRate = $this->taxRate;
            $details = InvoiceDetails::where('invoice_id', $invoice->id)->get();
            
            foreach ($details as $detail) {
                $tax = ($taxRate / 100) * $detail->selling_price * $detail->quantity;
                $detail->tax_rate = strval($taxRate);
                $detail->tax_amount = $tax;
                $detail->selling_amount = $detail->selling_price + ($tax / $detail->quantity);
                $detail->total = ($detail->selling_price * $detail->quantity) + $tax;
                $detail->save();
            }

            $invoice->total = InvoiceDetails::where('invoice_id', $invoice->id)->sum('total');
            $invoice->total_tax = InvoiceDetails::where('invoice_id', $invoice->id)->sum('tax_amount');
            $invoice->save();

            // Link batches to invoice
            SampleHeader::whereIn('batch_code', $this->selectedBatches)
                ->update(['invoice_id' => $invoice->id]);

            DB::commit();

            // Redirect to invoice view
            session()->flash('success', 'Sales Order ' . $invoice->invoice_number . ' generated successfully!');
            return redirect()->route('invoice-sample-header', ['id' => $invoice->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Error generating sales order: ' . $e->getMessage();
            $this->validationErrors[] = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.billing.sales-order-wizard', [
            'filteredZohoCustomers' => $this->filteredZohoCustomers,
            'filteredCurrencies' => $this->filteredCurrencies,
            'selectedZohoCustomer' => $this->selectedZohoCustomer,
            'selectedZohoCurrency' => $this->selectedZohoCurrency,
            'nonAnalysisItems' => $this->nonAnalysisItems,
            'analysisTotal' => $this->analysisTotal,
            'unmappedAnalysisCount' => $this->unmappedAnalysisCount,
            'additionalItemsTotal' => $this->additionalItemsTotal,
            'invoicePreview' => $this->invoicePreview,
            'subtotal' => $this->subtotal,
            'taxRate' => $this->taxRate,
            'taxAmount' => $this->taxAmount,
            'grandTotal' => $this->grandTotal,
            'nextInvoiceNumber' => $this->nextInvoiceNumber,
        ]);
    }
}
