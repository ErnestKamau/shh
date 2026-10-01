<?php

namespace App\Models;

use App\SampleDetails;
use App\SampleHeader;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionQrCode extends Model
{
    /** @use HasFactory<\Database\Factories\CollectionQrCodeFactory> */
    use HasFactory;

    public const STATUS_UNLINKED = 'unlinked';

    public const STATUS_LINKED = 'linked';

    public const STATUS_VOID = 'void';

    protected $fillable = [
        'token',
        'sampling_schedule_id',
        'submission_form_instance_id',
        'row_index',
        'slot_no',
        'batch_id',
        'sample_detail_id',
        'status',
        'linked_at',
        'generated_by',
        'scan_count',
        'last_scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'row_index' => 'integer',
            'slot_no' => 'integer',
            'linked_at' => 'datetime',
            'scan_count' => 'integer',
            'last_scanned_at' => 'datetime',
        ];
    }

    public function samplingSchedule(): BelongsTo
    {
        return $this->belongsTo(SamplingSchedule::class, 'sampling_schedule_id');
    }

    public function submissionFormInstance(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'batch_id');
    }

    public function sampleDetail(): BelongsTo
    {
        return $this->belongsTo(SampleDetails::class, 'sample_detail_id');
    }

    public function isVoid(): bool
    {
        return $this->status === self::STATUS_VOID;
    }

    public function publicUrl(): string
    {
        return route('public.collection-qr.show', ['token' => $this->token]);
    }
}
