<?php

namespace App\Http\Controllers;

use App\Models\SubmissionFormInstance;
use Illuminate\Http\Request;

class TestFormDataController extends Controller
{
    /**
     * Test the enhanced form data loading
     */
    public function testFormData($instanceId)
    {
        $instance = SubmissionFormInstance::with([
            'submissionForm.sections.elementHolders.elements',
            'values.element'
        ])->findOrFail($instanceId);

        // Get the enhanced form data
        $formData = $instance->getFormDataForDisplay();

        return response()->json([
            'success' => true,
            'instance_id' => $instance->id,
            'form_name' => $instance->submissionForm->name,
            'form_data' => $formData,
            'summary' => [
                'total_sections' => count($formData['sections']),
                'total_elements' => count($formData['elements_metadata']),
                'dependency_levels' => count($formData['dependency_chain']),
                'rows_data_count' => $this->countRowsData($formData)
            ]
        ]);
    }

    /**
     * Display enhanced form with data loading
     */
    public function showEnhancedForm($instanceId)
    {
        $instance = SubmissionFormInstance::with([
            'submissionForm.sections.elementHolders.elements',
            'values.element'
        ])->findOrFail($instanceId);

        return view('submission-forms.enhanced-display', compact('instance'));
    }

    /**
     * Debug enhanced form with detailed logging
     */
    public function debugEnhancedForm($instanceId)
    {
        $instance = SubmissionFormInstance::with([
            'submissionForm.sections.elementHolders.elements',
            'values.element'
        ])->findOrFail($instanceId);

        // Get the enhanced form data
        $formData = $instance->getFormDataForDisplay();
        
        // Log detailed information
        \Log::info('Enhanced Form Debug Data', [
            'instance_id' => $instanceId,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email ?? 'Not authenticated',
            'form_data_summary' => [
                'sections_count' => count($formData['sections']),
                'elements_count' => count($formData['elements_metadata']),
                'dependency_levels' => count($formData['dependency_chain'])
            ],
            'elements_metadata' => $formData['elements_metadata'],
            'sections_data' => $formData['sections']
        ]);

        return view('submission-forms.debug-enhanced-display', compact('instance', 'formData'));
    }

    /**
     * Display simple form with just labels and values
     */
    public function showSimpleForm($instanceId)
    {
        $instance = SubmissionFormInstance::with([
            'submissionForm.sections.elementHolders.elements',
            'values.element',
            'submittedBy'
        ])->findOrFail($instanceId);

        // Get the enhanced form data
        $formData = $instance->getFormDataForDisplay();

        return view('submission-forms.simple-display', compact('instance', 'formData'));
    }

    /**
     * Count total rows data across all sections
     */
    private function countRowsData($formData)
    {
        $count = 0;
        
        foreach ($formData['sections'] as $section) {
            foreach ($section['element_holders'] as $holder) {
                if ($holder['holder_type'] === 'rows') {
                    $count += count($holder['rows_data']);
                }
            }
        }
        
        return $count;
    }
}
