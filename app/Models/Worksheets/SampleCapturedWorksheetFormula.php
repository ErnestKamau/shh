<?php

namespace App\Models\Worksheets;

use App\CapturedResult;
use App\Models\Formulars\Formula;
use App\SampleDetails;
use App\SampleHeader;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SampleCapturedWorksheetFormula extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sample_header_id',
        'sample_detail_id',
        'captured_result_id',
        'formular_id',
        'date',
        'lab_no',
        'sample_details',
        'time_in',
        'done_by_user_id',
        'time_out',
        'read_by_user_id',
        'read_date',
        'final_result',
        'posted_at',
        'posted_by_user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'read_date' => 'date',
        'time_in' => 'datetime:H:i',
        'time_out' => 'datetime:H:i',
        'posted_at' => 'datetime',
    ];

    public function sampleHeader(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class);
    }

    public function sampleDetail(): BelongsTo
    {
        return $this->belongsTo(SampleDetails::class);
    }

    public function capturedResult(): BelongsTo
    {
        return $this->belongsTo(CapturedResult::class);
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class, 'formular_id');
    }

    public function doneByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'done_by_user_id');
    }

    public function readByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by_user_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by_user_id');
    }

    public function stepData(): HasMany
    {
        return $this->hasMany(SampleWorksheetFormularStepData::class, 'worksheet_formular_id');
    }

    public function mandatoryData(): HasMany
    {
        return $this->hasMany(SampleWorksheetFormularMandatoryData::class, 'worksheet_formular_id');
    }
}
