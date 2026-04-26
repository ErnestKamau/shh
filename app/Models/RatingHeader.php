<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingHeader extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

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