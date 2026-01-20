<?php

namespace App\Models\Worksheets;

use Illuminate\Database\Eloquent\Model;

class SerTestkitWorksheetSampleRelation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function header()
    {
        return $this->belongsTo(SerHeaderWorksheetSampleRelation::class, 'ser_header_id');
    }
}
