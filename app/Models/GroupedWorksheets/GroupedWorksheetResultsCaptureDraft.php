<?php

namespace App\Models\GroupedWorksheets;

use App\Casts\SafeEncrypted;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GroupedWorksheetResultsCaptureDraft extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sample_header_id',
        'grouped_worksheet_holder_id',
        'captured_result_id',
        'result',
        'reporting_symbol',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            'result' => SafeEncrypted::class,
            'remark' => SafeEncrypted::class,
        ];
    }
}
