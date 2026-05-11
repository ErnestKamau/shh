<?php

namespace App\Models\Billing;

use App\Models\CRM\CRMCustomer;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PricelistEmailLog extends Model
{
    use HasUuids;

    protected $table = 'pricelist_email_logs';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'has_attachment' => 'boolean',
    ];

    public function pricelist(): BelongsTo
    {
        return $this->belongsTo(Pricelist::class, 'pricelist_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'customer_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
