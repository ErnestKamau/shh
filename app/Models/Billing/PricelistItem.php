<?php

namespace App\Models\Billing;

use App\AnalysisElements;
use App\AnalysisType;
use App\SampleType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class PricelistItem extends Model
{
    use HasUuids;

    protected $table = 'pricelist_items';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'cost_price' => 'float',
        'selling_price' => 'float',
        'changed_price' => 'float',
        'vat' => 'boolean',
        'internal_use' => 'boolean',
        'external_view' => 'boolean',
        'active' => 'boolean',
        'is_package' => 'boolean',
        'level' => 'integer',
    ];

    public function pricelist(): BelongsTo
    {
        return $this->belongsTo(Pricelist::class, 'pricelist_id');
    }

    public function sampleType(): BelongsTo
    {
        return $this->belongsTo(SampleType::class, 'sample_type_id');
    }

    public function analysisType(): BelongsTo
    {
        return $this->belongsTo(AnalysisType::class, 'analysis_id');
    }

    public function analysisElement(): BelongsTo
    {
        return $this->belongsTo(AnalysisElements::class, 'analysis_element_id');
    }

    public function packageElements(): HasMany
    {
        return $this->hasMany(PricelistItemElement::class, 'pricelist_item_id');
    }

    /**
     * @return list<string>
     */
    public function coveredElementIds(): array
    {
        return $this->packageElements
            ->pluck('analysis_element_id')
            ->map(fn ($id): string => (string) $id)
            ->values()
            ->all();
    }
}
