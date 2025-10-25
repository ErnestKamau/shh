<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionFormAuditLog extends Model
{

    protected $fillable = [
        'submission_form_instance_id',
        'user_id',
        'action',
        'field_changes',
        'notes',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'field_changes' => 'array'
    ];

    /**
     * Get the instance that this audit log belongs to
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the action display name
     */
    public function getActionDisplayName(): string
    {
        switch($this->action) {
            case 'created':
                return 'Created';
            case 'updated':
                return 'Updated';
            case 'submitted':
                return 'Submitted';
            case 'reviewed':
                return 'Reviewed';
            case 'approved':
                return 'Approved';
            case 'rejected':
                return 'Rejected';
            case 'cancelled':
                return 'Cancelled';
            default:
                return ucfirst($this->action);
        }
    }

    /**
     * Get the action badge color for UI
     */
    public function getActionBadgeColor(): string
    {
        switch($this->action) {
            case 'created':
                return 'info';
            case 'updated':
                return 'warning';
            case 'submitted':
                return 'primary';
            case 'reviewed':
                return 'info';
            case 'approved':
                return 'success';
            case 'rejected':
                return 'danger';
            case 'cancelled':
                return 'dark';
            default:
                return 'secondary';
        }
    }

    /**
     * Get formatted field changes for display
     */
    public function getFormattedFieldChanges(): array
    {
        if (empty($this->field_changes)) {
            return [];
        }

        $formatted = [];
        foreach ($this->field_changes as $field => $change) {
            if (is_array($change) && isset($change['old'], $change['new'])) {
                $formatted[] = [
                    'field' => $this->formatFieldName($field),
                    'old_value' => $this->formatValue($change['old']),
                    'new_value' => $this->formatValue($change['new'])
                ];
            }
        }

        return $formatted;
    }

    /**
     * Format field name for display
     */
    private function formatFieldName(string $field): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $field));
    }

    /**
     * Format value for display
     */
    private function formatValue($value): string
    {
        if ($value === null) {
            return '(empty)';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Check if this log entry has field changes
     */
    public function hasFieldChanges(): bool
    {
        return !empty($this->field_changes);
    }

    /**
     * Check if this log entry has notes
     */
    public function hasNotes(): bool
    {
        return !empty($this->notes);
    }

    /**
     * Get the user's display name
     */
    public function getUserDisplayName(): string
    {
        if (!$this->user) {
            return 'Unknown User';
        }

        return $this->user->name ?? $this->user->email ?? 'User';
    }

    /**
     * Get a summary of the action for display
     */
    public function getActionSummary(): string
    {
        $userName = $this->getUserDisplayName();
        $action = $this->getActionDisplayName();
        
        $summary = "{$userName} {$action}";
        
        if ($this->hasFieldChanges()) {
            $changeCount = count($this->getFormattedFieldChanges());
            $summary .= " ({$changeCount} field" . ($changeCount > 1 ? 's' : '') . " changed)";
        }
        
        return $summary;
    }

    /**
     * Scope to filter by action
     */
    public function scopeWithAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to order by most recent first
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Create an audit log entry
     */
    public static function logAction(
        int $instanceId, 
        int $userId, 
        string $action, 
        ?array $fieldChanges = null, 
        ?string $notes = null
    ): self {
        return static::create([
            'submission_form_instance_id' => $instanceId,
            'user_id' => $userId,
            'action' => $action,
            'field_changes' => $fieldChanges,
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Get audit trail for an instance
     */
    public static function getAuditTrail(int $instanceId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('submission_form_instance_id', $instanceId)
            ->with('user')
            ->recent()
            ->get();
    }
}