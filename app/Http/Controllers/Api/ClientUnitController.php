<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\CRMCompanyUnit as CrmCompanyUnit;
use Illuminate\Support\Facades\Validator;

class ClientUnitController extends Controller
{
    /**
     * Display a listing of client units.
     */
    public function index(Request $request)
    {
        $query = CrmCompanyUnit::where('active', 1);
        
        // Filter by customer if provided
        if ($request->has('crm_customer_id') && $request->crm_customer_id) {
            $query->where('crm_customer_id', $request->crm_customer_id);
        }
        
        $units = $query->with('crmCustomer:id,name')
            ->select('id', 'name', 'crm_customer_id')
            ->orderBy('name')
            ->get();

        return response()->json($units);
    }

    /**
     * Store a newly created client unit.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'crm_customer_id' => 'required|exists:crm_customers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $unit = CrmCompanyUnit::create([
            'name' => $request->name,
            'crm_customer_id' => $request->crm_customer_id,
            'company_id' => 1, // Default company ID
            'active' => 1,
        ]);

        return response()->json($unit, 201);
    }
}