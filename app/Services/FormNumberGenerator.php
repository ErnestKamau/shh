<?php

namespace App\Services;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use Illuminate\Support\Facades\DB;

class FormNumberGenerator
{
    /**
     * Generate a unique form number for submission form instance
     *
     * @param SubmissionForm $form
     * @return array ['format' => string, 'sequence_no' => int]
     */
    public static function generate(SubmissionForm $form): array
    {
        $prefix = $form->naming_convention_prefix ?? 'SF';

        // Get the maximum sequence number for this form.
        $sequence_number = SubmissionFormInstance::where('submission_form_id', $form->id)
            ->max('sequence_number');

        // If no stored sequence exists, derive a baseline from configured start number.
        if (! $sequence_number || $sequence_number <= 0) {
            $sequence_number = (int) ($form->start_submission_number ?: 1);
        } else {
            $sequence_number = (int) $sequence_number + 1;
        }

        // Use 2-digit year
        $year = date('y');

        // Ensure generated number is free, even if legacy records have form_number but null sequence_number.
        $format = self::format($prefix, $sequence_number, $year);
        while (SubmissionFormInstance::where('form_number', $format)->exists()) {
            $sequence_number++;
            $format = self::format($prefix, $sequence_number, $year);
        }
        
        return [
            'format' => $format,
            'sequence_no' => $sequence_number
        ];
    }

    private static function format(string $prefix, int $sequenceNumber, string $year): string
    {
        // Format: PREFIX + 3-digit sequence + / + year, e.g. MIC001/25
        return $prefix . sprintf('%03d', $sequenceNumber) . '/' . $year;
    }
}

