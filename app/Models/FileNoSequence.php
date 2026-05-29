<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileNoSequence extends Model
{
    protected $fillable = [
        'year',
        'last_sequence',
    ];

    protected $casts = [
        'year' => 'integer',
        'last_sequence' => 'integer',
    ];

    /**
     * Get or create the sequence row for the given year.
     */
    public static function forYear(int $year): self
    {
        return self::firstOrCreate(
            ['year' => $year],
            ['last_sequence' => 0],
        );
    }
}
