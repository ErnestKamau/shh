<?php

namespace App\Models;

use App\LabSubCategory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class PreparationStepControl extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'preparation_step_controls';

    protected $fillable = [
        'preparation_step_id',
        'control_solution_id',
        'label',
    ];

    public function preparationStep(): BelongsTo
    {
        return $this->belongsTo(PreparationStep::class, 'preparation_step_id');
    }

    public function controlSolution(): BelongsTo
    {
        return $this->belongsTo(LabSubCategory::class, 'control_solution_id');
    }
}
