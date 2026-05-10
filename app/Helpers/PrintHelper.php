<?php

namespace App\Helpers;

use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstanceValue;
use Illuminate\Support\Collection;

class PrintHelper
{
    /**
     * Get the value for a specific element from the existing values
     */
    public static function getElementValue(SubmissionFormElement $element, Collection $existingValues): ?string
    {
        $value = $existingValues->firstWhere('element_id', $element->id);
        return $value ? $value->value : null;
    }

    /**
     * Format the element value for display in print template
     */
    public static function formatElementValue(SubmissionFormElement $element, Collection $existingValues): string
    {
        $value = self::getElementValue($element, $existingValues);
        
        if (!$value) {
            return 'Not provided';
        }

        // Handle different element types
        switch ($element->element_type) {
            case 'file':
            case 'camera_photo':
                return '<span class="file-value">' . basename($value) . '</span>';
            
            case 'checkbox':
                return $value ? 'Yes' : 'No';
            
            case 'radio':
            case 'select':
                // For radio and select, the value should already be the display text
                return htmlspecialchars($value);
            
            case 'textarea':
                return nl2br(htmlspecialchars($value));
            
            case 'date':
                try {
                    return \Carbon\Carbon::parse($value)->format('M d, Y');
                } catch (\Exception $e) {
                    return htmlspecialchars($value);
                }
            
            case 'datetime':
                try {
                    return \Carbon\Carbon::parse($value)->format('M d, Y H:i');
                } catch (\Exception $e) {
                    return htmlspecialchars($value);
                }
            
            case 'number':
                return is_numeric($value) ? number_format($value) : htmlspecialchars($value);
            
            case 'currency':
                return is_numeric($value) ? '$' . number_format($value, 2) : htmlspecialchars($value);
            
            default:
                return htmlspecialchars($value);
        }
    }
}
