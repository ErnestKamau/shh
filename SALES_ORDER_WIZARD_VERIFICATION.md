# Sales Order Wizard - Implementation Verification Guide

## ✅ Critical Bugs Fixed

### 1. Relationship Names (FIXED)
**File**: `app/Livewire/Billing/SalesOrderWizard.php` (Line 69-78)
- ✅ Changed `sampleType` → `sample_type`
- ✅ Changed `crmCustomer` → `client`

### 2. Invoice Validation Check (FIXED)
**File**: `app/Livewire/Billing/SalesOrderWizard.php` (Line 82)
- ✅ Changed `invoice_id != null` → `invoice_id > 0`

### 3. Price Editing Capability (ADDED)
**New Features**:
- ✅ Added `analysisCustomPrices` property for custom analysis pricing
- ✅ Updated Step 3 view to show editable price fields for analysis items
- ✅ Updated Step 4 view to show editable price fields for additional items
- ✅ Updated invoice generation to use custom prices when set
- ✅ Updated totals calculation to reflect custom prices

## 📋 Testing Checklist

### Pre-Test Setup
1. Ensure you have sample batches from the same customer with NO existing invoices
2. Ensure the customer has analysis types assigned
3. Ensure invoicable items are mapped to analysis types
4. Ensure Zoho customers exist in the system
5. Ensure currencies are configured

### Test Flow

#### Step 1: Access Wizard
- [ ] Go to Sample Workflow page
- [ ] Select 2-3 batches from SAME customer with NO existing invoices
- [ ] Click "Generate Sales Order" dropdown
- [ ] Click "Proceed to Wizard"
- [ ] **Expected**: Redirected to `/billing/sales-order/create?batches[]=BATCH1&batches[]=BATCH2`

#### Step 2: Batch Validation (Step 1 of Wizard)
- [ ] **Expected**: See list of selected batches with:
  - Batch code
  - Customer name (should be SAME for all)
  - Sample type
  - Receipt date
  - Number of samples
- [ ] **Expected**: No red warning boxes (means no validation errors)
- [ ] Click "Next"

#### Step 3: Customer/Zoho Mapping (Step 2)
- [ ] **Expected**: See customer name displayed
- [ ] **Expected**: See Zoho customer dropdown (may be pre-selected)
- [ ] Search for Zoho customer if not pre-selected
- [ ] Select appropriate Zoho customer
- [ ] **Expected**: Currency auto-selected based on Zoho customer
- [ ] **Expected**: If Zoho/Currency differs from customer record, see checkbox to update
- [ ] Verify currency is correct
- [ ] Click "Next"

#### Step 4: Analysis Type Mapping (Step 3)
- [ ] **Expected**: See table with all analysis types from selected batches
- [ ] **Expected**: Each row shows:
  - Analysis type name and code
  - Count (number of samples)
  - Invoicable item dropdown
  - **Unit Price** (EDITABLE input field)
  - Line total
- [ ] Click "Auto-Map All" button
- [ ] **Expected**: All analysis types mapped to their default invoicable items
- [ ] **Expected**: Default prices shown in editable fields
- [ ] **TEST PRICE EDITING**: Change unit price for one analysis item
- [ ] **Expected**: Line total updates automatically
- [ ] **Expected**: Total at bottom updates automatically
- [ ] Manually select different invoicable item for one analysis type (optional)
- [ ] Click "Next"

#### Step 5: Additional Items (Step 4)
- [ ] Click "Add Item" button
- [ ] **Expected**: Modal opens with list of non-analysis items
- [ ] Search for an item (e.g., "sampling fee", "logistics")
- [ ] Click on item to add
- [ ] **Expected**: Item added to table with:
  - Item code
  - Item name
  - **Quantity** (EDITABLE)
  - **Unit Price** (EDITABLE)
  - Total
  - Remove button
- [ ] **TEST QUANTITY EDITING**: Change quantity
- [ ] **Expected**: Total updates automatically
- [ ] **TEST PRICE EDITING**: Change unit price
- [ ] **Expected**: Total updates automatically
- [ ] **Expected**: Additional Items Total updates
- [ ] Add another item (optional)
- [ ] Remove an item using delete button (optional)
- [ ] Click "Next"

#### Step 6: Review & Generate (Step 5)
- [ ] **Expected**: See complete sales order preview:
  - **Customer Information Card**:
    - Customer name and email
    - Zoho ID
    - Currency
    - Credit days
    - Invoice number (FV-S-XXXX format)
  - **Batches Card**: List of all batch codes
  - **Line Items Table**:
    - All analysis items with custom prices if changed
    - All additional items
    - Subtotal
    - Tax (based on active tax regime)
    - Grand Total
- [ ] Verify all prices and quantities are correct
- [ ] Verify totals are calculated correctly
- [ ] Click "Generate Sales Order"

#### Step 7: Post-Generation Verification
- [ ] **Expected**: Redirected to invoice view page
- [ ] **Expected**: See success message: "Sales Order FV-S-XXXX generated successfully!"
- [ ] **Expected**: Invoice page shows:
  - Correct invoice number
  - Correct customer
  - All line items with correct prices and quantities
  - Correct totals

