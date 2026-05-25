<?php

namespace App\Models\LogEntryWorksheets;

use App\Enums\LogEntryWorksheet\LogEntryRowDriver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class LogEntryWorksheet extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'document_control_no',
        'revision',
        'issue_date',
        'row_driver',
        'row_driver_filters',
        'mandatory_fields_placement',
        'allow_manual_rows',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'allow_manual_rows' => 'boolean',
            'issue_date' => 'date',
            'row_driver_filters' => 'array',
        ];
    }

    public function columns(): HasMany
    {
        return $this->hasMany(LogEntryWorksheetColumn::class)->orderBy('order');
    }

    public function mandatoryFields(): HasMany
    {
        return $this->hasMany(LogEntryWorksheetMandatoryField::class)->orderBy('order');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(SampleLogEntryWorksheetInstance::class);
    }

    public function rowDriverEnum(): LogEntryRowDriver
    {
        return LogEntryRowDriver::from($this->row_driver);
    }
}
