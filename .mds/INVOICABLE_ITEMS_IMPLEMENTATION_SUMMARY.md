# Invoicable Items Billing System - Implementation Summary

## Overview

Successfully migrated from the pricelist-based billing system to a modern invoicable items system with Livewire components. The system now uses a centralized invoicable items table for all billing operations with flexible many-to-one mapping between analysis types and items.

---

## What Was Implemented

### 1. Database Schema ✅

#### New Tables Created:

**`invoicable_items`** - Central repository for all billable items
```sql
- id (primary key)
- item_code (unique) - e.g., "LAB-MICRO-001"
- item_name - Display name of the item
- description - Detailed description
- item_type - e.g., "Service", "Inventory"
- item_category_code - Category classification
- unit_price (decimal 15,2) - Selling price
- unit_cost (decimal 15,2) - Cost price
- currency_id - References module_pre_configs
- price_includes_tax (boolean)
- tax_group_code - Tax classification
- base_unit_of_measure - e.g., "PCS", "KG"
- gtin - Barcode/GTIN
- blocked (boolean) - Block from use
- active (boolean) - Active status
- timestamps
```

**`analysis_type_invoicable_item`** - Many-to-many mapping table
```sql
- id
- analysis_type_id - References analysis_types
- invoicable_item_id - References invoicable_items
- timestamps
- UNIQUE index on (analysis_type_id, invoicable_item_id)
```

#### Tables Updated:

- **`invoice_details`** - Added `invoicable_item_id` (nullable)
- **`quotation_details`** - Added `invoicable_item_id` (nullable)

#### Tables Removed:

- `pricelists` - DROPPED
- `pricelist_items` - DROPPED
- `pricelist_customers` - DROPPED
- `zoho_items_pricelist` - DROPPED

---

### 2. Models & Relationships ✅

#### New Model Created:

**`App\InvoicableItem`**
- Implements Auditable trait for change tracking
- Relationships:
  - `analysisTypes()` - Many-to-many with AnalysisType
  - `currency()` - BelongsTo ModulePreConfigs
- Methods:
  - `convertPrice($targetCurrencyId)` - Convert price to different currency
  - `getFormattedPriceAttribute()` - Get formatted price with currency

#### Models Updated:

**`App\AnalysisType`**
- Added `invoicableItems()` - Many-to-many relationship
- Added `defaultInvoicableItem()` - Get first mapped item

**`App\InvoiceDetails`**
- Added `invoicable_item_id` to fillable
- Added `invoicableItem()` - BelongsTo relationship

**`App\QuotationDetails`**
- Added fillable array
- Added `invoicable_item_id` to fillable
- Added `invoicableItem()` - BelongsTo relationship

#### Models Deleted:

- `App\Pricelist` - REMOVED
- `App\PricelistItem` - REMOVED
- `App\PricelistCustomer` - REMOVED

---

### 3. Helper Functions ✅

**`app/Helpers/CurrencyHelper.php`**
```php
function convertCurrency($amount, $fromCurrencyId, $toCurrencyId)
// Uses currency_conversions table
// Handles direct and reverse conversions

function getCurrencySymbol($currencyId)
// Get currency name from module_pre_configs
```

**`app/Helpers/BillingHelper.php`**
```php
function getPriceForAnalysisType($analysisTypeId, $targetCurrencyId = null)
// Returns: unit_price, unit_cost, invoicable_item_id, currency_id, item_name, etc.
// Automatically converts currency if target currency specified

function getInvoicableItemsForSelect($search = null, $limit = 50)
// Returns formatted collection for select dropdowns
```

---

### 4. Livewire Components ✅

#### **InvoicableItemManager** (`/billing/invoicable-items`)

**Features:**
- Complete CRUD for invoicable items
- Search by code, name, or description
- Filter by item type, currency, and status
- Pagination with configurable items per page
- Create/Edit modal with all fields
- Clone items functionality
- Prevent deletion if item is used in invoices/quotations
- Modern card-based UI with tag-style dropdowns
- Currency selection with searchable dropdown

