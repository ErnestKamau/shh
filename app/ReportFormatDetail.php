<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportFormatDetail extends Model
{
    protected $table = 'report_format_details';

    protected $fillable = [
        'report_format_id',
        'key_name',
        'text_value',
    ];

    /**
     * Get the report format that owns this detail.
     */
    public function reportFormat()
    {
        return $this->belongsTo(ReportFormat::class);
    }
}
