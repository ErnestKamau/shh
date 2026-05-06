<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\SampleHeader;
use Illuminate\Http\Request;

class SampleApprovalChecklistController extends Controller
{
    public function show(Request $request, SampleHeader $sample)
    {
        $availableStages = array_values(array_filter(getSampleWorflowStages(), function ($stage) {
            return $stage !== 'All Samples';
        }));

        $requestedStage = (string) $request->query('stage_name', $sample->status ?? '');
        $stageName = in_array($requestedStage, $availableStages, true)
            ? $requestedStage
            : ($availableStages[0] ?? '');

        return view('layouts.lab.sample-workflow.approval-checklist', [
            'sample' => $sample,
            'stageName' => $stageName,
        ]);
    }
}