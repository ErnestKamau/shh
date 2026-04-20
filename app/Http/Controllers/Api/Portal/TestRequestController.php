<?php

namespace App\Http\Controllers\Api\Portal;

use App\Batch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Customer Portal — Test Request (Batch) Management
 * 
 * Allows authenticated portal users to:
 * - List their test requests (batches in "Samples En-Route" status)
 * - Create new test requests
 * - View test request details
 * - Cancel pending test requests
 */
class TestRequestController extends Controller
{
    /**
     * GET /api/portal/test-requests
     * List all test requests belonging to the authenticated user's customer
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $customerId = (int) $user->client_id;

        $batches = Batch::where('customer_id', $customerId)
            ->whereIn('status', ['Samples En-Route', 'Received', 'In Progress', 'Completed'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($batches);
    }

    /**
     * POST /api/portal/test-requests
     * Create a new test request (batch)
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $customerId = (int) $user->client_id;

        $validated = $request->validate([
            'batch_number' => 'nullable|string|max:255',
            'sample_type_id' => 'required|integer|exists:sample_types,id',
            'analysis_type_id' => 'required|integer|exists:analysis_types,id',
            'number_of_samples' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            $batch = Batch::create([
                'customer_id' => $customerId,
                'batch_number' => $validated['batch_number'] ?? 'TR-' . time(),
                'sample_type_id' => $validated['sample_type_id'],
                'status' => 'Samples En-Route',
                'created_by' => $user->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'message' => 'Test request created successfully',
                'data' => $batch,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create test request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/portal/test-requests/{id}
     * View details of a specific test request
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $customerId = (int) $user->client_id;

        $batch = Batch::where('id', $id)
            ->where('customer_id', $customerId)
            ->first();

        if (!$batch) {
            return response()->json([
                'message' => 'Test request not found or access denied',
            ], 404);
        }

        return response()->json(['data' => $batch]);
    }

    /**
     * PATCH /api/portal/test-requests/{id}/cancel
     * Cancel a pending test request
     */
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $customerId = (int) $user->client_id;

        $batch = Batch::where('id', $id)
            ->where('customer_id', $customerId)
            ->where('status', 'Samples En-Route')
            ->first();

        if (!$batch) {
            return response()->json([
                'message' => 'Test request not found, already processed, or access denied',
            ], 404);
        }

        $batch->update([
            'status' => 'Cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Test request cancelled successfully',
            'data' => $batch,
        ]);
    }
}