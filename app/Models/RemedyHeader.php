<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemedyHeader extends Model implements Auditable
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
     * Get the remedy details for this header.
     */
    public function remedyDetails()
    {
        return $this->hasMany(RemedyDetail::class);
    }

    /**
     * Get the analysis elements that recommend this remedy header.
     */
    public function analysisElements()
    {
        return $this->hasMany('App\AnalysisElements');
    }
}