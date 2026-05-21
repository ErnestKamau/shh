<?php

namespace App\Models;

use App\Analyte;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class PreparationStepResult extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'preparation_step_results';

    protected $fillable = [
        'preparation_step_id',
        'analyte_id',
        'is_control',
        'control_solution_id',
        'result',
        'method_id',
        'analyst_id',
        'standard_limit',
        'standard_value',
    ];

    protected function casts(): array
    {
        return [
            'is_control' => 'boolean',
        ];
    }

    public function preparationStep(): BelongsTo
    {
        return $this->belongsTo(PreparationStep::class, 'preparation_step_id');
    }

    public function analyte(): BelongsTo
    {
        return $this->belongsTo(Analyte::class, 'analyte_id');
    }

    public function analyst(): BelongsTo
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }
}
