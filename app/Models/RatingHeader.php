<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingHeader extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the rating details for this header.
     */
    public function ratingDetails()
    {
        return $this->hasMany(RatingDetail::class);
    }

    /**
     * Get the sample types that use this rating header.
     */
    public function sampleTypes()
    {
        return $this->hasMany('App\SampleType');
    }
}