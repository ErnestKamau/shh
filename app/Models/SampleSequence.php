<?php

namespace App\Models;

use App\Services\Sampleworkflow\JobSampleNumberingService;
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
        $prefix = strtoupper($prefix);

        $existing = self::query()
            ->where('batch_code', $jobNumber)
            ->where('prefix', $prefix)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Unified numeric codes share one counter; seed from the highest legacy
        // category sequence so new -001 style codes do not collide with -C001 etc.
        $seed = 0;
        if ($prefix === JobSampleNumberingService::SEQUENCE_KEY) {
            $seed = (int) self::query()
                ->where('batch_code', $jobNumber)
                ->max('sample_sequence');
        }

        return self::query()->create([
            'batch_code' => $jobNumber,
            'prefix' => $prefix,
            'sample_sequence' => $seed,
        ]);
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
