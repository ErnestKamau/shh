<?php

namespace App\Models\LogEntryWorksheets;

use App\SampleHeader;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class SampleLogEntryWorksheetInstance extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sample_header_id',
        'log_entry_worksheet_id',
        'status',
    ];

    public function sampleHeader(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    public function worksheet(): BelongsTo
    {
        return $this->belongsTo(LogEntryWorksheet::class, 'log_entry_worksheet_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(SampleLogEntryWorksheetRow::class, 'instance_id')->orderBy('row_index');
    }

    public function mandatoryData(): HasMany
    {
        return $this->hasMany(SampleLogEntryWorksheetMandatoryData::class, 'instance_id');
    }
}
