<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicalJobNumberSequence extends Model
{
    protected $fillable = [
        'date_ymd',
        'last_sequence',
    ];

    protected function casts(): array
    {
        return [
            'last_sequence' => 'integer',
        ];
    }
}
