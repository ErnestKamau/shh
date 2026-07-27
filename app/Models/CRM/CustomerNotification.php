<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomerNotification extends Model
{
    use HasUuids;

    public const TYPE_ACCEPTANCE_FORM_SIGNING = 'Acceptance form signing';

    public const TYPE_REQUEST_NOTE = 'Request note';

    public const TYPE_SAMPLE_REJECTION = 'Sample request rejected';

    public const TYPE_ADDITIONAL_INFO_REQUIRED = 'Additional information required';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'customer_id',
        'entity_type',
        'entity_id',
        'notification_type',
        'notification_description',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'customer_id');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
