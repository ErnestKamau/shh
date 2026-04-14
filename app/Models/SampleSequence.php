<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class SampleSequence extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'batch_code',
        'sample_sequence'
    ];

    protected $casts = [
        'sample_sequence' => 'integer'
    ];

    /**
     * Get or create sample sequence for a batch
     */
    public static function getOrCreateSequence($batchCode)
    {
        return self::firstOrCreate(
            ['batch_code' => $batchCode],
            ['sample_sequence' => 0]
        );
    }

    /**
     * Get next sample sequence number and increment
     */
    public static function getNextSampleSequence($batchCode)
    {
        $sequence = self::getOrCreateSequence($batchCode);
        $sequence->increment('sample_sequence');
        return $sequence->sample_sequence;
    }
}