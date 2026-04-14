<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchSequence extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'submission_form_instance_id',
        'year',
        'batch_sequence'
    ];

    protected $casts = [
        'year' => 'integer',
        'batch_sequence' => 'integer'
    ];

    /**
     * Get the submission form instance that this sequence belongs to
     */
    public function submissionFormInstance(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormInstance::class);
    }

    /**
     * Get or create batch sequence for a submission form instance and year
     */
    public static function getOrCreateSequence($submissionFormInstanceId, $year)
    {
        return self::firstOrCreate(
            [
                'submission_form_instance_id' => $submissionFormInstanceId,
                'year' => $year
            ],
            ['batch_sequence' => 0]
        );
    }

    /**
     * Get next batch sequence number and increment
     */
    public static function getNextBatchSequence($submissionFormInstanceId, $year)
    {
        $sequence = self::getOrCreateSequence($submissionFormInstanceId, $year);
        $sequence->increment('batch_sequence');
        return $sequence->batch_sequence;
    }
}