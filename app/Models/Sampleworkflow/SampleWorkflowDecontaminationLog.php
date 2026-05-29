<?php

namespace App\Models\Sampleworkflow;

use App\Lab;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SampleWorkflowDecontaminationLog extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'log_date',
        'officer_name',
        'lab_id',
        'status',
        'company_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
        ];
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SampleWorkflowDecontaminationLogItem::class, 'decontamination_log_id');
    }
}
