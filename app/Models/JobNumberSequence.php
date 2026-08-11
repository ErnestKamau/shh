<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobNumberSequence extends Model
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

    /**
     * @param  string  $yearYy  Two-digit calendar year (e.g. "26"). Stored in date_ymd.
     */
    public static function forYear(string $yearYy): self
    {
        return self::query()->firstOrCreate(
            ['date_ymd' => $yearYy],
            ['last_sequence' => 0],
        );
    }
}
