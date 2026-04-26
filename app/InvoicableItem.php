<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class InvoicableItem extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'item_code',
        'item_name',
        'description',
        'item_type',
        'item_category_code',
        'unit_price',
        'unit_cost',
        'currency_id',
        'price_includes_tax',
        'tax_group_code',
        'base_unit_of_measure',
        'gtin',
        'blocked',
        'active',
        // Inventory & Stock Information
        'inventory_field',
        'shelf_no',
        'costing_method',
        // Cost Information
        'standard_cost',
        'last_direct_cost',
        'overhead_rate',
        'indirect_cost_percent',
        // Pricing Information
        'price_profit_calculation',
        'profit_percent',
        // Posting Groups
        'inventory_posting_group',
        'gen_prod_posting_group',
        'vat_prod_posting_group',
        'item_disc_group',
        // Vendor Information
        'vendor_no',
        'vendor_item_no',
        // Additional Item Details
        'tariff_no',
        'search_description',
        'last_date_modified',
        // Units of Measure
        'sales_unit_of_measure',
        'purch_unit_of_measure',
        // Manufacturing & Replenishment
        'replenishment_system',
        'manufacturing_policy',
        'assembly_policy',
        'flushing_method',
        'item_tracking_code',
        'production_bom_no',
        'routing_no',
        'lead_time_calculation',
        // Boolean Flags
        'created_from_nonstock_item',
        'substitutes_exist',
        'stockkeeping_unit_exists',
        'assembly_bom',
        'cost_is_adjusted',
        'coupled_to_crm',
        'coupled_to_dataverse',
        // Deferral Template
        'default_deferral_template_code',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'standard_cost' => 'decimal:2',
        'last_direct_cost' => 'decimal:2',
        'overhead_rate' => 'decimal:4',
        'indirect_cost_percent' => 'decimal:4',
        'profit_percent' => 'decimal:4',
        'price_includes_tax' => 'boolean',
        'blocked' => 'boolean',
        'active' => 'boolean',
        'created_from_nonstock_item' => 'boolean',
        'substitutes_exist' => 'boolean',
        'stockkeeping_unit_exists' => 'boolean',
        'assembly_bom' => 'boolean',
        'cost_is_adjusted' => 'boolean',
        'coupled_to_crm' => 'boolean',
        'coupled_to_dataverse' => 'boolean',
        'last_date_modified' => 'date',
    ];

    /**
     * Get the analysis types that use this invoicable item.
     */
    public function analysisTypes()
    {
        return $this->belongsToMany(
            AnalysisType::class,
            'analysis_type_invoicable_item',
            'invoicable_item_id',
            'analysis_type_id'
        )->withTimestamps();
    }

    /**
     * Get the currency for this item.
     */
    public function currency()
    {
        return $this->belongsTo(\App\Models\Currency::class, 'currency_id');
    }

    /**
     * Convert the price to a target currency.
     *
     * @param int $targetCurrencyId
     * @return float
     */
    public function convertPrice(int $targetCurrencyId): float
    {
        if ($this->currency_id == $targetCurrencyId) {
            return (float) $this->unit_price;
        }

        return convertCurrency($this->unit_price, $this->currency_id, $targetCurrencyId);
    }

    /**
     * Get formatted price with currency.
     */
    public function getFormattedPriceAttribute(): string
    {
        $currency = $this->currency;
        $currencyName = $currency ? $currency->name : '';
        return number_format($this->unit_price, 2) . ' ' . $currencyName;
    }
}

