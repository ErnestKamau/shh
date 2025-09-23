<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemedyDetail extends Model
{
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