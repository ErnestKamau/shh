<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemedyHeader extends Model
{
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