<?php

namespace App\Http\Controllers;

use App\Models\SubmissionFormInstance;
use App\Services\SampleCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SampleCreationController extends Controller
{
    protected $sampleCreationService;

    public function __construct(SampleCreationService $sampleCreationService)
    {
        $this->middleware('auth');
        $this->sampleCreationService = $sampleCreationService;
    }

    /**
     * Create samples from a submitted form instance
     */
    public function createFromForm(Request $request, SubmissionFormInstance $instance)
    {
        try {
            // Check if user can create samples
            if (!$this->canUserCreateSamples($instance)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to create samples from this form instance.'
                ], 403);
            }

            // Check if samples can be created
            if (!$this->sampleCreationService->canCreateSamples($instance)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create samples from this form instance. Please ensure the form is submitted and has mapped elements.'
                ], 400);
            }

            // Create samples
            $result = $this->sampleCreationService->createSamplesFromForm($instance);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Samples created successfully!',
                    'data' => [
                        'sample_header_id' => $result['sample_header']->id,
                        'batch_code' => $result['sample_header']->batch_code,
                        'sample_details_count' => count($result['sample_details'])
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create samples. Please try again.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error creating samples from form instance', [
                'instance_id' => $instance->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating samples: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sample creation status for a form instance
     */
    public function getStatus(SubmissionFormInstance $instance)
    {
        try {
            $status = $this->sampleCreationService->getSampleCreationStatus($instance);
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting sample creation status', [
                'instance_id' => $instance->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while checking status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk create samples from multiple form instances
     */
    public function bulkCreate(Request $request)
    {
        $request->validate([
            'instance_ids' => 'required|array',
            'instance_ids.*' => 'exists:submission_form_instances,id'
        ]);

        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($request->instance_ids as $instanceId) {
            try {
                $instance = SubmissionFormInstance::findOrFail($instanceId);
                
                if ($this->canUserCreateSamples($instance)) {
                    $result = $this->sampleCreationService->createSamplesFromForm($instance);
                    
                    if ($result) {
                        $results[] = [
                            'instance_id' => $instanceId,
                            'status' => 'success',
                            'sample_header_id' => $result['sample_header']->id,
                            'batch_code' => $result['sample_header']->batch_code
                        ];
                        $successCount++;
                    } else {
                        $results[] = [
                            'instance_id' => $instanceId,
                            'status' => 'failed',
                            'message' => 'Failed to create samples'
                        ];
                        $errorCount++;
                    }
                } else {
                    $results[] = [
                        'instance_id' => $instanceId,
                        'status' => 'unauthorized',
                        'message' => 'Not authorized to create samples'
                    ];
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $results[] = [
                    'instance_id' => $instanceId,
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
                $errorCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk creation completed. Success: {$successCount}, Errors: {$errorCount}",
            'data' => [
                'results' => $results,
                'success_count' => $successCount,
                'error_count' => $errorCount
            ]
        ]);
    }

    /**
     * Check if user can create samples from this form instance
     */
    private function canUserCreateSamples(SubmissionFormInstance $instance)
    {
        // Check if user is admin
        if (auth()->user()->hasRole && auth()->user()->hasRole('admin')) {
            return true;
        }

        // Check if user submitted this form
        if ($instance->submitted_by === auth()->id()) {
            return true;
        }

        // Add other authorization logic as needed
        return false;
    }
}
