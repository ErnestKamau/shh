<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class SampleDetailStaging extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'sample_detail_staging';
    
    protected $fillable = [
        'sample_header_id',
        'data_json',
        'is_processed',
    ];
    
    protected $casts = [
        'data_json' => 'array',
        'is_processed' => 'boolean',
    ];
    
    public function sampleHeader()
    {
        return $this->belongsTo(\App\SampleHeader::class, 'sample_header_id');
    }
}

