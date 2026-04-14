<?php

namespace App\Models\DMS;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentApprovalWorkflowStep extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'workflow_id',
        'step_order',
        'step_name',
        'step_type',
        'assignee_type',
        'assignee_id',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'step_order' => 'integer',
    ];

    /**
     * Get the workflow this step belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(DocumentApprovalWorkflow::class, 'workflow_id');
    }

    /**
     * Get the assignee (User or Role)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function assignee(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if user can approve this step
     *
     * @param \App\User $user
     * @return bool
     */
    public function canUserApprove($user): bool
    {
        // If assigned to specific user
        if ($this->assignee_type === User::class && $this->assignee_id === $user->id) {
            return true;
        }

        // If assigned to role
        if ($this->assignee_type === 'App\\Models\\Role') {
            return $user->roles->pluck('id')->contains($this->assignee_id);
        }

        return false;
    }
}

