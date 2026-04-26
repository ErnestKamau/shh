<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Document extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'description',
        'document_number',
        'document_type_id',
        'department_id',
        'version',
        'validity_period',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'status',
        'is_current_version',
        'parent_document_id',
        'document_folder_id',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'is_active',
        'is_published',
        'published_at',
        'published_by',
        'publish_scope',
        'publish_targets',
        'notification_frequency_id',
        'notification_days_before_expiry',
        'last_notification_sent',
        'notifications_enabled'
    ];

    protected $casts = [
        'validity_period' => 'date',
        'is_current_version' => 'boolean',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'publish_targets' => 'array',
        'last_notification_sent' => 'datetime',
        'notifications_enabled' => 'boolean',
    ];

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function department()
    {
        return $this->belongsTo(\App\InventoryDepartment::class);
    }

    public function attachments()
    {
        return $this->hasMany(DocumentAttachment::class);
    }

    public function versions()
    {
        return $this->hasMany(Document::class, 'parent_document_id');
    }

    public function folder()
    {
        return $this->belongsTo(DocumentFolder::class, 'document_folder_id');
    }

    /**
     * Get the full path of the document (Type / Folder / Subfolder)
     */
    public function getFullPathAttribute(): string
    {
        $parts = [];

        // Add document type name
        if ($this->documentType) {
            $parts[] = $this->documentType->name;
        }

        // Build folder path by traversing up the parent chain
        if ($this->folder) {
            $folderParts = [];
            $current = $this->folder;
            while ($current) {
                $folderParts[] = $current->name;
                $current = $current->parent;
            }
            // Reverse to get top-down order
            $parts = array_merge($parts, array_reverse($folderParts));
        }

        return implode(' / ', $parts);
    }

    public function parentDocument()
    {
        return $this->belongsTo(Document::class, 'parent_document_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\User::class, 'updated_by');
    }

    public function approver()
    {
        return $this->belongsTo(\App\User::class, 'approved_by');
    }

    public function publisher()
    {
        return $this->belongsTo(\App\User::class, 'published_by');
    }

    public function notificationFrequency()
    {
        return $this->belongsTo(NotificationFrequency::class);
    }

    public function publications()
    {
        return $this->hasMany(DocumentPublication::class);
    }

    public function scopeCurrentVersion($query)
    {
        // Get the latest version for each unique name + document_type combination
        return $query->whereIn('id', function($subQuery) {
            $subQuery->selectRaw('MAX(id)')
                     ->from('documents')
                     ->groupBy('name', 'document_type_id');
        });
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByType($query, $typeId)
    {
        return $query->where('document_type_id', $typeId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUnpublished($query)
    {
        return $query->where('is_published', false);
    }

    public function scopeByPublishScope($query, $scope)
    {
        return $query->where('publish_scope', $scope);
    }

    public function scopeByPublishTarget($query, $targetId, $scope)
    {
        return $query->where('publish_scope', $scope)
                    ->whereJsonContains('publish_targets', (string)$targetId);
    }

    public function scopeExpired($query)
    {
        return $query->where('validity_period', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('validity_period', '<=', now()->addDays($days))
                    ->where('validity_period', '>', now());
    }

    public function scopeValid($query)
    {
        return $query->where(function($q) {
            $q->where('validity_period', '>', now())
              ->orWhereNull('validity_period');
        });
    }

    public function scopeSameDocument($query, $documentName, $documentTypeId)
    {
        return $query->where('name', $documentName)
                    ->where('document_type_id', $documentTypeId);
    }

    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }

    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function isExpired()
    {
        if (!$this->validity_period) {
            return false;
        }
        return $this->validity_period->isPast();
    }

    public function isExpiringSoon($days = 30)
    {
        if (!$this->validity_period) {
            return false;
        }
        
        // Check if document expires within the next X days (must be in future)
        $daysUntilExpiry = now()->diffInDays($this->validity_period, false);
        return $this->validity_period->isFuture() && $daysUntilExpiry <= $days;
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->validity_period) {
            return null;
        }
        return now()->diffInDays($this->validity_period, false);
    }

    /**
     * Get formatted time remaining (days and hours)
     */
    public function getFormattedTimeRemainingAttribute()
    {
        if (!$this->validity_period) {
            return null;
        }

        $now = now();
        $expiryDate = $this->validity_period;

        if ($expiryDate->isPast()) {
            // Document has expired
            $totalHours = $now->diffInHours($expiryDate, false);
            $days = (int)($totalHours / 24);
            $hours = $totalHours % 24;
            
            if ($days > 0) {
                return "{$days} day" . ($days > 1 ? 's' : '') . " {$hours} hour" . ($hours > 1 ? 's' : '') . " ago";
            } else {
                return "{$hours} hour" . ($hours > 1 ? 's' : '') . " ago";
            }
        } else {
            // Document is still valid
            $totalHours = $now->diffInHours($expiryDate, false);
            $days = (int)($totalHours / 24);
            $hours = $totalHours % 24;
            
            if ($days > 0) {
                return "{$days} day" . ($days > 1 ? 's' : '') . " {$hours} hour" . ($hours > 1 ? 's' : '');
            } else {
                return "{$hours} hour" . ($hours > 1 ? 's' : '');
            }
        }
    }

    /**
     * Get time remaining in hours only (for short display)
     */
    public function getHoursRemainingAttribute()
    {
        if (!$this->validity_period) {
            return null;
        }

        $now = now();
        $expiryDate = $this->validity_period;

        if ($expiryDate->isPast()) {
            return $now->diffInHours($expiryDate, false);
        } else {
            return $now->diffInHours($expiryDate, false);
        }
    }

    public function isPublished()
    {
        return $this->is_published;
    }

    public function isPublishedToUser($user)
    {
        if (!$this->is_published) {
            return false;
        }

        switch ($this->publish_scope) {
            case 'all_departments':
                return true;
            case 'all_roles':
                return true;
            case 'department':
                return in_array($user->department_id, $this->publish_targets ?? []);
            case 'role':
                $userRoleIds = $user->roles->pluck('role_id')->toArray();
                return !empty(array_intersect($userRoleIds, $this->publish_targets ?? []));
            case 'mixed':
                // Check both department and role access
                $hasDepartmentAccess = in_array($user->department_id, $this->publish_targets['departments'] ?? []);
                $userRoleIds = $user->roles->pluck('role_id')->toArray();
                $hasRoleAccess = !empty(array_intersect($userRoleIds, $this->publish_targets['roles'] ?? []));
                return $hasDepartmentAccess || $hasRoleAccess;
            default:
                return false;
        }
    }

    public function getPublishScopeLabelAttribute()
    {
        switch ($this->publish_scope) {
            case 'all_departments':
                return 'All Departments';
            case 'all_roles':
                return 'All Roles';
            case 'department':
                return 'Specific Departments';
            case 'role':
                return 'Specific Roles';
            case 'mixed':
                return 'Departments & Roles';
            default:
                return 'Not Published';
        }
    }

    /**
     * Check if document should send expiry notification
     */
    public function shouldSendExpiryNotification()
    {
        if (!$this->notifications_enabled || !$this->validity_period) {
            return false;
        }

        $daysUntilExpiry = now()->diffInDays($this->validity_period, false);
        
        // If document is expired, send daily notifications
        if ($this->isExpired()) {
            return $this->canSendNotification(1); // Daily for expired documents
        }

        // If document is expiring soon, send notifications based on frequency
        if ($daysUntilExpiry <= $this->notification_days_before_expiry) {
            $frequency = $this->notificationFrequency;
            if ($frequency) {
                return $this->canSendNotification($frequency->days_interval);
            }
        }

        return false;
    }

    /**
     * Check if notification can be sent based on frequency and last sent time
     */
    private function canSendNotification($daysInterval)
    {
        if (!$this->last_notification_sent) {
            return true;
        }

        $lastSent = $this->last_notification_sent;
        $now = now();

        return $lastSent->diffInDays($now) >= $daysInterval;
    }

    /**
     * Mark notification as sent
     */
    public function markNotificationSent()
    {
        $this->update(['last_notification_sent' => now()]);
    }

    /**
     * Get notification recipients - send to document creator
     */
    public function getNotificationRecipients()
    {
        // Send notification to the person who created the document
        return collect([$this->creator]);
    }
}
