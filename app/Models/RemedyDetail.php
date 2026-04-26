<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemedyDetail extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use HasFactory;

    protected $fillable = [
        'remedy_header_id',
        'antibiotic',
        'sensitivity',
        'dimension',
        'comments',
    ];

    /**
     * Get the remedy header that owns this detail.
     */
    public function remedyHeader()
    {
        return $this->belongsTo(RemedyHeader::class);
    }
}