<?php

namespace Tests\Feature;

use App\Models\SubmissionFormElement;
use Tests\TestCase;

class SubmissionFormZoneSelectDynamicOptionsTest extends TestCase
{
    public function test_zone_select_has_exists_validation_and_is_custom_dynamic(): void
    {
        $element = new SubmissionFormElement([
            'element_type' => 'zone_select',
            'is_required' => true,
        ]);

        $this->assertTrue($element->isCustomDynamicElement());
        $this->assertContains('exists:zones,id', $element->getLaravelValidationRules());
    }

    public function test_zone_select_get_dynamic_options_returns_labeled_zone_rows(): void
    {
        $element = new SubmissionFormElement([
            'element_type' => 'zone_select',
        ]);

        $options = $element->getDynamicOptions();

        $this->assertIsArray($options);

        foreach ($options as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }
    }

    public function test_image_upload_validation_matches_image_mime_rules(): void
    {
        $element = new SubmissionFormElement([
            'element_type' => 'image_upload',
            'is_required' => false,
        ]);

        $this->assertFalse($element->isCustomDynamicElement());
        $rules = $element->getLaravelValidationRules();
        $this->assertContains('image', $rules);
        $this->assertContains('mimes:jpg,jpeg,png,webp', $rules);
    }
}
