<?php

namespace App\Models;

use App\LabCategoryItems;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class PreparationStepMedia extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'preparation_step_media';

    protected $fillable = [
        'preparation_step_id',
        'lab_category_item_id',
    ];

    public function preparationStep(): BelongsTo
    {
        return $this->belongsTo(PreparationStep::class, 'preparation_step_id');
    }

    public function labCategoryItem(): BelongsTo
    {
        return $this->belongsTo(LabCategoryItems::class, 'lab_category_item_id');
    }
}
