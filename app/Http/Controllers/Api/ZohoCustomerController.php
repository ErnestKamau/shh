<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ZohoCustomers;
use Illuminate\Http\Request;

class ZohoCustomerController extends Controller
{
    /**
     * Display a paginated listing of active Zoho/Dynamics customers.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');

        $query = ZohoCustomers::where('status', 'Active')
            ->select('id', 'customer_no', 'name', 'currency_code')
            ->orderBy('name');

        // Apply search filter if provided
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('customer_no', 'like', '%' . $search . '%');
            });
        }

        $zohoCustomers = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $zohoCustomers->items(),
            'current_page' => $zohoCustomers->currentPage(),
            'last_page' => $zohoCustomers->lastPage(),
            'per_page' => $zohoCustomers->perPage(),
            'total' => $zohoCustomers->total(),
            'has_more' => $zohoCustomers->hasMorePages(),
        ]);
    }
}

