<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportFormatSection extends Model
{
    protected $table = 'report_format_sections';

    protected $fillable = [
        'report_format_id',
        'section_name',
        'order',
        'is_visible',
        'custom_title',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the report format that owns this section.
     */
    public function reportFormat()
    {
        return $this->belongsTo(ReportFormat::class);
    }
}
