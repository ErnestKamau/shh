<?php

namespace App\Models\Registry;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTransition extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'workflow_definition_id',
        'from_step_id',
        'to_step_id',
        'action_name',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function fromStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'from_step_id');
    }

    public function toStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'to_step_id');
    }
}
