<?php

namespace App\Models\Formulars;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormulaVersion extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'formula_id',
        'version_number',
        'is_active',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the formula that owns this version.
     */
    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class);
    }

    /**
     * Get the user who created this version.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this version.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the formula steps for this version.
     */
    public function formulaSteps(): HasMany
    {
        return $this->hasMany(FormulaStep::class)->orderBy('step_number');
    }

    /**
     * Get the worksheet executions for this version.
     */
    public function worksheetExecutions(): HasMany
    {
        return $this->hasMany(WorksheetExecution::class);
    }

    /**
     * Get the mandatory fields for this version.
     */
    public function mandatoryFields(): HasMany
    {
        return $this->hasMany(FormulaMandatoryField::class)->orderBy('order');
    }
}
