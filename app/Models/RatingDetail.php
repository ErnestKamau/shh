<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'rating_header_id',
        'key',
        'label',
        'interpretation',
    ];

    /**
     * Get the rating header that owns this detail.
     */
    public function ratingHeader()
    {
        return $this->belongsTo(RatingHeader::class);
    }
}