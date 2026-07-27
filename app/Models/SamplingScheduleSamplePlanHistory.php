<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SamplingScheduleSamplePlanHistory extends Model
{
    use HasUuids;

    protected $table = 'sampling_schedule_sample_plan_histories';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sampling_schedule_id',
        'changed_by',
        'number_of_samples_before',
        'number_of_samples_after',
        'sample_details_before',
        'sample_details_after',
        'display_before',
        'display_after',
    ];

    protected function casts(): array
    {
        return [
            'number_of_samples_before' => 'integer',
            'number_of_samples_after' => 'integer',
            'sample_details_before' => 'array',
            'sample_details_after' => 'array',
            'display_before' => 'array',
            'display_after' => 'array',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(SamplingSchedule::class, 'sampling_schedule_id');
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
