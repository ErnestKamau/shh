<?php

namespace App\Models;

use App\LabSubCategory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class SolutionPreparationStepTemplateControl extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'solution_preparation_step_template_controls';

    protected $fillable = [
        'template_step_id',
        'control_solution_id',
        'label',
    ];

    public function templateStep(): BelongsTo
    {
        return $this->belongsTo(SolutionPreparationStepTemplate::class, 'template_step_id');
    }

    public function controlSolution(): BelongsTo
    {
        return $this->belongsTo(LabSubCategory::class, 'control_solution_id');
    }
}
