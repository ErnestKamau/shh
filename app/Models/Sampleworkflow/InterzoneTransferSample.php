<?php

namespace App\Models\Sampleworkflow;

use App\SampleDetails;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterzoneTransferSample extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'interzone_transfer_id',
        'sample_detail_id',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(InterzoneTransfer::class, 'interzone_transfer_id');
    }

    public function sampleDetail(): BelongsTo
    {
        return $this->belongsTo(SampleDetails::class, 'sample_detail_id');
    }
}
