<?php

namespace App\Models\GeneralRequisition;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class GeneralRequisitionSupplierQuotes extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    public function item(): HasOne
    {
        return $this->hasOne(GeneralRequisitionRequestItem::class, 'id', 'request_item_id');
    }

    /**
     * Get the user that owns the GeneralRequisitionSupplierQuotes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier_details(): BelongsTo
    {
        return $this->belongsTo(\App\Supplier::class, 'supplier_id');
    }
}