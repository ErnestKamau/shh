<?php

namespace App\Models\LogEntryWorksheets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class SampleLogEntryWorksheetMandatoryData extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sample_log_entry_worksheet_mandatory_data';

    protected $fillable = [
        'instance_id',
        'mandatory_field_id',
        'field_value',
    ];

    protected function casts(): array
    {
        return [
            'field_value' => 'encrypted',
        ];
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(SampleLogEntryWorksheetInstance::class, 'instance_id');
    }

    public function mandatoryField(): BelongsTo
    {
        return $this->belongsTo(LogEntryWorksheetMandatoryField::class, 'mandatory_field_id');
    }
}
