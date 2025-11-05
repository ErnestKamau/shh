<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRM\CRMCustomer as CrmCustomer;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of clients with pagination and search.
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 100);
        $search = $request->get('search', '');
        
        // Build query with search filter
        $query = CrmCustomer::where('active', 1);
        
        if (!empty($search)) {
            $query->where('name', 'LIKE', "%{$search}%");
        }
        
        // Paginate results
        $paginator = $query->select('id', 'name', 'code')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages()
            ]
        ]);
    }

    /**
     * Display the specified client.
     */
    public function show($id)
    {
        $client = CrmCustomer::select('id', 'name', 'code')
            ->findOrFail($id);

        return response()->json($client);
    }

    /**
     * Store a newly created client.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone1' => 'required|string|max:50',
            'telephone2' => 'nullable|string|max:50',
            'country_id' => 'required|exists:countries,id',
            'account_status' => 'required|exists:module_pre_configs,id',
            'postal_address' => 'required|string|max:500',
            'physical_address' => 'required|string|max:500',
            'website' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:50',
            'vat_no' => 'nullable|string|max:100',
            'credit_days' => 'nullable|integer|min:0',
            'zoho_customer_id' => 'nullable|exists:zoho_customers,id',
            'active' => 'nullable|boolean',
            'lpos_required' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate code if not provided
        $code = $request->code ?? $this->generateCustomerCode($request->name);

        // Handle zoho_customer_id - convert to array if single ID provided
        $zohoCustomerId = null;
        if ($request->zoho_customer_id) {
            $zohoCustomerId = is_array($request->zoho_customer_id) 
                ? $request->zoho_customer_id 
                : [$request->zoho_customer_id];
        }

        $client = CrmCustomer::create([
            'name' => $request->name,
            'code' => $code,
            'email' => $request->email,
            'telephone1' => $request->telephone1,
            'telephone2' => $request->telephone2,
            'country_id' => $request->country_id,
            'account_status' => $request->account_status,
            'postal_address' => $request->postal_address,
            'physical_address' => $request->physical_address,
            'website' => $request->website,
            'fax' => $request->fax,
            'vat_no' => $request->vat_no,
            'credit_days' => $request->credit_days,
            'zoho_customer_id' => $zohoCustomerId,
            'active' => $request->active ?? true,
            'lpos_required' => $request->lpos_required ?? false,
            'company_id' => 1, // Default company ID
        ]);

        return response()->json($client, 201);
    }

    /**
     * Generate a unique customer code from name.
     */
    protected function generateCustomerCode(string $name): string
    {
        // Take first 3 letters of name and add a number
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X');
        }
        
        // Find the next available number
        $lastCode = CrmCustomer::where('code', 'LIKE', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();
        
        if ($lastCode) {
            $number = intval(substr($lastCode->code, 3)) + 1;
        } else {
            $number = 1;
        }
        
        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}