<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class CapaRecord extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'complaint_id',
        'details_of_non_conformance',
        'identified_by',
        'root_cause',
        'effectiveness_verified_by',
        'effectiveness_date',
        'why_why_analysis',
        'lab_no',
        'ncr_identified_date',
    ];

    protected function casts(): array
    {
        return [
            'effectiveness_date' => 'date',
            'ncr_identified_date' => 'date',
            'why_why_analysis' => 'json',
        ];
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}
