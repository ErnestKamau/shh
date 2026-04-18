<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class MethodValidationRequest extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'method_id',
        'requested_by',
        'lab_assigned',
        'status',
        'validation_data',
        'notes',
        'requested_at',
        'accepted_at',
        'completed_at'
    ];

    protected $casts = [
        'validation_data' => 'array',
        'requested_at' => 'datetime',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    // Relationships
    public function method()
    {
        return $this->belongsTo('App\AnalysisMethod', 'method_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo('App\User', 'requested_by');
    }

    public function labAssigned()
    {
        return $this->belongsTo('App\User', 'lab_assigned');
    }

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    // Helper methods for status
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted()
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // Status badge class helper
    public function getStatusBadgeClass()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'badge-warning';
            case self::STATUS_ACCEPTED:
                return 'badge-info';
            case self::STATUS_IN_PROGRESS:
                return 'badge-primary';
            case self::STATUS_COMPLETED:
                return 'badge-success';
            case self::STATUS_FAILED:
                return 'badge-danger';
            case self::STATUS_CANCELLED:
                return 'badge-secondary';
            default:
                return 'badge-light';
        }
    }
}

