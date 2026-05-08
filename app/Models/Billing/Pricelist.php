<?php

namespace App\Models\Billing;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Pricelist extends Model
{
    use HasUuids;

    protected $table = 'pricelists';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'is_master' => 'boolean',
        'active' => 'boolean',
        'valid_till' => 'date',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PricelistItem::class, 'pricelist_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(PricelistCustomer::class, 'pricelist_id');
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(PricelistEmailLog::class, 'pricelist_id');
    }
}
