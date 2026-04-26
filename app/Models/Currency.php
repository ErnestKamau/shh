<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Currency extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'code',
        'description',
        'iso_code',
        'iso_numeric_code',
        // Exchange Rate Information
        'exchange_rate_date',
        'exchange_rate_amt',
        'currency_factor',
        // EMU Currency
        'emu_currency',
        // GL Accounts
        'realized_gains_acc',
        'realized_losses_acc',
        'unrealized_gains_acc',
        'unrealized_losses_acc',
        'realized_gl_gains_account',
        'realized_gl_losses_account',
        'residual_gains_account',
        'residual_losses_account',
        // Rounding Settings
        'amount_rounding_precision',
        'amount_decimal_places',
        'invoice_rounding_precision',
        'invoice_rounding_type',
        'unit_amount_rounding_precision',
        'unit_amount_decimal_places',
        'appln_rounding_precision',
        // Conversion Rounding Accounts
        'conv_lcy_rndg_debit_acc',
        'conv_lcy_rndg_credit_acc',
        // VAT Settings
        'max_vat_difference_allowed',
        'vat_rounding_type',
        // Payment Tolerance
        'payment_tolerance_percent',
        'max_payment_tolerance_amount',
        // Date Tracking
        'last_date_adjusted',
        'last_date_modified',
        // CRM Integration
        'coupled_to_crm',
        'coupled_to_dataverse',
        // Application Status
        'active',
    ];

    protected $casts = [
        'exchange_rate_date' => 'date',
        'exchange_rate_amt' => 'decimal:10',
        'currency_factor' => 'decimal:10',
        'emu_currency' => 'boolean',
        'amount_rounding_precision' => 'decimal:5',
        'invoice_rounding_precision' => 'decimal:5',
        'unit_amount_rounding_precision' => 'decimal:5',
        'appln_rounding_precision' => 'decimal:5',
        'max_vat_difference_allowed' => 'decimal:2',
        'payment_tolerance_percent' => 'decimal:4',
        'max_payment_tolerance_amount' => 'decimal:2',
        'last_date_adjusted' => 'date',
        'last_date_modified' => 'date',
        'coupled_to_crm' => 'boolean',
        'coupled_to_dataverse' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Get the invoices using this currency.
     */
    public function invoices()
    {
        return $this->hasMany(\App\Invoice::class, 'currency_id');
    }

    /**
     * Get the quotations using this currency.
     */
    public function quotations()
    {
        return $this->hasMany(\App\QuotationHeader::class, 'currency_id');
    }

    /**
     * Get the invoicable items using this currency.
     */
    public function invoicableItems()
    {
        return $this->hasMany(\App\InvoicableItem::class, 'currency_id');
    }
}
