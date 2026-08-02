<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Portal\PortalAmendmentService;

class BatchAmmendmentController extends Controller
{
    public function __construct(
        private readonly PortalAmendmentService $amendments,
    ) {
        $this->middleware('auth');
    }

    public function add(Request $request)
    {
        $request->validate([
            'batch_id' => ['required'],
            'samples' => ['required', 'array', 'min:1'],
            'reason' => ['required', 'string', 'min:5'],
        ]);

        $batch = getSampleHeaderByID($request->batch_id);
        if (! isset($batch->id)) {
            return redirect()->back()->with('error', 'There is no batch with the specified ID!');
        }

        try {
            $this->amendments->raise(
                (string) $batch->crm_customer_id,
                (string) $batch->id,
                array_values($request->samples),
                (string) $request->reason,
                auth()->id() ? (string) auth()->id() : null,
                null,
                'crm',
            );
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Amendment raised. Batch is now in Sample Verification.');
    }

    public function edit(Request $request)
    {
        $ammendment = \App\BatchAmmendment::find($request->ammendment_id);
        if (isset($ammendment->id)) {
            $batch = getSampleHeaderByID($ammendment->batch_id);
            $ammendment->samples = json_encode($request->samples);
            $ammendment->reason = $request->reason;
            $ammendment->report_url = \App\BatchAmmendment::snapshotReportUrl($batch);
            $ammendment->save();

            return redirect()->back()->with('success', 'Ammendment generated successfully');
        }

        return redirect()->back()->with('error', 'No ammendment with the specified ID!');
    }
}