**File Locations:**
- Component: `app/Livewire/Billing/InvoicableItemManager.php`
- View: `resources/views/livewire/billing/invoicable-item-manager.blade.php`
- Layout: `resources/views/layouts/billing/invoicable-items-index.blade.php`

#### **InvoiceManager** (`/billing/invoices`)

**Features:**
- List all invoices with advanced filters
- Filter by customer, currency, date range
- View invoice details in modal
- Show invoice line items with invoicable items
- Calculate and display subtotals, tax, and totals
- Link to print and full detail views
- Overdue indicator for past due invoices
- Modern responsive UI

**File Locations:**
- Component: `app/Livewire/Billing/InvoiceManager.php`
- View: `resources/views/livewire/billing/invoice-manager.blade.php`
- Layout: `resources/views/layouts/billing/invoices-index.blade.php`

#### **QuotationManager** (`/billing/quotations`)

**Features:**
- List quotations with filters (customer, stage, type, date)
- View draft quotations separately
- View quotation details in modal
- Show line items with invoicable item mapping
- Status badges (Complete, In Preparation, In Approval)
- Expiration date tracking
- Links to existing edit and print functions
- Clone and delete functionality

**File Locations:**
- Component: `app/Livewire/Billing/QuotationManager.php`
- View: `resources/views/livewire/billing/quotation-manager.blade.php`
- Layout: `resources/views/layouts/billing/quotations-index.blade.php`

#### **AnalysisTypeManager** - Enhanced

**New Feature Added:**
- Invoicable item selection field in analysis type form
- Searchable tag-style dropdown showing:
  - Item code, name, and unit price
  - Real-time search filtering
- Automatically syncs to `analysis_type_invoicable_item` table on save
- Shows currently mapped item when editing
- Allows clearing the mapping

**Files Modified:**
- Component: `app/Livewire/Analysis/AnalysisTypeManager.php`
- View: `resources/views/livewire/analysis/analysis-type-manager.blade.php`

---

### 5. Controllers Updated ✅

#### **InvoiceController**

**`generateinvoice()` method refactored:**
- Removed pricelist dependencies
- Now uses `getPriceForAnalysisType()` helper
- Automatically gets price from mapped invoicable item
- Converts currency if customer currency differs from item currency
- Applies tax based on `price_includes_tax` flag
- Stores `invoicable_item_id` in invoice_details

**Import cleanup:**
- Removed: PricelistCustomer, Pricelist, PricelistItem

#### **QuotationController**

**Methods updated:**
- `view_quote_header_detail()` - Removed pricelist lookup
- `edit_quotation_header()` - No longer requires customer to be in pricelist
- `clone_quotation()` - Sets pricelist_id to null

**Import cleanup:**
- Removed: PricelistCustomer, Pricelist, PricelistItem

---

### 6. Routes ✅

#### New Routes Added:
```php
GET /billing/invoicable-items  -> InvoicableItemManager component
GET /billing/invoices          -> InvoiceManager component  
GET /billing/quotations        -> QuotationManager component
```

#### Routes Deprecated (Commented Out):
```php
// All pricelist routes (11 routes total)
// /pricelists, /pricelist/{id}, /pricelist/{id}/item, etc.
```

---

### 7. Sample Data ✅

Created via tinker:
- 1 item from `dynamicJsons/items.json` (ATHENS Desk)
- 3 default lab service items:
  - LAB-MICRO-001: Microbiological Analysis (500.00)
  - LAB-CHEM-001: Chemical Analysis (750.00)
  - LAB-PHYS-001: Physical Testing (400.00)

---

## How It Works

### Pricing Flow

1. **Analysis Type ↔ Invoicable Item Mapping**
   - When creating/editing an analysis type, select an invoicable item from the dropdown
   - Multiple analysis types can share the same invoicable item
   - Mapping is stored in `analysis_type_invoicable_item` table

