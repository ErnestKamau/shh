<?php

namespace App\Models\DMS;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentExpiryNotificationSetting extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'user_id',
        'email_notifications_enabled',
        'app_notifications_enabled',
        'notification_frequency_days',
        'notification_times',
    ];

    protected $casts = [
        'email_notifications_enabled' => 'boolean',
        'app_notifications_enabled' => 'boolean',
        'notification_frequency_days' => 'integer',
        'notification_times' => 'array',
    ];

    /**
     * Get the user these settings belong to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create settings for a user
     *
     * @param int $userId
     * @return static
     */
    public static function getOrCreateForUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'email_notifications_enabled' => true,
                'app_notifications_enabled' => true,
                'notification_frequency_days' => 7,
                'notification_times' => ['09:00'],
            ]
        );
    }
}

