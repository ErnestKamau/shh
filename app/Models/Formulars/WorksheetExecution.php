<?php

namespace App\Models\Formulars;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorksheetExecution extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'formula_version_id',
        'sample_id',
        'batch_id',
        'executed_by',
        'execution_data',
        'final_result',
        'execution_mode',
        'is_saved',
    ];

    protected $casts = [
        'execution_data' => 'array',
        'is_saved' => 'boolean',
    ];

    /**
     * Get the formula version that owns this execution.
     */
    public function formulaVersion(): BelongsTo
    {
        return $this->belongsTo(FormulaVersion::class);
    }

    /**
     * Get the sample that owns this execution.
     */
    public function sample(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SampleDetail::class, 'sample_id');
    }

    /**
     * Get the batch that owns this execution.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SampleHeader::class, 'batch_id');
    }

    /**
     * Get the user who executed this worksheet.
     */
    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    /**
     * Get the execution mode options.
     */
    public static function getExecutionModes(): array
    {
        return [
            'workflow' => 'Workflow Mode',
            'standalone' => 'Standalone Mode',
        ];
    }

    /**
     * Scope for saved executions.
     */
    public function scopeSaved($query)
    {
        return $query->where('is_saved', true);
    }

    /**
     * Scope for workflow executions.
     */
    public function scopeWorkflow($query)
    {
        return $query->where('execution_mode', 'workflow');
    }

    /**
     * Scope for standalone executions.
     */
    public function scopeStandalone($query)
    {
        return $query->where('execution_mode', 'standalone');
    }
}
