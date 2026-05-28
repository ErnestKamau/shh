<?php

namespace App\Models\Formulars;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class FormulaStepTableStaticRow extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'formula_step_id',
        'order',
        'label',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(FormulaStep::class, 'formula_step_id');
    }

    public function cells(): HasMany
    {
        return $this->hasMany(FormulaStepTableStaticCell::class, 'static_row_id');
    }
}
