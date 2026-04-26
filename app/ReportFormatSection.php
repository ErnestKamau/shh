<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportFormatSection extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'report_format_sections';

    protected $fillable = [
        'report_format_id',
        'section_name',
        'order',
        'is_visible',
        'custom_title',
        'settings'
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'order' => 'integer',
        'settings' => 'array',
    ];

    /**
     * Get the report format that owns this section.
     */
    public function reportFormat()
    {
        return $this->belongsTo(ReportFormat::class);
    }
}