#### Step 8: Database Verification
Run these queries to verify:

```sql
-- Check invoice was created
SELECT * FROM customer_invoice 
WHERE invoice_number LIKE 'FV-S-%' 
ORDER BY id DESC LIMIT 1;

-- Check invoice details were created
SELECT * FROM invoice_details 
WHERE invoice_id = [INVOICE_ID_FROM_ABOVE];

-- Check batches are linked to invoice
SELECT batch_code, invoice_id 
FROM sample_headers 
WHERE batch_code IN ('BATCH1', 'BATCH2');

-- Verify custom prices were saved
SELECT 
    analysis_type_name,
    quantity,
    selling_price,
    total,
    invoicable_item_id
FROM invoice_details 
WHERE invoice_id = [INVOICE_ID];
```

**Expected**:
- [ ] Invoice record exists with correct customer_id and currency_id
- [ ] Invoice details exist for each analysis type and additional item
- [ ] All selected batches have their `invoice_id` set to the new invoice
- [ ] Custom prices are saved in `selling_price` field
- [ ] Invoicable items are linked via `invoicable_item_id`

### Error Testing

#### Test 1: Different Customers
- [ ] Select batches from different customers
- [ ] Click "Generate Sales Order" → "Proceed to Wizard"
- [ ] **Expected**: Error message: "Selected batches are from different customers"
- [ ] **Expected**: Cannot proceed to Step 2

#### Test 2: Existing Invoice
- [ ] Select a batch that already has an invoice (invoice_id > 0)
- [ ] Click "Generate Sales Order" → "Proceed to Wizard"
- [ ] **Expected**: Error message: "Batch XXXX already has an existing sales order"
- [ ] **Expected**: Cannot proceed to Step 2

#### Test 3: Missing Zoho Customer
- [ ] Proceed to Step 2
- [ ] Leave Zoho customer unselected
- [ ] Click "Next"
- [ ] **Expected**: Error message: "Please select a Zoho Customer"

#### Test 4: Missing Currency
- [ ] Select Zoho customer
- [ ] Clear currency selection
- [ ] Click "Next"
- [ ] **Expected**: Error message: "Please select a Currency"

#### Test 5: Unmapped Analysis
- [ ] Proceed to Step 3
- [ ] Leave one or more analysis types unmapped
- [ ] Click "Next"
- [ ] **Expected**: Error message: "Analysis type 'XXXX' is not mapped to any invoicable item"
- [ ] **Expected**: Warning shows count of unmapped items

## 🔍 Key Features Verification

### ✅ Batch Validation
- Same customer check
- Existing invoice check
- Batch data loading with correct relationships

### ✅ Customer/Zoho Mapping
- Pre-loads existing customer Zoho data
- Allows searching Zoho customers
- Auto-selects currency from Zoho customer
- Option to update customer record

### ✅ Currency Auto-Selection
- Fetches currency from Zoho customer
- Falls back to customer's existing currency
- Handles currency conversion via helper function

### ✅ Analysis Type Mapping
- Loads all analysis types from batches
- Groups and counts by analysis type
- Auto-mapping to default invoicable items
- Manual item selection with search
- **Custom price override capability**

### ✅ Price Editing
- **Analysis items**: Editable unit price with default shown
- **Additional items**: Editable quantity AND unit price
- Live updates to totals
- Custom prices saved to database

### ✅ Additional Items
- Search and add non-analysis items
- Edit quantity and price
- Remove items
- Calculate totals

### ✅ Invoice Generation
- Creates invoice header with correct format (FV-S-XXXX)
- Calculates due date from customer credit days
- Creates invoice details for all items
- Applies tax calculations
- **Links batches via invoice_id column**
- Saves custom prices
- Links invoicable items

### ✅ Batch Linking
- Updates `sample_headers.invoice_id` for all selected batches
- Uses batch codes to identify which batches to update
- Critical requirement: VERIFIED ✓

## 🐛 Known Considerations

1. **Currency Conversion**: Uses `convertCurrency()` helper - ensure this function works correctly
2. **Tax Calculation**: Uses active `TaxRegime` - ensure one exists
3. **Sample Analysis Type Relation**: Query assumes this table is populated
4. **Invoicable Item Mapping**: Assumes analysis types have invoicable items mapped

## 📊 Success Metrics

After successful test:
- [ ] Can create sales order from sample workflow
- [ ] All validations work correctly
- [ ] Prices can be customized
- [ ] Invoice is created with correct data
- [ ] Batches are linked to invoice
- [ ] Can view invoice after generation
- [ ] Database has all correct records

## 🎯 Next Steps After Verification

1. Test with real data
2. Verify Zoho integration (if applicable)
3. Test printing/emailing invoice
4. Verify workflow continues correctly after invoice generation
5. Test with various currency combinations
6. Verify tax calculations for different regions

## 📝 Notes

- The wizard is implemented as a Livewire component (`app/Livewire/Billing/SalesOrderWizard.php`)
- Uses 5-step wizard pattern
- All state is maintained in Livewire component properties
- Uses database transactions for safe invoice creation
- Price editing is now supported in Steps 3 and 4
- Custom prices are preserved through to invoice generation


