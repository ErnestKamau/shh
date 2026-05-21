<?php

namespace App\Policies;

use Illuminate\Http\Request;

class DashboardPolicy
{
    /**
     * Portal gateway requests authorize via X-CRM-Customer-Id matching the route customer_id.
     */
    public function viewCustomerDashboard(Request $request, string $customerId): bool
    {
        $headerCustomerId = (string) ($request->header('X-CRM-Customer-Id') ?? $request->input('crm_customer_id', ''));

        if ($headerCustomerId === '') {
            return false;
        }

        return hash_equals($headerCustomerId, $customerId);
    }
}
