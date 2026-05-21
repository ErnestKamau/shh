<?php

namespace App\Models\HybridWorksheets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class HybridFormulaStep extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'hybrid_worksheet_block_id',
        'step_number',
        'variable_name',
        'step_type',
        'expression',
        'label',
        'description',
        'lookup_config',
    ];

    protected function casts(): array
    {
        return [
            'lookup_config' => 'array',
        ];
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(HybridWorksheetBlock::class, 'hybrid_worksheet_block_id');
    }
}
