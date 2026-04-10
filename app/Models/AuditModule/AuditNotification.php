<?php

namespace App\Models\AuditModule;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditNotification extends Model
{
    protected $table = 'audit_notifications';

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'notification_type_id',
        'notification_type_name',
        'title',
        'message',
        'recipient_user_id',
        'is_read',
        'is_email_sent',
        'read_at',
        'scheduled_for',
        'company_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_email_sent' => 'boolean',
        'read_at' => 'datetime',
        'scheduled_for' => 'datetime',
    ];

    // Relationships
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(AuditNotificationType::class, 'notification_type_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('recipient_user_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('scheduled_for', '<=', now())->unread();
    }

    // Helper Methods
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}














