<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\CustomerContact as CrmCustomerContact;
use Illuminate\Support\Facades\Validator;

class ClientContactController extends Controller
{
    /**
     * Display a listing of client contacts.
     */
    public function index()
    {
        $contacts = CrmCustomerContact::where('active', 1)
            ->with('crmCustomer:id,name')
            ->select('id', 'first_name', 'last_name', 'middle_name', 'crm_customer_id')
            ->orderBy('first_name')
            ->get();

        return response()->json($contacts);
    }

    /**
     * Store a newly created client contact.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'crm_customer_id' => 'required|exists:crm_customers,id',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'job_occupation' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $contact = CrmCustomerContact::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'crm_customer_id' => $request->crm_customer_id,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'mobile' => $request->mobile,
            'job_occupation' => $request->job_occupation,
            'company_id' => 1, // Default company ID
            'active' => 1,
        ]);

        return response()->json($contact, 201);
    }
}