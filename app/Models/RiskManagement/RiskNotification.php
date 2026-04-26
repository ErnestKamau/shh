<?php

namespace App\Models\RiskManagement;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RiskNotification extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'risk_notifications';

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
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

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}