2. **Invoice Generation**
   ```
   Sample Batch → Analysis Types → Invoicable Items → Prices
   
   - System looks up mapped invoicable item for each analysis type
   - Gets unit_price and unit_cost from invoicable item
   - Converts to customer's currency if different
   - Applies tax if price doesn't include it
   - Creates invoice_details with invoicable_item_id
   ```

3. **Currency Conversion**
   ```
   Uses currency_conversions table:
   - Direct conversion: currency_1 → currency_2 (multiply by ratio)
   - Reverse conversion: currency_2 → currency_1 (divide by ratio)
   - No conversion if currencies match
   ```

---

## Usage Guide

### Managing Invoicable Items

1. **Access:** Navigate to `/billing/invoicable-items`

2. **Create New Item:**
   - Click "Add Invoicable Item"
   - Fill in required fields:
     - Item Code (unique)
     - Item Name
     - Unit Price
     - Unit Cost
     - Currency (searchable dropdown)
   - Optional fields:
     - Description
     - Item Type (Service, Inventory, etc.)
     - Category Code
     - Tax Group Code
     - Unit of Measure
     - GTIN/Barcode
   - Toggle options:
     - Price Includes Tax
     - Blocked
     - Active
   - Click "Save"

3. **Edit Item:**
   - Click pencil icon on any item
   - Modify fields
   - Save changes

4. **Clone Item:**
   - Click duplicate icon
   - Creates copy with "-COPY" suffix
   - Set to inactive by default

### Mapping Analysis Types to Invoicable Items

1. **Access:** Navigate to `/livewire/analysis-types/{sampleTypeId}`

2. **When Creating/Editing Analysis Type:**
   - Scroll to "Invoicable Item" field
   - Click in the field to open searchable dropdown
   - Type to search by item code or name
   - Dropdown shows: `CODE - NAME (PRICE)`
   - Click to select
   - Mapping is automatically saved when you save the analysis type

3. **Clear Mapping:**
   - Click the X icon on the selected item badge
   - Save the analysis type

### Generating Invoices

**Process remains the same:**
1. Complete sample batch analysis
2. Navigate to batch details
3. Click "Generate Invoice"
4. System automatically:
   - Finds mapped invoicable items for each analysis type
   - Gets prices (with currency conversion if needed)
   - Applies tax based on active tax regime
   - Creates invoice with proper line items

**Changes:**
- Prices now come from invoicable items instead of pricelists
- No need to assign customers to pricelists
- Currency conversion happens automatically

### Managing Invoices

1. **Access:** Navigate to `/billing/invoices`

2. **Filter Options:**
   - Search by invoice number or reference
   - Filter by customer
   - Filter by currency
   - Filter by date (invoice date or due date)
   - Adjust date range

3. **View Invoice:**
   - Click eye icon to view details in modal
   - Shows customer info, line items, totals
   - Displays mapped invoicable items

4. **Print/Email:**
   - Use existing print and email functions
   - Accessible from action buttons

### Managing Quotations

1. **Access:** Navigate to `/billing/quotations`

2. **Features:**
   - View draft quotations separately
   - Filter by customer, stage, type, date
   - View quotation details
   - Access edit and print functions
   - Clone quotations
   - Convert to batch

---

## Currency Management

### Exchange Rates Table

The existing `currency_conversions` table stores exchange rates:
```sql
- id
- currency_1 (from currency)
- currency_2 (to currency)  
- ratio (conversion rate)
- timestamps
```

**Example:**
- USD → KES: ratio = 130.00 (1 USD = 130 KES)
- KES → USD: system calculates reverse (1/130)

### Adding Exchange Rates

Use tinker or create a management interface:
```php
DB::table('currency_conversions')->insert([
    'currency_1' => 285, // AED
    'currency_2' => 286, // USD
    'ratio' => 3.67,
    'created_at' => now(),
    'updated_at' => now(),
]);
```

---

## Migration Path (Completed)

### What Changed:

