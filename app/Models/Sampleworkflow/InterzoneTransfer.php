<?php

namespace App\Models\Sampleworkflow;

use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use App\User;
use App\Zone;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterzoneTransfer extends Model
{
    use HasUuids;

    public const SCOPE_REQUEST = 'request';

    public const SCOPE_BATCH = 'batch';

    public const MODE_FULL = 'full';

    public const MODE_PARTIAL = 'partial';

    public const STATUS_COMPLETED = 'completed';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'transfer_scope',
        'transfer_mode',
        'submission_form_instance_id',
        'sample_header_id',
        'from_zone_id',
        'to_zone_id',
        'report_from_parent_zone',
        'status',
        'remarks',
        'initiated_by',
        'transferred_at',
    ];

    protected function casts(): array
    {
        return [
            'report_from_parent_zone' => 'boolean',
            'transferred_at' => 'datetime',
        ];
    }

    public function submissionFormInstance(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }

    public function sampleHeader(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    public function fromZone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'from_zone_id');
    }

    public function toZone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'to_zone_id');
    }

    public function initiatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(InterzoneTransferSample::class, 'interzone_transfer_id');
    }
}
