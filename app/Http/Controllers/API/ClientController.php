<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\CRMCustomer as CrmCustomer;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index()
    {
        $clients = CrmCustomer::where('active', 1)
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return response()->json($clients);
    }

    /**
     * Store a newly created client.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:crm_customers,code',
            'email' => 'nullable|email|max:255',
            'telephone1' => 'nullable|string|max:255',
            'postal_address' => 'nullable|string',
            'physical_address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $client = CrmCustomer::create([
            'name' => $request->name,
            'code' => $request->code,
            'email' => $request->email,
            'telephone1' => $request->telephone1,
            'postal_address' => $request->postal_address,
            'physical_address' => $request->physical_address,
            'company_id' => 1, // Default company ID
            'active' => 1,
        ]);

        return response()->json($client, 201);
    }
}