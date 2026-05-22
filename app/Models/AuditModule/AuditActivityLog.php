<?php

namespace App\Models\AuditModule;

use App\Models\AuditModule\Concerns\ScopesAuditTenantForCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class AuditActivityLog extends Model implements Auditable
{
    use HasUuids;
    use ScopesAuditTenantForCompany;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'audit_activity_logs';

    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'action',
        'workflow_step',
        'workflow_step_name',
        'previous_status',
        'current_status',
        'step_started_at',
        'duration_seconds',
        'remarks',
        'description',
        'old_values',
        'new_values',
        'performed_by',
        'ip_address',
        'user_agent',
        'company_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'workflow_step' => 'integer',
        'duration_seconds' => 'integer',
        'step_started_at' => 'datetime',
        'loggable_id' => 'string',
        'performed_by' => 'string',
    ];

    // Relationships
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Static helper methods
    public static function log($model, string $action, ?string $description = null, ?array $oldValues = null, ?array $newValues = null): self
    {
        return static::create([
            'loggable_type' => get_class($model),
            'loggable_id' => $model->id,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'performed_by' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'company_id' => getUserCompany(),
        ]);
    }

    public static function logCreation($model, ?string $description = null): self
    {
        return static::log($model, 'Created', $description, null, $model->toArray());
    }

    public static function logUpdate($model, array $oldValues, ?string $description = null): self
    {
        return static::log($model, 'Updated', $description, $oldValues, $model->toArray());
    }

    public static function logDeletion($model, ?string $description = null): self
    {
        return static::log($model, 'Deleted', $description, $model->toArray(), null);
    }

    public static function logStatusChange($model, string $oldStatus, string $newStatus, ?string $notes = null): self
    {
        $description = "Status changed from '{$oldStatus}' to '{$newStatus}'";
        if ($notes) {
            $description .= ". Notes: {$notes}";
        }
        return static::log($model, 'Status Changed', $description, ['status' => $oldStatus], ['status' => $newStatus]);
    }

    public static function logWorkflowTransition($model, int $workflowStep, string $workflowStepName, string $previousStatus, string $currentStatus, ?string $remarks = null, ?int $durationSeconds = null): self
    {
        $description = "Workflow transition: {$workflowStepName} (Step {$workflowStep})";
        if ($previousStatus !== $currentStatus) {
            $description .= " - Status changed from '{$previousStatus}' to '{$currentStatus}'";
        }
        
        // Calculate duration if not provided and we have a previous log
        // For step 1, duration should be null (no previous step)
        // For subsequent steps, calculate from the most recent log's created_at
        if ($durationSeconds === null && $workflowStep > 1) {
            $previousLog = static::where('loggable_type', get_class($model))
                ->where('loggable_id', $model->id)
                ->whereNotNull('workflow_step')
                ->orderBy('workflow_step', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($previousLog && $previousLog->created_at) {
                // Calculate duration from previous log's created_at to now
                $durationSeconds = $previousLog->created_at->diffInSeconds(now());
                // Ensure positive duration
                if ($durationSeconds < 0) {
                    $durationSeconds = abs($durationSeconds);
                }
            }
        }

        return static::create([
            'loggable_type' => get_class($model),
            'loggable_id' => $model->id,
            'action' => 'Workflow Transition',
            'workflow_step' => $workflowStep,
            'workflow_step_name' => $workflowStepName,
            'previous_status' => $previousStatus,
            'current_status' => $currentStatus,
            'step_started_at' => now(),
            'duration_seconds' => $durationSeconds, // null for step 1, calculated for others
            'remarks' => $remarks,
            'description' => $description,
            'old_values' => ['status' => $previousStatus, 'workflow_step' => $workflowStep - 1],
            'new_values' => ['status' => $currentStatus, 'workflow_step' => $workflowStep],
            'performed_by' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'company_id' => getUserCompany(),
        ]);
    }

    public function getDurationFormatted(): string
    {
        if (!$this->duration_seconds) {
            return 'N/A';
        }

        $days = floor($this->duration_seconds / 86400);
        $hours = floor(($this->duration_seconds % 86400) / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = "{$days}d";
        }
        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes}m";
        }
        if ($seconds > 0 && count($parts) < 2) {
            $parts[] = "{$seconds}s";
        }

        return implode(' ', $parts) ?: '0s';
    }
}