1. ✅ Created `invoicable_items` table
2. ✅ Created `analysis_type_invoicable_item` mapping table
3. ✅ Added `invoicable_item_id` to invoice_details and quotation_details
4. ✅ Dropped pricelist tables (pricelists, pricelist_items, pricelist_customers, zoho_items_pricelist)
5. ✅ Deleted pricelist models and controller
6. ✅ Deleted pricelist views
7. ✅ Commented out pricelist routes
8. ✅ Updated invoice and quotation generation logic

### What Stayed:

- Existing invoice and quotation workflows
- Sample batch processing
- Tax regime system
- Payment tracking
- PDF generation
- Email notifications

---

## Files Created

### Migrations (5):
1. `2025_10_22_065338_create_invoicable_items_table.php`
2. `2025_10_22_065341_create_analysis_type_invoicable_item_table.php`
3. `2025_10_22_065342_add_invoicable_item_to_invoice_details.php`
4. `2025_10_22_065344_add_invoicable_item_to_quotation_details.php`
5. `2025_10_22_071349_drop_pricelist_tables.php`

### Models (1):
1. `app/InvoicableItem.php`

### Helper Files (2):
1. `app/Helpers/CurrencyHelper.php`
2. `app/Helpers/BillingHelper.php`

### Livewire Components (3):
1. `app/Livewire/Billing/InvoicableItemManager.php`
2. `app/Livewire/Billing/InvoiceManager.php`
3. `app/Livewire/Billing/QuotationManager.php`

### Livewire Views (3):
1. `resources/views/livewire/billing/invoicable-item-manager.blade.php`
2. `resources/views/livewire/billing/invoice-manager.blade.php`
3. `resources/views/livewire/billing/quotation-manager.blade.php`

### Layout Views (3):
1. `resources/views/layouts/billing/invoicable-items-index.blade.php`
2. `resources/views/layouts/billing/invoices-index.blade.php`
3. `resources/views/layouts/billing/quotations-index.blade.php`

### Seeders (1):
1. `database/seeds/InvoicableItemSeeder.php`

---

## Files Modified

1. `app/AnalysisType.php` - Added invoicable item relationships
2. `app/InvoiceDetails.php` - Added invoicable_item_id and relationship
3. `app/QuotationDetails.php` - Added fillable, invoicable_item_id and relationship
4. `app/Livewire/Analysis/AnalysisTypeManager.php` - Added invoicable item field
5. `resources/views/livewire/analysis/analysis-type-manager.blade.php` - Added invoicable item dropdown
6. `app/Http/Controllers/Invoice/InvoiceController.php` - Refactored to use invoicable items
7. `app/Http/Controllers/Invoice/QuotationController.php` - Removed pricelist dependencies
8. `routes/web.php` - Added new routes, commented out pricelist routes
9. `composer.json` - Added helper files to autoload

---

## Files Deleted

1. `app/Pricelist.php`
2. `app/PricelistItem.php`
3. `app/PricelistCustomer.php`
4. `app/Http/Controllers/PricelistItemController.php`
5. `resources/views/layouts/lab/pricelist/index.blade.php`
6. `resources/views/layouts/lab/pricelist/print.blade.php`
7. `resources/views/layouts/lab/pricelist/show.blade.php`
8. Directory: `resources/views/layouts/lab/pricelist/` (removed)

---

## Testing the Implementation

### 1. Test Invoicable Items Management

```bash
# Visit the page
/billing/invoicable-items

# Verify:
- Can see 4 items (ATHENS Desk + 3 lab services)
- Can create new items
- Can edit existing items
- Can filter and search
- Currency dropdown works
```

### 2. Test Analysis Type Mapping

```bash
# Visit analysis types
/livewire/analysis-types/{sampleTypeId}

# Test:
- Create or edit an analysis type
- Find "Invoicable Item" field
- Search for an item
- Select and save
- Verify mapping in database:
SELECT * FROM analysis_type_invoicable_item;
```

### 3. Test Invoice Generation

