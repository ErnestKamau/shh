<?php

namespace App\Models\Worksheets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\CapturedResult;
use App\SampleDetails;
use App\SampleHeader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MethodSequenceRunSample extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'run_id',
        'captured_result_id',
        'sample_detail_id',
        'sample_header_id',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(MethodSequenceRun::class, 'run_id');
    }

    public function capturedResult(): BelongsTo
    {
        return $this->belongsTo(CapturedResult::class);
    }

    public function sampleDetail(): BelongsTo
    {
        return $this->belongsTo(SampleDetails::class);
    }

    public function sampleHeader(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class);
    }
}
