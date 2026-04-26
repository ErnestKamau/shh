<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportFormatDetail extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

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
