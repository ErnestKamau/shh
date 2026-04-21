<?php

namespace App\Support;

use App\Models\SupportingDocumentElement;

class SupportingDocumentElementValidation
{
    /**
     * @return array<int, string>
     */
    public static function rulesForElement(SupportingDocumentElement $element): array
    {
        $rules = [];

        $rules[] = $element->is_required ? 'required' : 'nullable';

        switch ($element->element_type) {
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'textarea':
            case 'text':
                $rules[] = 'string';
                break;
            default:
                $rules[] = 'string';
                break;
        }

        if (! empty($element->validation_rules) && is_array($element->validation_rules)) {
            $rules = array_merge($rules, $element->validation_rules);
        }

        return $rules;
    }

    /**
     * @return list<string>
     */
    public static function paragraphPlaceholderNames(?string $template): array
    {
        if ($template === null || $template === '') {
            return [];
        }

        if (! preg_match_all('/\{\{\s*([^}]+?)\s*\}\}/', $template, $matches)) {
            return [];
        }

        $names = [];
        foreach ($matches[1] as $raw) {
            $name = trim((string) $raw);
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }
}
