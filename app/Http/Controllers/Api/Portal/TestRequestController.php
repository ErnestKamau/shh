<?php

namespace App\Http\Controllers\Api\Portal;

use App\ChainOfCustody;
use App\Http\Controllers\Controller;
use App\Http\Resources\Portal\TestRequestResource;
use App\SampleAnalysisStage;
use App\SampleDetails;
use App\SampleHeader;
use App\SampleType;
use App\Models\CRM\CRMCustomer;
use App\Models\System\SystemConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handles "Samples En-Route" test request (batch) creation and retrieval for
 * authenticated CRM customer contacts via the portal.
 *
 * Security invariant: the crm_customer_id on every batch is ALWAYS taken from
 * the authenticated user's account — it is never trusted from the request body.
 * This prevents a contact from creating or viewing batches for other customers.
 */
class TestRequestController extends Controller
{
    // -------------------------------------------------------------------------
    // GET /api/portal/test-requests
    // List all batches belonging to the authenticated user's customer
    // -------------------------------------------------------------------------
    public function index(Request $request)
    {
        $user       = $request->user();
        $customerId = (int) $user->client_id;

        $batches = SampleHeader::with('samples')
            ->where('crm_customer_id', $customerId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return TestRequestResource::collection($batches);
    }

    // -------------------------------------------------------------------------
    // GET /api/portal/test-requests/{id}
    // Single batch — scoped to own customer
    // -------------------------------------------------------------------------
    public function show(Request $request, int $id)
    {
        $user       = $request->user();
        $customerId = (int) $user->client_id;

        $batch = SampleHeader::with('samples')
            ->where('id', $id)
            ->where('crm_customer_id', $customerId) // scope to own customer
            ->first();

        if (!$batch) {
            return response()->json(['message' => 'Test request not found.'], 404);
        }

        return new TestRequestResource($batch);
    }

    // -------------------------------------------------------------------------
    // POST /api/portal/test-requests
    // Create a new "Samples En-Route" batch
    //
    // Required body fields:
    //   sample_type_id    integer
    //   date_collected    date (Y-m-d)
    //   date_expected     date (Y-m-d) — when samples will arrive at the lab
    //
    // Optional body fields:
    //   crm_unit_id           integer
    //   batch_scope           string
    //   batch_instructions    string
    //   reference_number      string
    //   samples               array of sample objects (see validation below)
    // -------------------------------------------------------------------------
    public function store(Request $request)
    {
        $user       = $request->user();
        $customerId = (int) $user->client_id;
        $contactId  = (int) $user->crm_contact_id;

        $validated = $request->validate([
            'sample_type_id'           => 'required|integer|exists:sample_types,id',
            'date_collected'           => 'required|date_format:Y-m-d',
            'date_expected'            => 'required|date_format:Y-m-d|after_or_equal:date_collected',
            'crm_unit_id'              => 'nullable|integer',
            'batch_scope'              => 'nullable|string|max:500',
            'batch_instructions'       => 'nullable|string|max:1000',
            'reference_number'         => 'nullable|string|max:100',
            'samples'                  => 'nullable|array|min:1',
            'samples.*.sample_code'    => 'required_with:samples|string|max:100',
            'samples.*.sample_condition_id' => 'nullable|integer',
            'samples.*.sample_point_id'     => 'nullable|integer',
            'samples.*.analysis_type_id'    => 'nullable|string',
            'samples.*.notes'               => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // -----------------------------------------------------------
            // 1. Generate batch code using the same logic as the web app
            // -----------------------------------------------------------
            $customer    = CRMCustomer::findOrFail($customerId);
            $sampleType  = SampleType::findOrFail($validated['sample_type_id']);

            $batchConfig = SystemConfiguration::where('key', 'batch_code_config')->first();
            if (!$batchConfig) {
                return response()->json([
                    'message' => 'Batch code configuration is not set. Please contact support.',
                ], 500);
            }

            // Strip numeric characters from customer code to form prefix
            $custCode = str_split($customer->code);
            $code     = [];
            $cont     = [];
            foreach ($custCode as $i => $ch) {
                if ((int) $ch > 0) {
                    $cont[] = $i;
                } elseif ($ch !== '0') {
                    $code[] = $ch;
                }
            }

            $tt     = count($custCode) - 1;
            $ranges = range($cont[0] ?? 0, $tt);
            $values = [];
            if (count($cont) < 2) {
                $values = ['0', $custCode[$cont[0] ?? 0]];
            } else {
                foreach ($ranges as $r) {
                    $values[] = $custCode[$r];
                }
            }

            $prefix   = 'BA' . $batchConfig->value . implode('', $values) . $sampleType->code;
            $startNo  = SystemConfiguration::where('key', 'batch_start_no')->first();
            $lastId   = SampleHeader::latest('id')->value('id') ?? 0;
            $seqNo    = ((int) ($startNo->value ?? 0)) + $lastId + 1;
            $padded   = str_pad((string) $seqNo, 4, '0', STR_PAD_LEFT);
            $batchCode = $prefix . $padded;

            // -----------------------------------------------------------
            // 2. Create the SampleHeader
            // -----------------------------------------------------------
            $header = new SampleHeader();
            $header->batch_code          = $batchCode;
            $header->status              = 'Samples En-Route';  // always En-Route from portal
            $header->is_client_order     = 1;
            $header->crm_customer_id     = $customerId;       // always from auth — never from body
            $header->crm_contact_id      = $contactId;        // always from auth
            $header->sample_type_id      = $validated['sample_type_id'];
            $header->date_collected      = $validated['date_collected'];
            $header->date_expected       = $validated['date_expected'];
            $header->receipt_date        = $validated['date_collected'];
            $header->crm_unit_id         = $validated['crm_unit_id'] ?? 0;
            $header->batch_scope         = $validated['batch_scope'] ?? null;
            $header->batch_instructions  = $validated['batch_instructions'] ?? null;
            $header->reference_number    = $validated['reference_number'] ?? 'n/a';
            $header->schedule_customer_email = $user->email;
            $header->submitted_through_portal = 1;

            // Set the initial workflow tracking stage
            $stage = SampleAnalysisStage::where('sample_workflow', 'Samples En-Route')
                ->orderBy('level', 'asc')
                ->first();
            $header->sample_tracking_stage = $stage->id ?? 0;

            $header->save();

            // -----------------------------------------------------------
            // 3. Create sample details rows
            // -----------------------------------------------------------
            $sampleRows = $validated['samples'] ?? [];
            foreach ($sampleRows as $row) {
                $detail = new SampleDetails();
                $detail->sample_header_id    = $header->id;
                $detail->sample_code         = $row['sample_code'];
                $detail->sample_condition_id = $row['sample_condition_id'] ?? null;
                $detail->sample_point_id     = $row['sample_point_id'] ?? null;
                $detail->analysis_type_id    = $row['analysis_type_id'] ?? '';
                $detail->notes_body          = $row['notes'] ?? null;
                $detail->save();
            }

            // -----------------------------------------------------------
            // 4. Open a Chain of Custody record (audit trail)
            // -----------------------------------------------------------
            $custody = new ChainOfCustody();
            $custody->sample_header_id  = $header->id;
            $custody->workflow_stage    = 'Samples En-Route';
            $custody->tracking_stage_id = $header->sample_tracking_stage;
            $custody->moved_in_by       = $user->id;
            $custody->save();

            DB::commit();

            $header->load('samples');

            Log::info('[Portal] Test request created', [
                'batch_code'      => $batchCode,
                'user_id'         => $user->id,
                'crm_customer_id' => $customerId,
            ]);

            return (new TestRequestResource($header))
                ->response()
                ->setStatusCode(201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Portal] Test request creation failed', [
                'error'   => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return response()->json([
                'message' => 'Failed to create test request. Please try again.',
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // PATCH /api/portal/test-requests/{id}/cancel
    // Cancel a batch — only allowed if it is still in "Samples En-Route" status
    // -------------------------------------------------------------------------
    public function cancel(Request $request, int $id)
    {
        $user       = $request->user();
        $customerId = (int) $user->client_id;

        $batch = SampleHeader::where('id', $id)
            ->where('crm_customer_id', $customerId)
            ->first();

        if (!$batch) {
            return response()->json(['message' => 'Test request not found.'], 404);
        }

        if ($batch->status !== 'Samples En-Route') {
            return response()->json([
                'message' => 'Only test requests with status "Samples En-Route" can be cancelled.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Close the open chain of custody record
            ChainOfCustody::where('sample_header_id', $batch->id)
                ->whereNull('moved_out_date')
                ->update([
                    'moved_out_date' => now(),
                    'moved_out_by'   => $user->id,
                    'comments'       => 'Cancelled by customer via portal',
                ]);

            $batch->status = 'Cancelled';
            $batch->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Portal] Cancel failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to cancel. Please try again.'], 500);
        }

        return response()->json(['message' => 'Test request cancelled successfully.']);
    }
}
