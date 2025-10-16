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
        
        // Get the maximum sequence number for this form
        $sequence_number = SubmissionFormInstance::where('submission_form_id', $form->id)
            ->max('sequence_number');
        
        // If no sequence exists, start from start_submission_number + 1, otherwise increment
        $sequence_number = $sequence_number && $sequence_number > 0 
            ? ($sequence_number + 1) 
            : (($form->start_submission_number ?? 0) + 1);
        
        // Use 2-digit year
        $year = date('y');
        
        // Format: PREFIX + SEQUENCE + / + YEAR
        // Example: MIC1/25, MIC2/25, MIC100/25
        $format = $prefix . sprintf('%03d', $sequence_number) . '/' . $year;
        
        return [
            'format' => $format,
            'sequence_no' => $sequence_number
        ];
    }
}

