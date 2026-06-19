<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SampleSequence extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'batch_code',
        'prefix',
        'sample_sequence',
    ];

    protected $casts = [
        'sample_sequence' => 'integer',
    ];

    /**
     * Get or create sample sequence for a job number and category prefix.
     */
    public static function getOrCreateSequence(string $jobNumber, string $prefix): self
    {
        return self::firstOrCreate(
            [
                'batch_code' => $jobNumber,
                'prefix' => strtoupper($prefix),
            ],
            ['sample_sequence' => 0],
        );
    }

    /**
     * Get next sample sequence number and increment for job + prefix.
     */
    public static function getNextSampleSequence(string $jobNumber, string $prefix): int
    {
        $sequence = self::getOrCreateSequence($jobNumber, $prefix);
        $sequence->increment('sample_sequence');

        return (int) $sequence->sample_sequence;
    }
}
