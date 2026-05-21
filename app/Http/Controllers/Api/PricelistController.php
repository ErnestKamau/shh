<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use Illuminate\Http\Request;

class PricelistController extends Controller
{
    /**
     * Get the pricelist for a specific customer.
     * If the customer doesn't have an assigned pricelist, return the master pricelist.
     */
    public function showForCustomer($customerId)
    {
        $pricelistCustomer = null;
        
        // Only query if customerId is a valid UUID to prevent Postgres errors
        if (\Illuminate\Support\Str::isUuid($customerId)) {
            $pricelistCustomer = PricelistCustomer::where('customer_id', $customerId)->first();
        }

        if ($pricelistCustomer && $pricelistCustomer->pricelist_id) {
            $pricelist = Pricelist::with([
                'items' => function($query) {
                    $query->where('active', 1)->orderBy('level')->with(['sampleType', 'analysisType', 'analysisElement.analyte']);
                },
                'currency'
            ])->find($pricelistCustomer->pricelist_id);
        } else {
            // Fallback to master pricelist
            $pricelist = Pricelist::with([
                'items' => function($query) {
                    $query->where('active', 1)->orderBy('level')->with(['sampleType', 'analysisType', 'analysisElement.analyte']);
                },
                'currency'
            ])
            ->where('is_master', 1)
            ->where('active', 1)
            ->first();
        }

        if (!$pricelist) {
            return response()->json([
                'success' => false,
                'message' => 'No pricelist found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pricelist
        ]);
    }
}
