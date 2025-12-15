<?php

namespace App\Models\Equipments;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentDisposalApprovalWorkflowStep extends Model
{
    protected $fillable = [
        'workflow_id',
        'step_order',
        'step_name',
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
        return $this->belongsTo(EquipmentDisposalApprovalWorkflow::class, 'workflow_id');
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
        if ($this->assignee_type === 'App\\Models\\Role' || $this->assignee_type === 'App\\Role') {
            return $user->roles->pluck('id')->contains($this->assignee_id);
        }

        return false;
    }

    /**
     * Get assignee name for display
     *
     * @return string
     */
    public function getAssigneeNameAttribute(): string
    {
        if ($this->assignee_type === User::class) {
            $user = User::find($this->assignee_id);
            return $user ? $user->name : 'Unknown User';
        }

        if ($this->assignee_type === 'App\\Models\\Role' || $this->assignee_type === 'App\\Role') {
            $role = \App\Role::find($this->assignee_id);
            return $role ? $role->name : 'Unknown Role';
        }

        return 'Unknown';
    }
}

