<?php

namespace App\Models;

use App\LabCategoryItems;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class PreparationStepDiluent extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'preparation_step_diluents';

    protected $fillable = [
        'preparation_step_id',
        'lab_category_item_id',
        'amount',
        'uom_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
        ];
    }

    public function preparationStep(): BelongsTo
    {
        return $this->belongsTo(PreparationStep::class, 'preparation_step_id');
    }

    public function labCategoryItem(): BelongsTo
    {
        return $this->belongsTo(LabCategoryItems::class, 'lab_category_item_id');
    }
}