```bash
# Prerequisites:
1. Map at least one analysis type to an invoicable item
2. Have a sample batch with that analysis type

# Test:
1. Navigate to sample batch
2. Click "Generate Invoice"
3. Verify invoice is created with:
   - Correct prices from invoicable item
   - Currency conversion (if applicable)
   - Tax applied correctly
   - invoicable_item_id populated in invoice_details
```

### 4. Test Currency Conversion

```php
// Via tinker:
php artisan tinker

// Test conversion function
convertCurrency(100, 285, 286);  // Convert 100 AED to USD

// Test price lookup with conversion
$priceData = getPriceForAnalysisType(1, 286); 
// Get price for analysis type 1 in currency 286
```

---

## Key Benefits

1. **Centralized Pricing** - Single source of truth for all item prices
2. **Flexible Mapping** - Multiple analysis types can use same invoicable item
3. **Multi-Currency Support** - Built-in currency conversion using exchange rates table
4. **Modern UI** - Livewire components with responsive, card-based design
5. **No Customer-Pricelist Assignment** - Simplified workflow, no need to assign customers to pricelists
6. **Searchable Dropdowns** - Easy item selection with tag-style interface
7. **Better Data Integrity** - Foreign key references, proper relationships
8. **Audit Trail** - All changes tracked via Auditable trait

---

## Next Steps (Optional Enhancements)

### Recommended:
1. **Create Currency Exchange Rate Manager** - Livewire component to manage currency_conversions table
2. **Bulk Mapping Tool** - Optional tool to map multiple analysis types at once
3. **Import/Export** - Bulk import/export of invoicable items via CSV/Excel
4. **Price History** - Track price changes over time
5. **Discount System** - Add discount fields to quotation_details
6. **Navigation Menu** - Add links to new pages in main navigation

### Update Navigation:
Find your main navigation file (likely in `resources/views/layouts/` or similar) and add:
```html
<li><a href="{{ route('billing.invoicable-items') }}">Invoicable Items</a></li>
<li><a href="{{ route('billing.invoices') }}">Invoices</a></li>
<li><a href="{{ route('billing.quotations') }}">Quotations</a></li>
```

Remove:
```html
<!-- Old pricelist links -->
```

---

## Important Notes

1. **Data Migration:** As per user request, old data was not migrated. Tables were truncated.

2. **Backwards Compatibility:** `pricelist_id` fields still exist in quotation_headers and customer_invoice tables (set to null). Can be removed in a future migration if desired.

3. **Currency Table:** Uses existing `currency_conversions` table. Ensure exchange rates are populated for multi-currency operations.

4. **Tax Handling:** System respects the `price_includes_tax` flag on invoicable items. If false, tax is calculated using active tax regime.

5. **Validation:** All analysis types should have an invoicable item mapped before generating invoices. The system skips unmapped analysis types with a continue statement.

---

## Troubleshooting

### Issue: "No price found for analysis type"
**Solution:** Map the analysis type to an invoicable item via `/livewire/analysis-types/{sampleTypeId}`

### Issue: "Currency conversion not working"
**Solution:** Add exchange rate in `currency_conversions` table

### Issue: "Invoicable items not showing in dropdown"
**Solution:** Ensure items are marked as `active = 1`

### Issue: "Livewire component not loading"
**Solution:** 
```bash
composer dump-autoload
php artisan config:clear
php artisan view:clear
```

---

## Success Metrics

✅ All migrations ran successfully
✅ No lint errors in new code
✅ Models created with proper relationships
✅ Helper functions autoloaded
✅ Livewire components functional
✅ Sample data created
✅ Pricelist system completely removed
✅ Invoice generation refactored
✅ Routes configured
✅ All TODOs completed

---

## Summary

The invoicable items billing system is now **fully implemented and operational**. The system provides a modern, flexible approach to pricing laboratory services with:

- ✅ Centralized item management
- ✅ Flexible many-to-one analysis type mapping
- ✅ Multi-currency support with automatic conversion
- ✅ Modern Livewire UI components
- ✅ Complete removal of legacy pricelist system
- ✅ Seamless integration with existing invoice/quotation workflows

The system is ready for use and testing!

