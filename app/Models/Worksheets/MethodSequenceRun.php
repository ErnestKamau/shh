<?php

namespace App\Models\Worksheets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\Models\MethodSequences\MethodSequence;
use App\Models\MethodSequences\MethodSequenceStage;
use App\SampleHeader;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MethodSequenceRun extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $fillable = [
        'sample_header_id',
        'method_sequence_id',
        'analyst_id',
        'run_date',
        'run_number',
        'run_name',
        'current_stage_id',
        'status',
        'started_by_user_id',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'run_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function sampleHeader(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class);
    }

    public function methodSequence(): BelongsTo
    {
        return $this->belongsTo(MethodSequence::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(MethodSequenceStage::class, 'current_stage_id');
    }

    public function analyst(): BelongsTo
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }

    public function startedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(MethodSequenceRunSample::class, 'run_id');
    }

    public function stageData(): HasMany
    {
        return $this->hasMany(MethodSequenceRunStageData::class, 'run_id');
    }
}
