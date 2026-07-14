<?php

namespace App\Models\Billing;

use App\AnalysisElements;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricelistItemElement extends Model
{
    use HasUuids;

    protected $table = 'pricelist_item_elements';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    public function pricelistItem(): BelongsTo
    {
        return $this->belongsTo(PricelistItem::class, 'pricelist_item_id');
    }

    public function analysisElement(): BelongsTo
    {
        return $this->belongsTo(AnalysisElements::class, 'analysis_element_id');
    }
}
