<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\CRMCompanySubUnit;
use Illuminate\Support\Facades\Validator;

class CompanySubUnitController extends Controller
{
    /**
     * Display a listing of company sub units.
     */
    public function index()
    {
        $subUnits = CRMCompanySubUnit::where('active', 1)
            ->with('companyUnit:id,name')
            ->select('id', 'name', 'code', 'crm_company_unit_id', 'crm_customer_id')
            ->orderBy('name')
            ->get();

        return response()->json($subUnits);
    }

    /**
     * Store a newly created company sub unit.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:crm_company_sub_units,code',
            'crm_company_unit_id' => 'required|exists:crm_company_units,id',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the customer_id from the company unit
        $companyUnit = \App\Models\CRM\CRMCompanyUnit::find($request->crm_company_unit_id);

        $subUnit = CRMCompanySubUnit::create([
            'name' => $request->name,
            'code' => $request->code,
            'crm_company_unit_id' => $request->crm_company_unit_id,
            'crm_customer_id' => $companyUnit ? $companyUnit->crm_customer_id : null,
            'active' => $request->active ?? 1,
        ]);

        return response()->json($subUnit, 201);
    }
}

