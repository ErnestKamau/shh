<?php

namespace App\Models\LogEntryWorksheets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class SampleLogEntryWorksheetCellValue extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'row_id',
        'column_id',
        'value',
    ];

    public function row(): BelongsTo
    {
        return $this->belongsTo(SampleLogEntryWorksheetRow::class, 'row_id');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(LogEntryWorksheetColumn::class, 'column_id');
    }
}
