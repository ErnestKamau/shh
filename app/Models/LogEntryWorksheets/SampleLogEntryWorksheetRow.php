<?php

namespace App\Models\LogEntryWorksheets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class SampleLogEntryWorksheetRow extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'instance_id',
        'row_index',
        'row_source',
        'driver_type',
        'driver_id',
    ];

    protected function casts(): array
    {
        return [
            'row_index' => 'integer',
        ];
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(SampleLogEntryWorksheetInstance::class, 'instance_id');
    }

    public function cellValues(): HasMany
    {
        return $this->hasMany(SampleLogEntryWorksheetCellValue::class, 'row_id');
    }
}
