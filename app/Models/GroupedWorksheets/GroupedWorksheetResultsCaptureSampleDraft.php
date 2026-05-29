<?php

namespace App\Models\GroupedWorksheets;

use App\Casts\SafeEncrypted;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GroupedWorksheetResultsCaptureSampleDraft extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sample_header_id',
        'grouped_worksheet_holder_id',
        'sample_detail_id',
        'header_body',
        'main_body',
        'notes_body',
    ];

    protected function casts(): array
    {
        return [
            'header_body' => SafeEncrypted::class,
            'main_body' => SafeEncrypted::class,
            'notes_body' => SafeEncrypted::class,
        ];
    }
}
