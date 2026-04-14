<?php

namespace App\Models\Worksheets;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class SerTestkitWorksheetSampleRelation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function header()
    {
        return $this->belongsTo(SerHeaderWorksheetSampleRelation::class, 'ser_header_id');
    }
}
