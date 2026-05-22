<?php

namespace App;

use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\CRM\CRMCustomer;
use App\Models\Currency;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use App\ModulePreConfigs;

class Invoice extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    protected $table = 'customer_invoice';
    protected $appends = ['batchcodes','samplecodes','invoicetotal'];

    protected $fillable = ['deleted_at','delete_reason'];

    public function crmCustomer(){
        return $this->belongsTo(CRMCustomer::class,'customer_id');
    }
    public function currencyinfo(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function pricelist(): BelongsTo
    {
        return $this->belongsTo(Pricelist::class, 'pricelist_id');
    }

    public function getDisplayCurrencyAttribute(): ?Currency
    {
        if ($this->hasValidPricelistId()) {
            $currency = $this->pricelist?->currency;
            if ($currency) {
                return $currency;
            }
        }

        if ($this->currency_id) {
            return $this->relationLoaded('currencyinfo')
                ? $this->currencyinfo
                : $this->currencyinfo()->first();
        }

        if ($this->customer_id) {
            $customerPricelist = PricelistCustomer::query()
                ->where('customer_id', $this->customer_id)
                ->with('pricelist.currency')
                ->first();

            return $customerPricelist?->pricelist?->currency;
        }

        return null;
    }

    public function getCurrencyLabelAttribute(): string
    {
        $currency = $this->display_currency;

        if (! $currency) {
            return 'N/A';
        }

        return $currency->description
            ? "{$currency->code} - {$currency->description}"
            : $currency->code;
    }

    protected function hasValidPricelistId(): bool
    {
        return filled($this->pricelist_id)
            && ! in_array((string) $this->pricelist_id, ['0'], true);
    }
    public function getbatchcodesAttribute(){
        return SampleHeader::where('invoice_id',$this->id)->pluck('batch_code')->toArray();
    }
    public function getsamplecodesAttribute(){
        $ids = SampleHeader::where('invoice_id',$this->id)->pluck('id')->toArray();
        return SampleDetails::whereIn('sample_header_id',$ids)->pluck('sample_code')->toArray();
    }
    public function getInvoiceTotalAttribute(){
        return InvoiceDetails::where('invoice_id',$this->id)->sum('total');
    }
    public function details(): HasMany
    {
        return $this->hasMany(InvoiceDetails::class, 'invoice_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(SampleHeader::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePaymentDetail::class, 'invoice_id')
            ->where('is_delete', false)
            ->orderByDesc('created_at');
    }

    public function getCreatedByUserAttribute(): ?User
    {
        $audit = $this->audits()
            ->where('event', 'created')
            ->orderBy('created_at')
            ->first();

        if (! $audit?->user_id) {
            return null;
        }

        return User::find($audit->user_id);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->zoho_so_confirmed ? 'Confirmed' : 'Draft';
    }

    public function getZohoStatusLabelAttribute(): string
    {
        return filled($this->sales_order_id) ? 'Sent to Zoho' : 'Not sent';
    }
}
