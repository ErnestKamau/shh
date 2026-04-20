<?php

namespace App\Http\Controllers\Api\Portal;

use App\AnalysisType;
use App\Http\Controllers\Controller;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\SamplePoint;
use App\SampleCondition;
use App\SampleType;
use Illuminate\Http\Request;

/**
 * Returns reference / lookup data needed to populate form dropdowns in the
 * customer portal.  All endpoints are scoped to the authenticated contact's
 * own customer (crm_customer_id) — no additional customer_id parameter is
 * accepted to prevent cross-customer data access.
 */
class ReferenceDataController extends Controller
{
    // GET /api/portal/reference/sample-types
    public function sampleTypes()
    {
        $types = SampleType::where('active', 1)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'description']);

        return response()->json(['data' => $types]);
    }

    // GET /api/portal/reference/analysis-types?sample_type_id=5
    public function analysisTypes(Request $request)
    {
        $request->validate([
            'sample_type_id' => 'required|integer|exists:sample_types,id',
        ]);

        $types = AnalysisType::where('sample_type_id', $request->sample_type_id)
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'turn_around_time']);

        return response()->json(['data' => $types]);
    }

    // GET /api/portal/reference/sample-conditions
    public function sampleConditions()
    {
        $conditions = SampleCondition::where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $conditions]);
    }

    // GET /api/portal/reference/sample-points
    // Returns sample points that belong to the authenticated user's customer
    public function samplePoints(Request $request)
    {
        $user       = $request->user();
        $customerId = (int) $user->client_id;

        $points = SamplePoint::where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        return response()->json(['data' => $points]);
    }

    // GET /api/portal/reference/units
    // Returns company units that belong to the authenticated user's customer
    public function units(Request $request)
    {
        $user       = $request->user();
        $customerId = (int) $user->client_id;

        $units = CRMCompanyUnit::where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $units]);
    }

    // GET /api/portal/reference/contacts
    // Returns active contacts at the authenticated user's customer — useful
    // for the "submitted by" / contact selector in the test request form
    public function contacts(Request $request)
    {
        $user       = $request->user();
        $customerId = (int) $user->client_id;

        $contacts = CustomerContact::where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'email', 'telephone', 'job_occupation']);

        return response()->json(['data' => $contacts]);
    }
}
