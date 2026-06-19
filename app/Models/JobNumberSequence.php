<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobNumberSequence extends Model
{
    protected $fillable = [
        'date_ymd',
        'last_sequence',
    ];

    protected $casts = [
        'last_sequence' => 'integer',
    ];

    public static function forDate(string $dateYmd): self
    {
        return self::query()->firstOrCreate(
            ['date_ymd' => $dateYmd],
            ['last_sequence' => 0],
        );
    }
}
